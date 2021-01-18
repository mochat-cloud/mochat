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

use App\Constants\User\Status;
use App\Contract\UserServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use Qbhy\HyperfAuth\AuthManager;
use Qbhy\SimpleJwt\JWTManager;

/**
 * 登陆验证
 * @Controller
 */
class Auth extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var AuthManager
     */
    protected $auth;

    /**
     * @Inject
     * @var UserServiceInterface
     */
    protected $user;

    /**
     * @RequestMapping(path="/user/auth", methods="POST")
     */
    public function handle(): array
    {
        // 请求参数
        $params = $this->request->inputs(['phone', 'password']);

        // 类型验证
        $this->validated($params);

        // 模型数据
        $userData = $this->user->getUserByPhone($params['phone'], ['id', 'status', 'password']);
        if (! $userData) {
            throw new CommonException(ErrorCode::AUTH_LOGIN_FAILED);
        }
        // 判断账户状态
        if ($userData['status'] != Status::NORMAL) {
            throw new CommonException(ErrorCode::ACCESS_REFUSE, sprintf('账户%s，无法登录', Status::getMessage($userData['status'])));
        }

        // 逻辑处理
        $guard = $this->auth->guard('jwt');
        /** @var JWTManager $jwt */
        $jwt      = $guard->getJwtManager();
        $checkRes = $jwt->getEncrypter()->check($params['password'], $userData->password);
        if (! $checkRes) {
            throw new CommonException(ErrorCode::AUTH_LOGIN_FAILED);
        }

        // 响应参数
        return [
            'token'  => $guard->login($userData),
            'expire' => $jwt->getTtl(),
        ];
    }

    /**
     * 验证规则.
     */
    protected function rules(): array
    {
        return [
            'phone'    => 'required|numeric',
            'password' => 'required',
        ];
    }

    /**
     * 属性替换.
     * @return array|string[] ...
     */
    protected function attributes(): array
    {
        return [
            'phone'    => '手机号',
            'password' => '密码',
        ];
    }
}
