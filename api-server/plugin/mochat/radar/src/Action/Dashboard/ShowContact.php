<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Radar\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\Radar\Contract\RadarChannelContract;
use MoChat\Plugin\Radar\Contract\RadarContract;
use MoChat\Plugin\Radar\Contract\RadarRecordContract;

/**
 * 互动雷达-详情-客户数据.
 * @Controller
 */
class ShowContact extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RadarContract
     */
    protected $radarService;

    /**
     * @Inject
     * @var RadarRecordContract
     */
    protected $radarRecordService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var RadarChannelContract
     */
    protected $radarChannelService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/radar/showContact", methods="get")
     */
    public function handle(): array
    {
        ## 验证接受参数
        $params = $this->request->all();
        $this->validated($params);
        ## 获取当前登录用户
        $user = user();

        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) !== 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 接收参数
        $params = [
            'type' => $this->request->input('type'),
            'radar_id' => $this->request->input('radar_id'),
            'contactName' => $this->request->input('contactName'),
            'channelId' => $this->request->input('channelId'),
            'employeeId' => $this->request->input('employeeId'),
            'start_time' => $this->request->input('start_time'),
            'end_time' => $this->request->input('end_time'),
            'page' => $this->request->input('page', 1),
            'perPage' => $this->request->input('perPage', 10000),
        ];
        $data = $this->handleParams($user, $params);

        return $this->shop($data, $params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'type' => 'required | integer | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'type.required' => '类型 必填',
            'type.integer' => '类型 必须为整型',
        ];
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParams(array $user, array $params): array
    {
        $where['corp_id'] = $user['corpIds'][0];
        $where['radar_id'] = (int) $params['radar_id'];
        if (isset($params['contactName']) && ! empty($params['contactName'])) {
            $where[] = ['nickname', 'LIKE', '%' . $params['contactName'] . '%'];
        }
        if (isset($params['channelId']) && ! empty($params['channelId'])) {
            $where['channel_id'] = (int) $params['channelId'];
        }
        if (isset($params['employeeId']) && ! empty($params['employeeId'])) {
            $where['employee_id'] = $params['employeeId'];
        }
        if (isset($params['start_time']) && ! empty($params['start_time'])) {
            $where[] = ['created_at', '>', $params['start_time']];
            $where[] = ['created_at', '<', $params['end_time']];
        }
        $options = [
            'perPage' => $params['perPage'],
            'page' => $params['page'],
            'orderByRaw' => 'id desc',
        ];

        return ['where' => $where, 'options' => $options];
    }

    /**
     * 店主.
     * @throws \JsonException
     */
    private function shop(array $data, array $params): array
    {
        $columns = ['channel_id', 'nickname', 'contact_id', 'employee_id', 'created_at', 'corp_id'];
        $contactList = $this->radarRecordService->getRadarRecordList($data['where'], $columns, $data['options']);
        $list = [];
        $data2 = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($contactList['data']) ? $data2 : $this->handleData($contactList, $params);
    }

    /**
     * 数据处理.
     * @param array $contactList 列表数据
     * @param array $params 参数数组
     * @return array 响应数组
     */
    private function handleData(array $contactList, array $params): array
    {
        $list = [];
        $radar = $this->radarService->getRadarById((int) $params['radar_id'], ['title']);
        // TODO 优化在循环中查表，改为提前统一查好再组装数据
        foreach ($contactList['data'] as $key => $val) {
            $contact = $this->workContactService->getWorkContactById($val['contactId'], ['name', 'avatar']);
            $contactAvatar = empty($contact) ? '' : file_full_url($contact['avatar']);
            $contactName = isset($contact['name']) ? $contact['name'] : $val['nickname'];
            $employee = $this->workEmployeeService->getWorkEmployeeById($val['employeeId'], ['name', 'avatar']);
            $employeeAvatar = empty($employee) ? '' : file_full_url($employee['avatar']);
            $channel = $this->radarChannelService->getRadarChannelById($val['channelId'], ['name']);
            $clickInfo = $this->radarRecordService->getRadarRecordByCorpIdContactIdSearch($val['corpId'], $val['contactId'], $params, ['channel_id', 'content', 'created_at']);
            foreach ($clickInfo as $k => $v) {
                $channelInfo = $this->radarChannelService->getRadarChannelById((int) $v['channelId'], ['name']);
                // TODO 优化掉 HTML 标签
                $clickInfo[$k]['content'] = "<img src='{$contactAvatar}' />{$contactName}客户 打开了<img src='{$employeeAvatar}' />{$employee['name']}员工 在「{$channelInfo['name']}」里发送的雷达链接 「{$radar['title']}」";
                unset($clickInfo[$k]['channelId']);
            }
            $list[$key] = [
                'contactName' => $contactName,
                'avatar' => $contactAvatar,
                'employee' => $employee['name'],
                'createdAt' => $val['createdAt'],
                'channel' => $channel['name'],
                'click_num' => $this->radarRecordService->countRadarRecordByCorpIdContactIdSearch($val['corpId'], $val['contactId'], $params),
                'click_info' => $clickInfo,
                'contact_id' => $val['contactId'],
                'employee_id' => $val['employeeId'],
            ];
        }
        $data['page']['total'] = $contactList['total'];
        $data['page']['totalPage'] = $contactList['last_page'];
        $data['list'] = $list;
        return $data;
    }
}
