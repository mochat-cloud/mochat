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

use App\Constants\WorkContactEmployee\Status;
use App\Contract\WorkContactEmployeeServiceInterface;
use App\Model\WorkContactEmployee;
use Hyperf\DbConnection\Db;
use MoChat\Framework\Service\AbstractService;

class WorkContactEmployeeService extends AbstractService implements WorkContactEmployeeServiceInterface
{
    /**
     * @var WorkContactEmployee
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeeById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeesById(array $ids, array $columns = ['*']): array
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
    public function getWorkContactEmployeeList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkContactEmployee(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createWorkContactEmployees(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkContactEmployeeById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContactEmployee(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContactEmployees(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询单条 - 根据员工id和客户id.
     * @param int $employeeId ID
     * @param int $contactId ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function findWorkContactEmployeeByOtherIds($employeeId, $contactId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('employee_id', $employeeId)
            ->where('contact_id', $contactId)
            ->first($columns);
        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据员工id和客户id.
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeeByOtherIds(array $employeeIds, array $contactIds, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereIn('contact_id', $contactIds)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 修改单条 - 根据员工id和客户id.
     * @param int $employeeId 员工id
     * @param int $contactId 客户id
     * @param array $data 参数
     * @return int 返回值
     */
    public function updateWorkContactEmployeeByOtherIds($employeeId, $contactId, array $data): int
    {
        return $this->model::query()
            ->where('employee_id', $employeeId)
            ->where('contact_id', $contactId)
            ->update($data);
    }

    /**
     * 修改多条 - 根据员工id和客户id.
     * @param array $employeeId 员工id
     * @param int $contactId 客户id
     * @param array $data 参数
     * @return int 返回值
     */
    public function updateWorkContactEmployeesByOtherIds(array $employeeId, $contactId, array $data): int
    {
        return $this->model::query()
            ->whereIn('employee_id', $employeeId)
            ->where('contact_id', $contactId)
            ->update($data);
    }

    /**
     * 修改多条 根据客户id.
     * @param array $contactIds 客户id
     * @param array $data 参数
     * @return int 返回值
     */
    public function updateWorkContactEmployeeByContactIds(array $contactIds, array $data): int
    {
        return $this->model::query()
            ->whereIn('contact_id', $contactIds)
            ->update($data);
    }

    /**
     * 查询多条 - 根据客户id.
     * @param int $contactId ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeesByContactId(int $contactId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('contact_id', $contactId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据多个客户id.
     * @param array $contactIds ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeesByContactIds(array $contactIds, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('contact_id', $contactIds)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据备注（模糊搜索）.
     * @param string $remark
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeesByRemark($remark, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('remark', 'like', "%{$remark}%")
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据来源.
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeesByAddWay(array $addWay, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('add_way', $addWay)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据员工id.
     * @param array $employeeIds ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeesByEmployeeIds(array $employeeIds, array $columns = ['*']): array
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
     * 删除 - 多条 根据客户id和员工id.
     * @param int $contactId 客户id
     * @param array $employeeIds 员工id
     * @return int 返回值
     */
    public function deleteWorkContactEmployeesByOtherIds($contactId, array $employeeIds): int
    {
        return $this->model::query()
            ->where('contact_id', $contactId)
            ->whereIn('employee_id', $employeeIds)
            ->delete();
    }

    /**
     * 删除多条 - 根据客户ID.
     * @param array $contactIds 客户id
     * @return int 返回值
     */
    public function deleteWorkContactEmployeesByContactIds(array $contactIds): int
    {
        return $this->model::query()
            ->whereIn('contact_id', $contactIds)
            ->delete();
    }

    /**
     * 删除单条- 根据客户Id和成员id.
     * @param int $contactId 客户id
     * @param int $employeeId 成员id
     * @return int 返回值
     */
    public function deleteWorkContactEmployeesByOtherId($contactId, $employeeId): int
    {
        return $this->model::query()
            ->where('contact_id', $contactId)
            ->where('employee_id', $employeeId)
            ->delete();
    }

