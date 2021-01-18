<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkDepartment;

use App\Contract\CorpServiceInterface;
use App\Logic\WorkDepartment\ShowEmployeeLogic;
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
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @Inject
     * @var ShowEmployeeLogic
     */
    private $showEmployeeLogic;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/workDepartment/showEmployee", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());

        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }
        $params = [
            'departmentId' => $this->request->input('departmentId', null),
            'page'         => $this->request->input('page', 1),
            'perPage'      => $this->request->input('perPage', 10),
        ];

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
            'departmentId' => 'required | integer | min:0 | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'departmentId.required' => '部门ID 必填',
            'departmentId.integer'  => '部门ID 必须为整数',
            'departmentId.min  '    => '部门ID 不可小于1',
        ];
    }
}
