<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomWelcome\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomWelcome\Contract\RoomWelcomeContract;

/**
 * 入群欢迎语 - 删除.
 *
 * @Controller
 */
class Destroy extends AbstractAction
{
    use ValidateSceneTrait;
    use AppTrait;

    /**
     * @Inject
     * @var RoomWelcomeContract
     */
    protected $roomServiceService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/roomWelcome/destroy", methods="DELETE")
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) !== 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }
        ## 验证参数
        $this->validated($this->request->all(), 'destroy');
        $id = (int) $this->request->input('id');

        ## 删除入群欢迎语素材
        $client = $this->container->get(RoomWelcomeContract::class);
        $info = $client->getRoomWelcomeById($id);
        $template = $this->wxApp($user['corpIds'][0], 'contact')->external_contact_message_template->delete($info['complexTemplateId']);
        if ($template['errcode'] !== 0) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '删除入群欢迎语素材失败' . $template['errmsg']);
        }
        ## 删除数据
        Db::beginTransaction();
        try {
            $client->deleteRoomWelcome($id);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '欢迎语创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '欢迎语创建失败');
        }

        return [];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'id' => 'required | numeric | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'id.required' => '欢迎语id 必填',
            'id.numeric' => '欢迎语id 必须为数字类型',
        ];
    }
}