    /**
     * 查询单条 - 根据员工id和客户id.
     * @param int $employeeId 员工id
     * @param int $contactId 客户id
     * @param array $columns 字段
     * @return array 返回值
     */
    public function getWorkContactEmployeeByOtherId($employeeId, $contactId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('employee_id', $employeeId)
            ->where('contact_id', $contactId)
            ->first($columns);
        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据公司ID和成员ID.
     * @param int $employeeId 员工id
     * @param int $corpId 企业id
     * @param array $columns 参数
     * @return array 返回值
     */
    public function getWorkContactEmployeeByCorpIdEmployeeId(int $employeeId, int $corpId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('employee_id', $employeeId)
            ->where('corp_id', $corpId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 修改多条 - 根据员工id和客户id.
     * @param int $employeeId 员工id
     * @param array $contactIds 客户id
     * @param array $data 参数
     * @return int 返回值
     */
    public function updateWorkContactEmployeesByOtherId(int $employeeId, array $contactIds, array $data): int
    {
        return $this->model::query()
            ->where('employee_id', $employeeId)
            ->whereIn('contact_id', $contactIds)
            ->update($data);
    }

    /**
     * 删除多条 - 根据员工id和多个客户id.
     * @param int $employeeId 客户id
     * @param array $contactIds 员工id
     * @return int 返回值
     */
    public function deleteWorkContactEmployeesByOther(int $employeeId, array $contactIds): int
    {
        return $this->model::query()
            ->where('employee_id', $employeeId)
            ->whereIn('contact_id', $contactIds)
            ->delete();
    }

    /**
     * 多条分页 - 客户列表分页搜索.
     * @param array $where 条件
     * @param array $columns 字段
     * @param int $perPage 每页显示数
     * @param int $page 页码
     * @param array $orderBy 排序
     * @return array 返回值
     */
    public function getWorkContactEmployeeIndex(array $where, array $columns = ['*'], int $perPage, int $page, $orderBy = [['work_contact_employee.create_time', 'desc']]): array
    {
        $model = $this->model::query();

        //标明是流失客户 需查询软删的数据
        if (! empty($where['isFlag'])) {
            $model = $model->withTrashed();
        }
        if (! empty($where['corpId'])) {
            $model = $model->whereIn('corp_id', $where['corpId']);
        }
        if (! empty($where['remark'])) {
            $model = $model->where('remark', 'like', "%{$where['remark']}%");
        }
        if (! empty($where['status'])) {
            if (is_array($where['status'])) {
                $model = $model->whereIn('status', $where['status']);
            } else {
                $model = $model->where('status', $where['status']);
            }
        }
        if (isset($where['addWay']) && is_numeric($where['addWay'])) {
            $model = $model->where('add_way', $where['addWay']);
        }
        if (! empty($where['contactId'])) {
            $model = $model->whereIn('contact_id', $where['contactId']);
        }
        if (! empty($where['noContactId'])) {
            $model = $model->whereNotIn('contact_id', $where['noContactId']);
        }
        if (! empty($where['employeeId'])) {
            if (is_array($where['employeeId'])) {
                $model = $model->whereIn('employee_id', $where['employeeId']);
            } else {
                $model = $model->where('employee_id', $where['employeeId']);
            }
        }
        if (! empty($where['startTime'])) {
            $model = $model->where('create_time', '>=', $where['startTime']);
        }
        if (! empty($where['endTime'])) {
            $model = $model->where('create_time', '<=', $where['endTime']);
        }

        if (! empty($orderBy) && is_array($orderBy)) {
            foreach ($orderBy as $v) {
                $model->orderBy($v[0], $v[1]);
            }
        }

        $res         = $model->paginate($perPage, $columns, 'page', $page);
        $res || $res = collect([]);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据企业自定义的state参数.
     * @param array $state 企业自定义的state参数
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeesByStates(array $state, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('state', $state)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 计算员工持有客户数.
     * @param array $employeeIds 员工id
     * @return array 返回值
     */
    public function countWorkContactEmployeesByEmployee(array $employeeIds): array
    {
        $res = Db::table('work_contact_employee')
            ->where('deleted_at', null)
            ->whereIn('employee_id', $employeeIds)
            ->selectRaw('employee_id')
            ->selectRaw('count(contact_id) as total')
            ->groupBy('employee_id')
            ->get();

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据企业自定义的state参数.
     * @param string $state 企业自定义的state参数
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeesByState(string $state, array $columns = ['*']): array
    {
        $data = $this->model::query()
            ->withTrashed()
            ->where('state', $state)
            ->get($columns);

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 计算企业某段时间内新增客户数.
     * @param int $corpId 企业id
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return int 返回值
     */
    public function countWorkContactEmployeesByCorpIdTime(int $corpId, string $startTime, string $endTime): int
    {
        return $this->model::query()
            ->withTrashed()
            ->where('corp_id', $corpId)
            ->where('create_time', '>', $startTime)
            ->where('create_time', '<', $endTime)
            ->count();
    }

    /**
     * 计算企业某段时间内流失客户数.
     * @param int $corpId 企业id
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return int 返回值
     */
    public function countLossWorkContactEmployeesByCorpIdTime(int $corpId, string $startTime, string $endTime): int
    {
        return $this->model::query()
            ->withTrashed()
            ->where('corp_id', $corpId)
            ->where('deleted_at', '>', $startTime)
            ->where('deleted_at', '<', $endTime)
            ->whereIn('status', [Status::REMOVE, Status::PASSIVE_REMOVE])
            ->count();
    }

    /**
     * 计算企业总客户数.
     * @param int $corpId 企业id
     * @param array $status 状态
     * @return int 返回值
     */
    public function countWorkContactEmployeesByCorpId(int $corpId, array $status): int
    {
        return $this->model::query()
            ->where('corp_id', $corpId)
            ->whereIn('status', $status)
            ->count();
    }

    /**
     * 修改多条 - 根据员工id.
     * @param int $employeeId 员工id
     * @param array $data 参数
     * @return int 返回值
     */
    public function updateWorkContactEmployeesByEmployeeId(int $employeeId, array $data): int
    {
        return $this->model::query()
            ->where('employee_id', $employeeId)
            ->update($data);
    }

    /**
     * 修改多条 - 根据员工id和客户id.
     * @param int $employeeId 员工id
     * @param array $contactId 客户id
     * @param array $columns 查询字段
     * @return array 返回值
     */
    public function getWorkContactEmployeesByOtherIds(int $employeeId, array $contactId, array $columns = ['*']): array
    {
        $data = $this->model::query()
            ->where('employee_id', $employeeId)
            ->whereIn('contact_id', $contactId)
            ->get($columns);

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 批量更新.
     * @param array $data 多条数据
     * @return int 响应数据
     */
    public function updateWorkContactEmployeesCaseIds(array $data): int
    {
        return $this->model->batchUpdateByIds($data);
    }
}
