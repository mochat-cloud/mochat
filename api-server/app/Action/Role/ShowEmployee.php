<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Role;

use App\Logic\Role\ShowEmployeeLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 组织管理- 查看人员列表.
 *
 * Class ShowEmployee.
 * @Controller
 */
class ShowEmployee extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ShowEmployeeLogic
     */
    protected $showEmployeeLogic;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/role/showEmployee", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 获取当前登录用户
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 接受参数并验证
        $params = [
            'roleId'  => (int) $this->request->input('roleId'),
            'perPage' => (int) $this->request->input('perPage', '10'),
            'page'    => (int) $this->request->input('page', 1),
        ];
        $this->validated($params);

        return $this->showEmployeeLogic->handle($user, $params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'roleId' => 'required | integer | min:0 | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'roleId.required' => '角色ID 必填',
            'roleId.integer'  => '角色ID 必需为整数',
            'roleId.min  '    => '角色ID 不可小于1',
        ];
    }
}
