<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkDepartment;

use App\Constants\WorkDepartment\Level;
use App\Contract\WorkDepartmentServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 部门-列表.
 *
 * Class PageIndexLogic
 */
class PageIndexLogic
{
    /**
     * @Inject
     * @var WorkDepartmentServiceInterface
     */
    protected $departmentService;

    /**
     * @var int
     */
    protected $page;

    /**
     * @var int
     */
    protected $perPage = 10;

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理请求参数
        $where = $this->handleParams($user, $params);

        ## 查询数据
        return $this->getWorkDepartment($where);
    }

    /**
     * @param array $user 当前登录用户
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function handleParams(array $user, array $params): array
    {
        ## 分页数据
        $this->perPage = $params['perPage'];
        $this->page    = $params['page'];

        ## 列表查询条件
        $where = [];
        ## 企业id
        $where['corp_id'] = (int) $user['corpIds'][0];
        ## 组织名称搜索
        if (! empty($params['name']) && empty($params['parentName'])) {
            $departmentIdArr = $this->getDepartmentByName($user['corpIds'][0], $params['name']);
            if (empty($departmentIdArr)) {
                $where['id'] = [];
                return $where;
            }

            $where['id'] = $departmentIdArr;
        }

        ## 根据父级名称查询子部门
        if (! empty($params['parentName']) && empty($params['name'])) {
            $departmentIdArr = $this->getDepartmentByName($user['corpIds'][0], $params['parentName']);
            if (empty($departmentIdArr)) {
                $where['id'] = [];
                return $where;
            }
            $where['id'] = $departmentIdArr;
        }

        ## 根据父级名称和组织名称同时搜索
        if (! empty($params['parentName']) && ! empty($params['name'])) {
            $departIdsByName       = $this->getDepartmentByName($user['corpIds'][0], $params['name']);
            $departIdsByParentName = $this->getDepartmentByName($user['corpIds'][0], $params['parentName']);
            $departIds             = array_intersect($departIdsByName, $departIdsByParentName);
            if (empty($departIds)) {
                $where['id'] = [];
                return $where;
            }
            $where['id'] = $departIds;
        }

        return $where;
    }

    /**
     * 根据父级名称获取子级的部门id-排除父级本身.
     * @param int $corpId 企业id
     * @param string $name 部门名称
     * @return array 返回部门
     */
    private function getDepartmentByName(int $corpId, string $name): array
    {
        ## 模糊搜索部门数据
        $department = $this->departmentService->getWorkDepartmentsByCorpIdName([$corpId], $name, ['id', 'path']);
        if (empty($department)) {
            return [];
        }
        ## 获取每一级别的顶级部门
        $likeWhere = [];
        foreach ($department as $k => $v) {
            if (strpos($v['path'], '-')) {
                $likeWhere[] = str_replace('#', '', explode('-', $v['path'])[1]);
            } else {
                $likeWhere[] = str_replace('#', '', $v['path']);
            }
        }

        ## 父级部门以下的子部门id
        $likeWhere = array_unique($likeWhere);

        $childrenIds = $this->departmentService->getWorkDepartmentsByCorpIdPath($corpId, $likeWhere, ['id']);
        if (empty($childrenIds)) {
            return [];
        }

        return array_column($childrenIds, 'id');
    }

    /**
     * 获取部门列表-分页.
     *
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function getWorkDepartment(array $params): array
    {
        ## 分页查询数据表
        $columns = ['id', 'name', 'level', 'parent_id'];

        if (isset($params['id']) && empty($params['id'])) {
            return [
                'page' => [
                    'perPage'   => 10,
                    'total'     => '0',
                    'totalPage' => '0',
                ],
                'list' => [],
            ];
        }

        $workDepartment = $this->departmentService->getWorkDepartmentsBySearch($params, $columns);

        ## 返回数据
        $data = [
            'page' => [
                'perPage'   => 10,
                'total'     => '0',
                'totalPage' => '0',
            ],
            'list' => [],
        ];

        return empty($workDepartment) ? $data : $this->handleData($data, $workDepartment);
    }

    /**
     * 组织数据.
     * @param array $data 请求参数
     * @param array $workDepartment 部门列表 //todo::这种方式实现不了 path
     * @return array 响应数组
     */
    private function handleData(array $data, array $workDepartment): array
    {
        //处理子部门
        $items = [];
        foreach ($workDepartment as &$v) {
            if ($v['level'] == 0) {
                continue;
            }
            $items[$v['id']] = $v;
        }

        $tree = [];
        foreach ($items as $k => $item) {
            $items[$k]['departmentId'] = $item['id'];
            $items[$k]['level']        = ! empty($item['level']) ? Level::getMessage($item['level']) : '';
            if (isset($items[$item['parentId']])) {
                $items[$item['parentId']]['children'][] = &$items[$k];
            } else {
                $tree[] = &$items[$k];
            }
        }
        unset($items);

        ## 无限递归处理数据
        $pathId = 1;
        foreach ($tree as $kkk => &$vvv) {
            $vvv['departmentPath'] = $pathId;
            if (! empty($vvv['children'])) {
                $vvv['children'] = $this->rec($vvv['children'], (string) $pathId);
            }
            ++$pathId;
        }

        $total     = count($tree);
        $totalPage = ceil($total / $this->perPage);
        $offset    = ($this->page - 1) * $this->perPage;

        ## 分页数据
        $data['page']['total']     = $total;
        $data['page']['totalPage'] = $totalPage;
        $data['list']              = array_slice($tree, $offset, $this->perPage);

        return $data;
    }

    /**
     * 无限递归数据.
     *
     * @param array $data 数据
     * @param string $path 路径
     * @return array 响应数组
     */
    private function rec(array $data, string $path = ''): array
    {
        $pathId = 1;
        foreach ($data as $kkk => &$vvv) {
            $vvv['departmentPath'] = $path . '-' . $pathId;
            if (! empty($vvv['children'])) {
                $vvv['children'] = $this->rec($vvv['children'], (string) $vvv['departmentPath']);
            }
            ++$pathId;
        }

        return $data;
    }

    /**
     * 无限递归数据.
     *
     * @param array $data 数据
     * @param int $id 主键id
     * @param string $path 路径
     * @return array 响应数组
     */
    private function recursion(array $data, int $id = 1, string $path = ''): array
    {
        $tree    = [];
        $pathKey = 1;
        foreach ($data as $key => $val) {
            if ($val['parentId'] != $id) {
                continue;
            }
            $val['departmentId']   = $val['id'];
            $val['departmentPath'] = $val['parentId'] ? $path . '-' . $pathKey : $pathKey;
            ++$pathKey;
            $val['departmentPath'] = (string) $val['departmentPath'];
            $val['level']          = ! empty($val['level']) ? Level::getMessage($val['level']) : '';

            unset($data[$key]);
            $val['children'] = $this->recursion($data, $val['id'], $val['departmentPath']);
            $tree[]          = $val;
        }

        return $tree;
    }
}
