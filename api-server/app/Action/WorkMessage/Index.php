<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkMessage;

use App\Constants\WorkMessage\MsgType;
use App\Contract\WorkContactServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkMessageServiceInterface;
use App\Logic\User\Traits\UserTrait;
use App\Logic\WorkMessage\Traits\ContentTrait;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 查询 - 列表.
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;
    use ContentTrait;
    use UserTrait;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $employeeClient;

    /**
     * @Inject
     * @var WorkContactServiceInterface
     */
    protected $contactClient;

    /**
     * @var WorkMessageServiceInterface
     */
    protected $msgClient;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/workMessage/index", methods="GET")
     */
    public function handle(): array
    {
        $this->msgClient = make(WorkMessageServiceInterface::class, [$this->corpId()]);

        ## 参数
        $params = $this->request->inputs(
            ['workEmployeeId', 'toUserType', 'toUserId', 'content', 'dateTimeStart', 'dateTimeEnd', 'type', 'page', 'perPage'],
            ['type' => 0, 'page' => 1, 'perPage' => 10]
        );
        $this->validated($params);
        $params['workEmployeeId'] = (int) $params['workEmployeeId'];
        $params['toUserType']     = (int) $params['toUserType'];
        $params['toUserId']       = (int) $params['toUserId'];

        ## 分页查询
        [$where, $options] = $this->paramsHandle($params);
        $pageData          = $this->msgClient->getWorkMessageList(
            $where,
            ['id', 'action', 'from', 'corp_id', 'tolist_id', 'tolist_type', 'msg_type', 'content', 'msg_time', 'room_id'],
            $options
        );

        ## 响应数据处理
        $employeeWxUserId = $this->getEmployee($params['workEmployeeId']);
        $data             = array_map(function ($item) use ($employeeWxUserId) {
            $item['content'] = $this->contentFormat(json_decode($item['content'], true), $item['msgType']);
            $item['msgDataTime'] = date('Y-m-d H:i:s', (int) ($item['msgTime'] * 0.001));
            $item['type'] = $item['msgType'];
            return array_merge($item, $this->otherResData($item, $employeeWxUserId));
        }, $pageData['data']);

        return [
            'page' => [
                'perPage'   => $pageData['per_page'],
                'total'     => $pageData['total'],
                'totalPage' => $pageData['last_page'],
            ],
            'list' => $data,
        ];
    }

    /**
     * 属性替换.
     * @return array|string[] ...
     */
    public function attributes(): array
    {
        return [
            'workEmployeeId' => '员工',
            'toUserType'     => '会话对象类型',
            'toUserId'       => '会话对象',
            'type'           => '会话内容类型',
            'content'        => '搜索内容',
            'dateTimeStart'  => '搜索开始时间',
            'dateTimeEnd'    => '搜索结束时间',
            'page'           => '页码',
            'perPage'        => '每页条数',
        ];
    }

    /**
     * 验证规则.
     */
    protected function rules(): array
    {
        return [
            'workEmployeeId' => 'required|integer',
            'toUserType'     => 'required|integer|in:0,1,2',
            'toUserId'       => 'required|integer',
            'content'        => 'sometimes|nullable|string',
            'dateTimeStart'  => 'sometimes|nullable|date|before_or_equal:dateTimeEnd',
            'dateTimeEnd'    => 'sometimes|nullable|date',
            'type'           => 'integer',
            'page'           => 'integer',
            'perPage'        => 'integer',
        ];
    }

    /**
     * 获取员工微信userId.
     * @param int $workEmployeeId ..
     * @return string ..
     */
    protected function getEmployee(int $workEmployeeId): string
    {
        $data = $this->employeeClient->getWorkEmployeeById($workEmployeeId, ['id', 'wx_user_id']);
        if (empty($data)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '员工不存在');
        }
        return $data['wxUserId'];
    }

    /**
     * 获取客户微信userId.
     * @param int $contactId ..
     * @return string ..
     */
    protected function getContact(int $contactId): string
    {
        $data = $this->contactClient->getWorkContactById($contactId, ['id', 'wx_external_userid']);
        if (empty($data)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '客户不存在');
        }
        return $data['wxExternalUserid'];
    }

    /**
     * 模型条件处理.
     * @param array $params ...
     * @return array ...
     */
    protected function paramsHandle(array $params): array
    {
        $type = (int) $params['type'];
        if ($type === 100) {
            $where['msg_type'] = MsgType::$otherType;
        } elseif ($type) {
            $where['msg_type'] = $type;
        }
        if ($params['toUserType'] === 2) {
            $where['room_id'] = $params['toUserId'];
        } else {
            $fromUserId = $this->getEmployee($params['workEmployeeId']);
            $toUserId   = $params['toUserType'] === 0 ? $this->getEmployee($params['toUserId']) : $this->getContact($params['toUserId']);
            $where[]    = [
                "((`from` = ? AND `tolist_id`->'$[0]' = ?) OR (`from` = ? AND `tolist_id`->'$[0]' = ?))",
                'RAW',
                [$fromUserId, $params['toUserId'], $toUserId, $params['workEmployeeId'],
                ], ];
        }

        if ($params['content']) {
            if ($type === MsgType::TEXT) {
                $where[] = ["content->'$.content' LIKE ?", 'RAW', ['%' . $params['content'] . '%']];
            }
        }
        if ($params['dateTimeStart']) {
            $where[] = ['msg_time', '>=', strtotime($params['dateTimeStart']) . '000'];
        }
        if ($params['dateTimeEnd']) {
            $where[] = ['msg_time', '<=', strtotime($params['dateTimeEnd']) . '999'];
        }

        $options = [
            'page'       => $params['page'],
            'perPage'    => $params['perPage'],
            'orderByRaw' => '`created_at` ASC',
        ];

        return [$where, $options];
    }

    /**
     * 处理其它响应数据.
     * @param array $item ...
     * @param string $currentWxUserId ...
     * @return array ...
     */
    protected function otherResData(array $item, string $currentWxUserId): array
    {
        $data = [
            'name'          => '系统',
            'avatar'        => '',
            'isCurrentUser' => 0,
        ];

        ## 机器人
        if (strpos($item['from'], 'we') === 0) {
            $data['name'] = '机器人';
        }

        ## 客户
        if (strpos($item['from'], 'wo') === 0 || strpos($item['from'], 'wm') === 0) {
            $contact = $this->contactClient->getWorkContactByWxExternalUserId($item['from'], ['id', 'name', 'avatar']);
            if (empty($contact)) { // 非客户
                $data['name'] = $item['from'];
                return $data;
            }
            $data['name']                                = $contact['name'];
            empty($contact['avatar']) || $data['avatar'] = file_full_url($contact['avatar']);
        } else {
            ## 员工
            $employee = $this->employeeClient->getWorkEmployeeByWxUserId($item['from'], ['id', 'name', 'thumb_avatar']);
            if (empty($employee)) {
                return $data;
            }
            $data['name']                                                = $employee['name'];
            $item['from'] === $currentWxUserId && $data['isCurrentUser'] = 1;
            empty($employee['thumbAvatar']) || $data['avatar']           = file_full_url($employee['thumbAvatar']);
        }

        return $data;
    }
}
