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

use App\Constants\WorkEmployee\Status;
use App\Contract\WorkEmployeeServiceInterface;
use App\Model\RbacUserRole;
use App\Model\WorkEmployee;
use MoChat\Framework\Service\AbstractService;

class WorkEmployeeService extends AbstractService implements WorkEmployeeServiceInterface
{
    /**
     * @var WorkEmployee
     */
    protected $model;

    /**
     * @var RbacUserRole
     */
    protected $userRoleModel;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeeById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesById(array $ids, array $columns = ['*']): array
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
    public function getWorkEmployeeList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkEmployee(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createWorkEmployees(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkEmployeeById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteWorkEmployee(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteWorkEmployees(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询多条 - 根据企业ID.
     * @param int $corpId 企业ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesByCorpId($corpId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $corpId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 查询多条 - 根据企业ID和状态.
     * @param int $corpId 企业ID
     * @param int $status 状态
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesByCorpIdStatus($corpId, int $status, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $corpId)
            ->where('status', $status)
            ->get($columns);

        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 查询多条 - 根据企业ID和成员名称.
     * @param int $corpId 企业ID
     * @param string $name 成员名称（模糊搜索）
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesByCorpIdName(int $corpId, $name, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $corpId)
            ->where('name', 'like', "%{$name}%")
            ->get($columns);

        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 查询多条 - 根据企业ID、成员名称和状态.
     * @param int $corpId 企业ID
     * @param string $name 成员名称（模糊搜索）
     * @param int $status 状态
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesByCorpIdNameStatus(int $corpId, string $name, int $status, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $corpId)
            ->where('name', 'like', "%{$name}%")
            ->where('status', $status)
            ->get($columns);

        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 查询多条 - 根据企业ID和成员名称.
     * @param array $corpIds 企业ID数组
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesByCorpIds(array $corpIds, array $columns = ['*']): array
    {
        $res = $this->model::query()->whereIn('corp_id', $corpIds);

        $res = $res->get($columns);
        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 查询多条 - 根据成员id和状态.
     * @param array $id 企业ID
     * @param int $status 状态
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesByIdStatus(array $id, int $status, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('id', $id)
            ->where('status', $status)
            ->get($columns);

        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 查询多条 - 根据用户ID.
     * @param int $logUserId 用户表ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeeByLogUserId(int $logUserId, array $columns = ['*']): array
    {
        $res = $this->model::query()->where('log_user_id', $logUserId);

        $res = $res->get($columns);
        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 查询单条 - 根据企业ID、用户表ID.
     * @param int $corpId 企业ID
     * @param int $logUserId 用户表ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeeByCorpIdLogUserId(int $corpId, int $logUserId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $corpId)
            ->where('log_user_id', $logUserId)
            ->first();

        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 查询多条 - 根据公司ID.
     * @param string $corpId 企业id
     * @param array $columns 字段
     * @return array 返回值
     */
    public function getWorkEmployeeDepartmentsByCorpId(string $corpId, array $columns = ['*']): array
    {
        return $this->model::query()
            ->leftJoin('work_employee_department', 'work_employee.id', '=', 'work_employee_department.employee_id')
            ->leftJoin('work_department', 'work_department.id', '=', 'work_employee_department.department_id')
            ->where('work_employee.corp_id', $corpId)
            ->get($columns)
            ->toArray();
    }

    /**
     * 查询多条 - 根据公司ID和成员微信ID.
     * @param array $corpIds 企业id
     * @param array $wxUserIds 成员微信id
     * @param array $columns 参数
     * @return array 返回值
     */
    public function getWorkEmployeesByCorpIdsWxUserId(array $corpIds, array $wxUserIds, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('corp_id', $corpIds)
            ->whereIn('wx_user_id', $wxUserIds)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkEmployeesByNameAlias(string $name, int $corpId, array $columns = ['*'], int $limit = 20): array
    {
        $nameStr = '%' . $name . '%';
        $data    = $this->model::query()
            ->where('corp_id', $corpId)
            ->where(function ($query) use ($nameStr) {
                $query->where('name', 'LIKE', $nameStr)
                    ->orWhere('nick_name', 'LIKE', $nameStr);
            })
            ->limit($limit)
            ->get($columns);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 查询wx_user_id不为空的成员信息 - 根据企业id.
     * @param array $corpIds 企业id
     * @param array $columns 参数
     * @return array 返回值
     */
    public function getWorkEmployeesByCorpIdWxUserIdNotNull(array $corpIds, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('corp_id', $corpIds)
            ->where('wx_user_id', '!=', '')
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询单条 - 根据微信成员UserId.
     */
    public function getWorkEmployeeByWxUserId(string $wxUserId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('wx_user_id', $wxUserId)
            ->first($columns);

        $res || $res = collect([]);
        return $res->toArray();
    }

    /**
     * 修改多条 - 根据Ids批量修改.
     */
    public function batchUpdateByIds(array $values, bool $transToSnake = false, bool $isColumnFilter = false): int
    {
        return $this->model->batchUpdateByIds($values, $transToSnake, $isColumnFilter);
    }

    /**
     * 查询单条 - 根据公司ID和成员微信ID.
     */
    public function getWorkEmployeeByCorpIdWxUserId(string $corpId, string $wxUserId, array $columns = ['*']): array
    {
        return $this->model::query()
            ->where('corp_id', $corpId)
            ->where('wx_user_id', $wxUserId)
            ->get($columns)
            ->toArray();
    }

    /**
     * 查询多条 - 用户表ID.
     * @param int $logUserId 用户表ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesByLogUserId(int $logUserId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('log_user_id', $logUserId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkEmployeesByIdName(array $ids, string $name = '', array $columns = ['*']): array
    {
        $data = $this->model::query()->whereIn('id', $ids);
        $name && $data->where('name', 'LIKE', '%' . $name . '%');
        $data = $data->get($columns);

        if (empty($data)) {
            return [];
        }
        return $data->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkEmployeesByMobile(string $phone, array $columns = ['*']): array
    {
        $data = $this->model::query()->where('mobile', $phone)->get($columns);

        if (empty($data)) {
            return [];
        }
        return $data->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function updateWorkEmployeesCaseIds(array $data): int
    {
        return $this->model->batchUpdateByIds($data);
    }

    /**
     * 查询多条 - 用户表ID.
     * @param array $logUserIds 用户表ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesByLogUserIds(array $logUserIds, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('log_user_id', $logUserIds)
            ->get($columns);

        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * @param array $where 角色ID
     * @param array $options 分页操作
     * @param array $columns
     */
    public function getWorkEmployeesByRoleId(array $where, array $options, $columns = ['*']): array
    {
        ## 根据角色id获取用户
        $userRoleRes = RbacUserRole::query()
            ->where('role_id', $where['roleId'])
            ->get(['user_id']);
        $userRole = $userRoleRes->toArray();
        if (empty($userRole)) {
            return [];
        }

        $userIdArr = array_column($userRole, 'userId');

        ## 根据用户获取成员
        $employeeWhere            = [];
        $employeeWhere['corp_id'] = $where['corpId'];
        $employeeWhere[]          = ['log_user_id', 'IN', $userIdArr];

        $options = [
            'page'       => $options['page'],
            'perPage'    => $options['perPage'],
            'orderByRaw' => 'id desc',
        ];

        return $this->getWorkEmployeeList($employeeWhere, $columns, $options);
    }

    /**
     * 根据角色id和企业id获取成员.
     * @param array $where 角色ID ['roleId' => 1, 'corpId' => 2]
     */
    public function getWorkEmployeesCountByRoleId(array $where): int
    {
        ## 根据角色id获取用户
        $userRoleRes = RbacUserRole::query()
            ->where('role_id', $where['roleId'])
            ->get(['user_id']);
        $userRole = $userRoleRes->toArray();
        if (empty($userRole)) {
            return 0;
        }

        $userIdArr = array_column($userRole, 'userId');

        ## 根据用户获取成员
        return $this->model::query()
            ->where('corp_id', $where['corpId'])
            ->whereIn('log_user_id', $userIdArr)
            ->count('id');
    }

    /**
     * 计算企业已激活的总成员人数.
     * @param int $corpId 企业id
     * @return int 返回值
     */
    public function countWorkEmployeesByCorpId(int $corpId): int
    {
        return $this->model::query()
            ->where('corp_id', $corpId)
            ->where('status', Status::ACTIVE)
            ->count();
    }
}
