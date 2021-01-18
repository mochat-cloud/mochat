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

use App\Logic\User\UpdateLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 子账户管理- 更新提交.
 *
 * Class Update.
 * @Controller
 */
class Update extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var UpdateLogic
     */
    private $updateLogic;

    /**
     * @RequestMapping(path="/user/update", methods="put")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 获取当前登录用户
        $user = user();
        ## 接收参数
        $params = [
            'userId'     => $this->request->input('userId'),
            'name'       => $this->request->input('userName'),
            'phone'      => $this->request->input('phone'),
            'gender'     => $this->request->input('gender'),
            'department' => $this->request->input('department', ''),
            'roleId'     => $this->request->input('roleId', 0),
            'status'     => $this->request->input('status'),
        ];
        return $this->updateLogic->handle($params, $user);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'userId'   => 'required | integer | min:0 | bail',
            'userName' => 'required | string  | min:1 | bail',
            'phone'    => 'required | string  | size:11 | bail',
            'gender'   => 'required | integer | in:1,2, | bail',
            'status'   => 'required | integer | in:0,1,2, | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'userId.required'   => '用户ID 必填',
            'userId.integer'    => '用户ID 必需为整数',
            'userId.min'        => '用户ID 值必须大于1',
            'userName.required' => '用户名称 必填',
            'userName.string'   => '用户名称 必需为字符串',
            'userName.min'      => '用户名称 字符串长度不可小于1',
            'phone.required'    => '手机号码 必填',
            'phone.string'      => '手机号码 必需为字符串',
            'phone.size'        => '手机号码 字符串长度为固定值：11',
            'gender.required'   => '性别 必填',
            'gender.integer'    => '性别 必需为整数',
            'gender.in'         => '性别 值必须在列表内：[1,2]',
            'status.required'   => '状态 必填',
            'status.integer'    => '状态 必需为整数',
            'status.in'         => '状态 值必须在列表内：[0,1,2]',
        ];
    }
}
