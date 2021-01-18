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

use App\Contract\UserServiceInterface;
use Hyperf\Contract\StdoutLoggerInterface;
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
 * 子账户管理- 更新员工账户登录密码
 *
 * Class PasswordUpdate.
 * @Controller
 */
class PasswordUpdate extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var UserServiceInterface
     */
    protected $userService;

    /**
     * @Inject
     * @var AuthManager
     */
    protected $authManager;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/user/passwordUpdate", methods="put")
     * @return array 返回数组
     */
    public function index()
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 当前登录用户
        $user = user();
        ## 接收参数
        $oldPassword = $this->request->input('oldPassword');
        $newPassword = $this->request->input('newPassword');

        ## 验证userId的有效性
        $userInfo = $this->userService->getUserById((int) $user['id'], ['id', 'password']);
        if (empty($userInfo)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '当前账户不存在，不可操作');
        }

        ## 旧密码验证
        $guard = $this->authManager->guard('jwt');
        /** @var JWTManager $jwt */
        $jwt      = $guard->getJwtManager();
        $checkRes = $jwt->getEncrypter()->check($oldPassword, $userInfo['password']);
        if (! $checkRes) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '旧密码错误，不可操作');
        }
        ## 生成新密码
        $newPassword = $jwt->getEncrypter()->signature($newPassword);

        try {
            ## 数据入库
            $this->userService->updateUserPasswordById((int) $userInfo['id'], $newPassword);
            ## 退出登录
            $guard->logout();
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('%s [%s] %s', '账户更新密码失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '账户更新密码失败');
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
            'oldPassword'      => 'required|min:1|alpha_num|bail',
            'newPassword'      => 'required|min:1|alpha_num|bail',
            'againNewPassword' => 'required|same:newPassword|bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'oldPassword.required'      => '旧密码 必填',
            'oldPassword.min'           => '旧密码 不可为空',
            'oldPassword.alpha_num'     => '旧密码 组成必须是字母或数字',
            'newPassword.required'      => '新密码 必填',
            'newPassword.min'           => '新密码 不可为空',
            'newPassword.alpha_num'     => '新密码 组成必须是字母或数字',
            'againNewPassword.required' => '确认新密码 必填',
            'againNewPassword.same'     => '确认新密码 必须和新密码一致',
        ];
    }
}
