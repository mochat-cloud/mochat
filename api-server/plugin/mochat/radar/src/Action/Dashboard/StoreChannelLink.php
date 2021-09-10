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
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\Utils\Url;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\Radar\Contract\RadarChannelLinkContract;

/**
 * 互动雷达 - 增加渠道链接.
 *
 * Class Store.
 * @Controller
 */
class StoreChannelLink extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RadarChannelLinkContract
     */
    protected $radarChannelLinkService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/radar/storeChannelLink", methods="post")
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) !== 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 参数验证
        $params = $this->request->all();
        $this->validated($params, 'store');

        ## 处理参数
        $data = $this->handleParam($user, $params);
        if (! empty($this->radarChannelLinkService->getRadarChannelLinkByCorpIdRadarIdChannelIdEmployeeId($user['corpIds'][0], $data['radar_id'], $data['channel_id'], $data['employeeId'], ['id']))) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '渠道链接重复');
        }
        ## 创建渠道
        return $this->createRadarChannelLink($user, $data, (int) $params['type']);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'type' => 'required',
            'radar_id' => 'required',
            'channel_id' => 'required',
            'employeeId' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'type.required' => 'type 必传',
            'radar_id.required' => '雷达id 必传',
            'channel_id.required' => '渠道id 必传',
            'employeeId.required' => 'employeeId 必传',
        ];
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @throws \JsonException
     * @return array 响应数组
     */
    private function handleParam(array $user, array $params): array
    {
        ## 基本信息
        return [
            'radar_id' => (int) $params['radar_id'],
            'channel_id' => (int) $params['channel_id'],
            'employeeId' => (int) $params['employeeId'],
            'tenant_id' => isset($params['tenant_id']) ? $params['tenant_id'] : 0,
            'corp_id' => $user['corpIds'][0],
            'create_user_id' => $user['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * 创建链接.
     */
    private function createRadarChannelLink(array $user, array $params, int $type): array
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建渠道链接
            $id = $this->radarChannelLinkService->createRadarChannelLink($params);
            $link = Url::getAuthRedirectUrl(6, $params['radar_id'], [
                'type' => $type,
                'employee_id' => $params['employeeId'],
                'target_id' => $id,
            ]);
            $this->radarChannelLinkService->updateRadarChannelLinkById($id, ['link' => $link]);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '雷达渠道链接创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
        return [$id];
    }
}
