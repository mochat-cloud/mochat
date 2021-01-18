<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Menu;

use App\Constants\Menu\Status;
use App\Contract\RbacMenuServiceInterface;
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
 * 菜单- 菜单状态修改.
 *
 * Class StatusUpdate.
 * @Controller
 */
class StatusUpdate extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RbacMenuServiceInterface
     */
    protected $menuService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/menu/statusUpdate", methods="put")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $params = $this->request->all();
        $this->validated($params, 'update');

        ## 修改菜单状态
        return $this->updateStatus($params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'menuId' => 'required | integer | bail',
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
            'menuId.required' => '菜单id 必填',
            'menuId.integer'  => '菜单id 必须为整型',
            'status.required' => '状态 必填',
            'status.integer'  => '状态 必需为整数',
            'status.in'       => '状态 值必须在列表内：[1,2]',
        ];
    }

    /**
     * 修改菜单状态
     * @param array $params 参数
     * @return array
     */
    private function updateStatus(array $params)
    {
        $menuId = (int) $params['menuId'];

        if ($params['status'] == Status::DISABLE) {
            ## 获取要删除的子菜单id
            $idPath  = '#' . $params['menuId'] . '#';
            $menus   = $this->menuService->getMenusByPath($idPath, ['id']);
            $menuIds = array_column($menus, 'id');

            ## 追加当前菜单
            array_push($menuIds, $menuId);

            ## 当前菜单及子菜单都设置为禁用
            $menuId = $this->menuService->updateRbacMenuByIds($menuIds, ['status' => $params['status']]);
        } else {
            ## 当前菜单设置启用
            $menuId = $this->menuService->updateRbacMenuById($menuId, ['status' => $params['status']]);
        }

        if (! $menuId) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '修改失败');
        }

        return [];
    }
}
