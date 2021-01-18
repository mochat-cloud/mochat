<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkEmployee;

use App\Logic\WorkEmployee\IndexLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 查询 - 列表.
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/workEmployee/index", methods="GET")
     */
    public function handle(): array
    {
        $user = user();
        if (empty($user)) {
            throw new CommonException(ErrorCode::AUTH_UNAUTHORIZED, '登入异常请重新登入');
        }
        // 请求参数
        $params = $this->request->inputs(
            ['corpId', 'status', 'name', 'contactAuth', 'page', 'perPage'],
            ['corpId' => user()['corpIds'], 'status' => 0, 'name' => '', 'contactAuth' => 'all', 'page' => 1, 'perPage' => 10]
        );
        // 校验参数
        $this->validated($params);
        $params['user'] = $user;
        return $this->container->get(IndexLogic::class)->handle($params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'corpId'  => 'required',
            'status'  => 'integer',
            'page'    => 'integer',
            'perPage' => 'integer',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'corpId.required' => '企业微信不能为空',
            'status.integer'  => '成员状态必须为整数',
            'page.integer'    => '页码必须为整数',
            'perPage.integer' => '页码必须为整数',
        ];
    }
}
