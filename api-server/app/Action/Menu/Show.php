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

use App\Constants\Menu\Level;
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
 * 查询 - 详情.
 * @Controller
 */
class Show extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RbacMenuServiceInterface
     */
    protected $menuService;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/menu/show", methods="GET")
     */
    public function handle(): array
    {
        ## 接受验证参数
        $params = $this->request->all();
        $this->validated($params, 'store');

        ## 菜单详情
        $menuId = (int) $params['menuId'];
        $menu   = $this->menuService->getRbacMenuById($menuId);
        if (empty($menu)) {
            throw new CommonException(ErrorCode::URI_NOT_FOUND, '菜单不存在');
        }

        ## 数据处理
        return $this->handleData($menu);
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
     * 数据处理.
     * @param array $menu 菜单数据
     */
    private function handleData(array $menu): array
    {
        $data = [
            'menuId'         => $menu['id'],
            'name'           => $menu['name'],
            'level'          => $menu['level'],
            'levelName'      => ! empty($menu['level']) ? Level::getMessage($menu['level']) : '',
            'status'         => $menu['status'],
            'icon'           => ! empty($menu['icon']) ? $menu['icon'] : '',
            'linkUrl'        => ! empty($menu['linkUrl']) ? $menu['linkUrl'] : '',
            'isPageMenu'     => $menu['isPageMenu'],
            'linkType'       => ! empty($menu['linkType']) ? $menu['linkType'] : '',
            'dataPermission' => ! empty($menu['dataPermission']) ? $menu['dataPermission'] : '',
            'firstMenuId'    => '',
            'secondMenuId'   => '',
            'thirdMenuId'    => '',
            'fourthMenuId'   => '',
        ];

        if (! empty($menu['path'])) {
            $menu['path'] = str_replace('#', '', $menu['path']);
            if (! strpos($menu['path'], '-')) {
                $data['firstMenuId'] = $menu['path'];
            } else {
                $pathArr              = explode('-', $menu['path']);
                $data['firstMenuId']  = ! empty($pathArr[0]) ? (int) $pathArr[0] : '';
                $data['secondMenuId'] = ! empty($pathArr[1]) ? (int) $pathArr[1] : '';
                $data['thirdMenuId']  = ! empty($pathArr[2]) ? (int) $pathArr[2] : '';
                $data['fourthMenuId'] = ! empty($pathArr[3]) ? (int) $pathArr[3] : '';
            }
        }
        return $data;
    }
}
