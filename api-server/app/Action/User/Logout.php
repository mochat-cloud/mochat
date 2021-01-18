<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\User;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Utils\ApplicationContext;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use Qbhy\HyperfAuth\AuthManager;

/**
 * 子账户管理-退出登录.
 *
 * Class Logout.
 * @Controller
 */
class Logout extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var AuthManager
     */
    protected $auth;

    /**
     * @RequestMapping(path="/user/logout", methods="put")
     * @return array 返回数组
     */
    public function handle(): array
    {
        // 清除缓存（用户绑定信息）
        $container = ApplicationContext::getContainer();
        $redis     = $container->get(\Hyperf\Redis\Redis::class);
        $redis->del('mc:user.' . user()['id']);

        // 退出登录
        $guard = $this->auth->guard('jwt');
        $guard->logout();

        return [];
    }

    /**
     * 验证规则.
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * 属性替换.
     * @return array|string[] ...
     */
    protected function attributes(): array
    {
        return [];
    }
}
