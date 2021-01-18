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

use App\Contract\RbacMenuServiceInterface;
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
 * 删除 - 动作.
 * @Controller
 */
class Destroy extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * 菜单.
     * @Inject
     * @var RbacMenuServiceInterface
     */
    protected $menuService;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/menu/destroy", methods="DELETE")
     */
    public function handle(): array
    {
        ## 验证接受参数
        $this->validated($this->request->all(), 'destroy');
        $menuId = (int) $this->request->input('menuId');

        ## 删除该菜单下的所有子菜单
        $this->deleteChildrenMenu($menuId);

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
            'menuId' => 'required | integer | bail',
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
        ];
    }

    /**
     * @param int $menuId 菜单id
     * @return int
     */
    private function deleteChildrenMenu(int $menuId)
    {
        ## 获取要删除的子菜单id
        $idPath  = '#' . $menuId . '#';
        $menus   = $this->menuService->getMenusByPath($idPath, ['id']);
        $menuIds = array_column($menus, 'id');

        ## 追加当前菜单id
        array_push($menuIds, $menuId);

        ## 批量删除子菜单
        $res = $this->menuService->deleteRbacMenus($menuIds);

        if (! $res) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '子菜单删除失败');
        }

        return $res;
    }
}
