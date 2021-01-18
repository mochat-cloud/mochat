<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\Role;

use App\Contract\RbacRoleServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 角色-列表.
 *
 * Class IndexLogic
 */
class IndexLogic
{
    /**
     * @Inject
     * @var RbacRoleServiceInterface
     */
    protected $roleService;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @var int
     */
    protected $page;

    /**
     * @var int
     */
    protected $perPage = 10;

    /**
     * @var int 企业id
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
        $params = $this->handleParams($user, $params);

        ## 查询数据
        return $this->getRolesList($params);
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParams(array $user, array $params): array
    {
        $where['tenant_id'] = $user['tenantId'];
        $this->corpId       = $user['corpIds'][0];
        if (! empty($params['name'])) {
            $where['name'] = $params['name'];
        }

        $options = [
            'perPage'    => $params['perPage'],
            'page'       => $params['page'],
            'orderByRaw' => 'id desc',
        ];

        return ['where' => $where, 'options' => $options];
    }

    /**
     * 获取角色列表.
     * @param array $params 参数
     * @return array 响应数组
     */
    private function getRolesList(array $params): array
    {
        $columns  = ['id', 'name', 'remarks', 'updated_at', 'status'];
        $roleList = $this->roleService->getRbacRoleList($params['where'], $columns, $params['options']);

        $list = [];
        $data = [
            'page' => [
                'perPage'   => $this->perPage,
                'total'     => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($roleList['data']) ? $data : $this->handleData($roleList);
    }

    /**
     * 数据处理.
     * @param array $roleList 角色列表数据
     * @return array 响应数组
     */
    private function handleData(array $roleList): array
    {
        $list = [];
        foreach ($roleList['data'] as $key => $val) {
            $list[$key] = [
                'roleId'      => $val['id'],
                'name'        => $val['name'],
                'remarks'     => $val['remarks'],
                'updatedAt'   => $val['updatedAt'],
                'status'      => $val['status'],
                'employeeNum' => $this->getEmployeesCountByRoleId($val['id']),
            ];
        }
        $data['page']['total']     = $roleList['total'];
        $data['page']['totalPage'] = $roleList['last_page'];
        $data['list']              = $list;

        return $data;
    }

    /**
     * 获取总数.
     * @param int $roleId 角色id
     */
    private function getEmployeesCountByRoleId(int $roleId): int
    {
        $where = [
            'roleId' => $roleId,
            'corpId' => $this->corpId,
        ];

        $employee = $this->workEmployeeService->getWorkEmployeesCountByRoleId($where);

        return ! empty($employee) ? $employee : 0;
    }
}
