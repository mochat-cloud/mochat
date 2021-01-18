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

use App\Contract\RbacRoleServiceInterface;
use App\Middleware\PermissionMiddleware;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 角色 - 角色状态修改.
 *
 * Class StatusUpdate.
 * @Controller
 */
class StatusUpdate extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * 角色.
     * @Inject
     * @var RbacRoleServiceInterface
     */
    protected $roleService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/role/statusUpdate", methods="put")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all(), 'update');
        ## 接收参数
        $roleId = (int) $this->request->input('roleId');
        $status = (int) $this->request->input('status');

        try {
            ## 数据入表
            $this->roleService->updateRbacRoleById($roleId, ['status' => $status]);
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('%s [%s] %s', '角色状态修改失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '角色状态修改失败');
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
            'roleId' => 'required | integer | bail',
            'status' => 'required | integer | in:1,2, |bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'roleId.required' => '角色id 必填',
            'roleId.integer'  => '角色id 必须为整型',
            'status.required' => '状态 必填',
            'status.integer'  => '状态 必需为整数',
            'status.in'       => '状态 值必须在列表内：[1,2]',
        ];
    }
}
