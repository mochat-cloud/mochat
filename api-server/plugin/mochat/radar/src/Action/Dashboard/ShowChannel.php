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
 * 互动雷达-详情-渠道数据.
 * @Controller
 */
class ShowChannel extends AbstractAction
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
     * @RequestMapping(path="/dashboard/radar/showChannel", methods="get")
     */
    public function handle(): array
    {
        ## 验证接受参数
        $params = $this->request->all();
        $this->validated($params);
        ## 获取当前登录用户
        $user = user();

        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 接收参数
        $params = [
            'type' => $this->request->input('type'),
            'id' => $this->request->input('radar_id'),
            'start_time' => $this->request->input('start_time'),
            'end_time' => $this->request->input('end_time'),
            'page' => $this->request->input('page', 1),
            'perPage' => $this->request->input('perPage', 10000),
        ];

        return $this->shop($user, $params);
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
     * 店主.
     * @throws \JsonException
     */
    private function shop(array $user, array $params): array
    {
        $channelList = $this->radarRecordService->getRadarRecordByCorpIdRadarIdGroupByChannelId($user['corpIds'][0], $params);
        $list = [];
        foreach ($channelList as $key => $val) {
            $channel = $this->radarChannelService->getRadarChannelById($val['channelId'], ['name']);
            $list[$key] = [
                'channelId' => $val['channelId'],
                'channelName' => $channel['name'],
                'click_num' => $val['total'],
                'person_num' => $this->radarRecordService->countRadarRecordByCorpIdRadarIdChannelId($user['corpIds'][0], $val['channelId'], $params),
            ];
        }
        return ['list' => $list];
    }
}
