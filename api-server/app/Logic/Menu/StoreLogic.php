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

use App\Constants\Menu\DataPermission;
use App\Constants\Menu\IsPageMenu;
use App\Constants\Menu\Level;
use App\Constants\Menu\LinkType;
use App\Contract\RbacMenuServiceInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 菜单-更新.
 *
 * Class StoreLogic
 */
class StoreLogic
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
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理请求参数
        $data = $this->handleParams($user, $params);

        ## 查询数据
        return $this->createMenu($data);
    }

    /**
     * 给某菜单批量创建子集菜单-批量.
     *
     * @param array $data 菜单数据 必须参数为[['name' => 'XXX', 'link_type' => 1, 'link_url' => '/path#get']]
     * @param string $parentLinkUrl 父菜单的链接地址
     */
    public function createMenus(array $data, string $parentLinkUrl = ''): bool
    {
        ## 根据linkUrl获取菜单
        $parentMenu = $this->menuService->getMenuByLinkUrl($parentLinkUrl, ['id', 'level', 'path']);

        if (empty($data)) {
            return false;
        }
        ## 处理数据并创建菜单
        foreach ($data as $key => $val) {
            $level      = ! empty($parentLinkUrl) ? $parentMenu['level'] + 1 : 1;
            $createData = [
                'name'            => $val['name'],
                'level'           => $level,
                'parent_id'       => ! empty($parentLinkUrl) ? $parentMenu['id'] : 0,
                'path'            => ! empty($parentLinkUrl) ? $parentMenu['path'] : '',
                'icon'            => ! empty($val['icon']) ? $val['icon'] : '',
                'is_page_menu'    => ! empty($val['is_page_menu']) ? $val['is_page_menu'] : IsPageMenu::YES,
                'link_type'       => empty($val['link_type']) ? LinkType::INSIDE : LinkType::OUTSIDE,
                'link_url'        => $val['link_url'],
                'data_permission' => ! empty($val['data_permission']) ? $val['data_permission'] : DataPermission::CLOSE,
                'operate_id'      => 0,
                'operate_name'    => '系统',
                'created_at'      => date('Y-m-d H:i:s'),
            ];

            ## 创建菜单
            try {
                $this->createMenu($createData);
            } catch (\Throwable $e) {
                $this->logger->error(sprintf('%s [%s] %s', '菜单添加失败', date('Y-m-d H:i:s'), $e->getMessage()));
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $user 当前登录用户
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function handleParams(array $user, array $params): array
    {
        $data = [
            'name'         => $params['name'],
            'level'        => $params['level'],
            'operate_id'   => $user['id'],
            'operate_name' => $user['name'],
            'is_page_menu' => IsPageMenu::YES,
            'created_at'   => date('Y-m-d H:i:s'),
        ];

        if ($params['level'] == Level::FIRST_LEVEL) {
            $data['parentId'] = 0;
            $data['path']     = '';
        }
        if ($params['level'] == Level::SECOND_LEVEL) {
            if (empty($params['firstMenuId'])) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '一级菜单id不能为空');
            }
            $data['parentId'] = $params['firstMenuId'];
            $data['path']     = '#' . $params['firstMenuId'] . '#';
        }
        if ($params['level'] == Level::THIRD_LEVEL) {
            if (empty($params['firstMenuId'])) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '一级菜单id不能为空');
            }
            if (empty($params['secondMenuId'])) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '二级菜单id不能为空');
            }
            $data['parentId'] = $params['secondMenuId'];
            $data['path']     = '#' . $params['firstMenuId'] . '#-#' . $params['secondMenuId'] . '#';
        }
        if ($params['level'] == Level::FOURTH_LEVEL) {
            if (empty($params['firstMenuId'])) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '一级菜单id不能为空');
            }
            if (empty($params['secondMenuId'])) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '二级菜单id不能为空');
            }
            if (empty($params['thirdMenuId'])) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '三级菜单id不能为空');
            }
            $data['parentId'] = $params['thirdMenuId'];
            $data['path']     = '#' . $params['firstMenuId'] . '#-#' . $params['secondMenuId'] . '#-#' . $params['thirdMenuId'] . '#';
        }
        if ($params['level'] == Level::FIFTH_LEVEL) {
            if (empty($params['firstMenuId'])) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '一级菜单id不能为空');
            }
            if (empty($params['secondMenuId'])) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '二级菜单id不能为空');
            }
            if (empty($params['thirdMenuId'])) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '三级菜单id不能为空');
            }
            if (empty($params['fourthMenuId'])) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '四级菜单id不能为空');
            }
            $data['parentId'] = $params['fourthMenuId'];
            $data['path']     = '#' . $params['firstMenuId'] . '#-#' . $params['secondMenuId'] . '#-#' . $params['thirdMenuId'] . '#-#' . $params['fourthMenuId'] . '#';
        }
        if (in_array($params['level'], [Level::THIRD_LEVEL, Level::FOURTH_LEVEL, Level::FIFTH_LEVEL])) {
            $data['link_type']       = $params['linkType'];
            $data['link_url']        = $params['linkUrl'];
            $data['data_permission'] = isset($params['dataPermission']) ? $params['dataPermission'] : DataPermission::CLOSE;
        }
        if (in_array($params['level'], [Level::FOURTH_LEVEL, Level::FIFTH_LEVEL])) {
            if (empty($params['isPageMenu'])) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '请选择页面菜单');
            }
            $data['is_page_menu'] = $params['isPageMenu'];
        }

        if (in_array($params['level'], [Level::FIRST_LEVEL, Level::SECOND_LEVEL])) {
            $data['icon']     = $params['icon'];
            $data['link_url'] = 'path/' . time();
        }

        return $data;
    }

    /**
     * 菜单创建-单条.
     * @param array $createData 创建数据
     * @return array 响应数组
     */
    private function createMenu(array $createData): array
    {
        $this->nameIsUnique($createData['link_url']);
        //开启事务
        Db::beginTransaction();
        try {
            ## 添加菜单
            $menuId = $this->menuService->createRbacMenu($createData);
            ## 根据菜单id获取菜单
            $menu = $this->menuService->getRbacMenuById($menuId, ['path']);
            $path = ! empty($menu['path']) ? $menu['path'] . '-' . '#' . $menuId . '#' : '#' . $menuId . '#';

            ## 更新菜单path
            $this->menuService->updateRbacMenuById($menuId, ['path' => $path]);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage());
        }

        return [];
    }

    /**
     * 验证菜单的linkUrl是否存在.
     * @param string $linkUrl 链接
     */
    private function nameIsUnique(string $linkUrl): bool
    {
        $existData = $this->menuService->getMenuByLinkUrl($linkUrl, ['id']);
        if (! empty($existData)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, $linkUrl . '-该LinkUrl已存在，不能重复');
        }
        return true;
    }
}
