<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Service;

use App\Contract\WorkEmployeeDepartmentServiceInterface;
use App\Model\WorkEmployeeDepartment;
use MoChat\Framework\Service\AbstractService;

class WorkEmployeeDepartmentService extends AbstractService implements WorkEmployeeDepartmentServiceInterface
{
    /**
     * @var WorkEmployeeDepartment
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeeDepartmentById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeeDepartmentsById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getWorkEmployeeDepartmentList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkEmployeeDepartment(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createWorkEmployeeDepartments(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkEmployeeDepartmentById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteWorkEmployeeDepartment(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteWorkEmployeeDepartments(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询多条 - 根据部门ID.
     * @param array $departmentId 部门ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeeDepartmentsByDepartmentIds(array $departmentId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('department_id', $departmentId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据成员id.
     * @param int $employeeId 成员id
     * @param array $columns 参数
     * @return array 返回值
     */
    public function getWorkEmployeeDepartmentsByEmployeeId(int $employeeId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('employee_id', $employeeId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 修改多条 - 根据Ids批量修改.
     * @param array $values 修改数据(必须包含ID)
     * @param bool $transToSnake 是否key转snake
     * @param bool $isColumnFilter 是否过滤不存在于表内的字段数据
     * @return int 影响条数
     */
    public function batchUpdateByIds(array $values, bool $transToSnake = false, bool $isColumnFilter = false): int
    {
        return $this->model->batchUpdateByIds($values, $transToSnake, $isColumnFilter);
    }

    /**
     * 查询多条 - 根据成员ID.
     * @param array $employeeIds 成员id
     * @param array $columns 参数
     * @return array 返回值
     */
    public function getWorkEmployeeDepartmentsByEmployeeIds(array $employeeIds, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('employee_id', $employeeIds)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据部门id和成员ID.
     * @param array $departmentIds 部门id
     * @param array $employeeIds 成员id
     * @param array $columns 参数
     * @return array 返回值
     */
    public function getWorkEmployeeDepartmentsByOtherId(array $departmentIds, array $employeeIds, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereIn('department_id', $departmentIds)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }
}
