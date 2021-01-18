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
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 菜单-菜单已使用图标.
 *
 * Class IconIndex.
 * @Controller
 */
class IconIndex extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RbacMenuServiceInterface
     */
    protected $menuService;

    /**
     * @RequestMapping(path="/menu/iconIndex", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 获取数据
        $columns = ['icon'];
        $menu    = $this->menuService->getMenusBySearch([], $columns);
        if (empty($menu)) {
            return [];
        }
        ## 处理数据
        $icon = [];
        foreach ($menu as $v) {
            ! empty($v['icon']) && $icon[] = $v['icon'];
        }
        return $icon;
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [];
    }
}
