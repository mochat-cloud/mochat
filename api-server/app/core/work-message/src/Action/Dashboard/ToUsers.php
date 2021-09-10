<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkMessage\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\User\Logic\Traits\UserTrait;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkMessage\Constants\MsgType;
use MoChat\App\WorkMessage\Contract\WorkMessageContract;
use MoChat\App\WorkMessage\Contract\WorkMessageIndexContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * @Controller
 */
class ToUsers extends AbstractAction
{
    use UserTrait;

    /**
     * @var WorkMessageContract
     */
    protected $workMessageService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomClient;

    /**
     * @Inject
     * @var WorkMessageIndexContract
     */
    protected $workMsgIndexService;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/workMessage/toUsers", methods="GET")
     */
    public function handle(): array
    {
        ## 请求参数.验证
        $corpId = $this->corpId();
        $this->workMessageService = make(WorkMessageContract::class, [$corpId]);
        $workEmployeeId = (int) $this->request->query('workEmployeeId', 0);
        $employeeData = $this->workEmployeeService->getWorkEmployeeById($workEmployeeId, ['id', 'wx_user_id']);
        if (! $employeeData) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '员工不存在');
        }
        $name = $this->request->query('name');
        $toUserType = (int) $this->request->query('toUsertype', 0);
        $options = [
            'page' => $this->request->query('page', 1),
            'perPage' => $this->request->query('perPage', 10),
            'orderByRaw' => '`id` ASC',
        ];
        $where = [
            'from_id' => $employeeData['id'],
            'corp_id' => $corpId,
            'to_type' => $toUserType,
        ];

        ## 获取模型数据
        if ($name) {
            ## 聊天对象 - 列表
            $toData = $this->getToList($corpId, $toUserType, $name);
            ## 检索数据
            $where['to_id'] = array_keys($toData);
            $pageData = $this->workMsgIndexService->getWorkMessageIndexList($where, ['id', 'to_id', 'to_type'], $options);
        } else {
            ## 检索数据
            $pageData = $this->workMsgIndexService->getWorkMessageIndexList($where, ['id', 'to_id', 'to_type'], $options);
            ## 聊天对象 - 列表
            $toData = $this->getToList($corpId, $toUserType, array_column($pageData['data'], 'toId'));
        }
        ## 聊天对象 - 最近一条记聊天录
        $toDataIds = array_keys($toData);
        $msgData = $this->getLastMsg($employeeData['wxUserId'], $toDataIds, $toUserType);

        ## 格式化数据
        $data = [];
        foreach ($pageData['data'] as $item) {
            if (! isset($toData[$item['toId']])) {
                continue;
            }
            $msgIndex = $this->getMsgIndexFlag($item['toType'], (int) $item['toId'], $item['toId']);
            $data[] = [
                'workEmployeeId' => $workEmployeeId,
                'toUsertype' => $item['toType'],
                'toUserId' => $item['toId'],
                'name' => $toData[$item['toId']]['name'] ?? '',
                'alias' => $toData[$item['toId']]['alias'] ?? '',
                'avatar' => $toData[$item['toId']]['avatar'] ?? '',
                'content' => $msgData[$msgIndex]['content'] ?? '',
                'msgDataTime' => isset($msgData[$msgIndex]) ? date('Y-m-d H:i:s', (int) ($msgData[$msgIndex]['msgTime'] * 0.001)) : '',
            ];
        }
        return [
            'page' => [
                'perPage' => $pageData['per_page'],
                'total' => $pageData['total'],
                'totalPage' => $pageData['last_page'],
            ],
            'list' => $data,
        ];
    }

    /**
     * 聊天对象 - 列表数据.
     * @param int $corpId ...
     * @param int $toUserType ...
     * @param array|string $whereParam 表ID或者name查询
     * @return array ...
     */
    protected function getToList(int $corpId, int $toUserType, $whereParam): array
    {
        switch ($toUserType) {
            case 0:
                $fields = ['id', 'name', 'alias', 'avatar'];
                if (is_array($whereParam)) {
                    $toModelData = $this->workEmployeeService->getWorkEmployeesById($whereParam, $fields);
                } else {
                    $toModelData = $this->workEmployeeService->getWorkEmployeesByNameAlias($whereParam, $corpId, $fields);
                }
                break;
            case 1:
                $fields = ['id', 'name', 'nick_name', 'avatar'];
                if (is_array($whereParam)) {
                    $toModelData = $this->workContactService->getWorkContactsById($whereParam, $fields);
                } else {
                    $toModelData = $this->workContactService->getWorkContactsByNameAlias($whereParam, $corpId, $fields);
                }
                break;
            case 2:
                $fields = ['id', 'name'];
                if (is_array($whereParam)) {
                    $toModelData = $this->workRoomClient->getWorkRoomsById($whereParam, $fields);
                } else {
                    $toModelData = $this->workRoomClient->getWorkRoomByCorpIdName([$corpId], $whereParam, $fields);
                }
                break;
            default:
                throw new CommonException(ErrorCode::INVALID_PARAMS, '类型错误');
        }

        return array_reduce($toModelData, function ($carry, $item) {
            isset($item['nickName']) && $item['alias'] = $item['nickName'];
            isset($item['avatar']) || $item['avatar'] = '';
            empty($item['avatar']) || $item['avatar'] = file_full_url($item['avatar']);
            isset($item['alias']) || $item['alias'] = $item['name'];
            $carry[$item['id']] = $item;
            return $carry;
        }, []);
    }

    /**
     * 获取最近一条消息.
     * @param string $wxUserId ...
     * @param array $toIds ...
     * @param int $toUserType ...
     * @throws \JsonException
     * @return array ...
     */
    protected function getLastMsg(string $wxUserId, array $toIds, int $toUserType): array
    {
        $msgData = $this->workMessageService->getWorkMessagesLast($wxUserId, $toIds, $toUserType, [
            'id', 'tolist_type', 'tolist_id', 'room_id', 'msg_type', 'content', 'msg_time',
        ]);
        return array_reduce($msgData, function ($carry, $item) {
            $item['content'] = $this->typeContent((int) $item['msgType'], $item['content']);
            $item['tolistId'] = json_decode($item['tolistId'], true, 512, JSON_THROW_ON_ERROR);
            $msgIndex = empty($item['tolistId']) ? 0 : $this->getMsgIndexFlag($item['tolistType'], (int) $item['tolistId'][0], $item['roomId']);
            $carry[$msgIndex] = $item;
            return $carry;
        }, []);
    }

    /**
     * 获取类型消息.
     * @param int $type ...
     * @param null|string $content ...json
     * @return string ...
     */
    protected function typeContent(int $type, ?string $content): string
    {
        if (empty($content)) {
            return '';
        }
        $contentArr = json_decode($content, true);
        if (empty($contentArr)) {
            return '';
        }
        if ($type === MsgType::TEXT) {
            return $contentArr['content'];
        }
        if (in_array($type, MsgType::$fixedType, true)) {
            return MsgType::getMessage($type);
        }
        return '其它类型消息';
    }

    protected function getMsgIndexFlag(int $toType, int $userId, int $roomId): string
    {
        if ($toType === 2) {
            return $toType . '_' . $roomId;
        }
        return $toType . '_' . $userId;
    }
}
