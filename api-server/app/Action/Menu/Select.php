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
 * 菜单-菜单下拉列表.
 *
 * Class Select.
 * @Controller
 */
class Select extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RbacMenuServiceInterface
     */
    protected $menuService;

    /**
     * @RequestMapping(path="/menu/select", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 获取数据
        $columns = ['id', 'name', 'level', 'parent_id', 'data_permission'];
        $menu    = $this->menuService->getMenusBySearch([], $columns);

        ## 处理数据
        return $this->handleData($menu);
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

    /**
     * @param array $menu 菜单数据
     * @return array 响应数组
     */
    private function handleData(array $menu): array
    {
        if (empty($menu)) {
            return [];
        }
        return $this->recursion($menu);
    }

    /**
     * 无限递归数据.
     * @param array $data 数据
     * @param int $id 主键id
     * @return array
     */
    private function recursion(array $data, int $id = 0)
    {
        $tree = [];
        foreach ($data as $key => $val) {
            if ($val['parentId'] != $id) {
                continue;
            }
            $val['menuId'] = $val['id'];

            unset($data[$key]);
            $val['children'] = $this->recursion($data, $val['id']);
            $tree[]          = $val;
        }

        return $tree;
    }
}
