<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\User\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\User\Logic\StoreLogic;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 子账户管理- 创建提交.
 *
 * Class Store.
 * @Controller
 */
class Store extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var StoreLogic
     */
    protected $storeLogic;

    /**
     * @RequestMapping(path="/dashboard/user/store", methods="post")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
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
            'name' => $this->request->input('userName'),
            'phone' => $this->request->input('phone'),
            'gender' => $this->request->input('gender'),
            'status' => $this->request->input('status'),
            'roleId' => $this->request->input('roleId', 0),
            'password' => $this->request->input('password', 123456),
        ];

        // 添加用户的默认租户id为当前用户的租户id
        $params['tenant_id'] = $user['tenantId'];

        return $this->storeLogic->handle($params, $user);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'userName' => 'required | string | min:1 | bail',
            'phone' => 'required | string | size:11 | bail',
            'gender' => 'required | integer | in:1,2, | bail',
            'status' => 'required | integer | in:0,1,2, | bail',
            'password' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'userName.required' => '用户名称 必填',
            'userName.string' => '用户名称 必需为字符串',
            'userName.min' => '用户名称 字符串长度不可小于1',
            'phone.required' => '手机号码 必填',
            'phone.string' => '手机号码 必需为字符串',
            'phone.size' => '手机号码 字符串长度为固定值：11',
            'gender.required' => '性别 必填',
            'gender.integer' => '性别 必需为整数',
            'gender.in' => '性别 值必须在列表内：[1,2]',
            'status.required' => '状态 必填',
            'status.integer' => '状态 必需为整数',
            'status.in' => '状态 值必须在列表内：[0,1,2]',
            'password' => '密码 必填',
        ];
    }
}
