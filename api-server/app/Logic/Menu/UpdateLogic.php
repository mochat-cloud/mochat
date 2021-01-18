<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\Menu;

use App\Constants\Menu\IsPageMenu;
use App\Constants\Menu\Level;
use App\Contract\RbacMenuServiceInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 菜单-添加.
 *
 * Class UpdateLogic
 */
class UpdateLogic
{
    /**
     * @Inject
     * @var RbacMenuServiceInterface
     */
    protected $menuService;

    /**
     * @var int
     */
    protected $corpId;

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理请求参数
        $menuId = (int) $params['menuId'];
        $data   = $this->handleParams($params);

        ## 查询数据
        return $this->updateMenu($menuId, $data);
    }

    /**
     * 处理参数.
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function handleParams(array $params): array
    {
        ## 获取菜单详情
        $menuId = (int) $params['menuId'];
        $menu   = $this->menuService->getRbacMenuById($menuId);

        $data = [
            'name'         => $params['name'],
            'is_page_menu' => IsPageMenu::YES,
        ];
        ## 菜单一、二、三级有图标
        if (in_array($menu['level'], [Level::FIRST_LEVEL, Level::SECOND_LEVEL, Level::THIRD_LEVEL])) {
            $data['icon'] = $params['icon'];
        }
        ## 菜单三、四、五有链接
        if (in_array($menu['level'], [Level::THIRD_LEVEL, Level::FOURTH_LEVEL, Level::FIFTH_LEVEL])) {
            $data['link_type']                                           = $params['linkType'];
            $data['is_page_menu']                                        = isset($params['isPageMenu']) ? $params['isPageMenu'] : IsPageMenu::YES;
            $data['link_url']                                            = $params['linkUrl'];
            isset($params['dataPermission']) && $data['data_permission'] = $params['dataPermission'];
        }

        return $data;
    }

    /**
     * @param int $menuId 菜单id
     * @param array $data 创建数据
     * @return array 响应数组
     */
    private function updateMenu(int $menuId, array $data): array
    {
        ## 验证linkUrl是否重复
        if (isset($data['link_url'])) {
            $this->nameIsUnique($data['link_url'], $menuId);
        }

        ## 修改菜单
        $menuId = $this->menuService->updateRbacMenuById($menuId, $data);

        if (! $menuId) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '修改失败');
        }

        return [];
    }

    /**
     * 验证菜单的linkUrl是否存在.
     *
     * @param string $linkUrl 链接
     * @param int $menuId 菜单id
     */
    private function nameIsUnique(string $linkUrl, int $menuId): bool
    {
        $existData = $this->menuService->getMenuByLinkUrl($linkUrl, ['id']);
        if (empty($existData)) {
            return true;
        }
        $existData['id'] == $menuId && $existData = [];
        if (! empty($existData)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, $linkUrl . '-该LinkUrl已存在，不能重复');
        }
        return true;
    }
}
