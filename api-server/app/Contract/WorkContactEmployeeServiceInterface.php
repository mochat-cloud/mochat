<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Contract;

interface WorkContactEmployeeServiceInterface
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeeById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeesById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getWorkContactEmployeeList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkContactEmployee(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createWorkContactEmployees(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkContactEmployeeById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContactEmployee(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContactEmployees(array $ids): int;

    /**
     * 查询单条 - 根据员工id和客户id.
     * @param int $employeeId ID
     * @param int $contactId ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function findWorkContactEmployeeByOtherIds($employeeId, $contactId, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据员工id和客户id.
     * @param array $employeeIds 员工id
     * @param array $contactIds 客户id
     * @param array $columns 参数
     * @return array 返回值
     */
    public function getWorkContactEmployeeByOtherIds(array $employeeIds, array $contactIds, array $columns = ['*']): array;

    /**
     * 修改单条 - 根据员工id和客户id.
     * @param int $employeeId 员工id
     * @param int $contactId 客户id
     * @param array $data 参数
     * @return int 返回值
     */
    public function updateWorkContactEmployeeByOtherIds($employeeId, $contactId, array $data): int;

    /**
     * 修改多条 - 根据员工id和客户id.
     * @param array $employeeId 员工id
     * @param int $contactId 客户id
     * @param array $data 参数
     * @return int 返回值
     */
    public function updateWorkContactEmployeesByOtherIds(array $employeeId, $contactId, array $data): int;

    /**
     * 修改多条 根据客户id.
     * @param array $contactIds 客户id
     * @param array $data 参数
     * @return int 返回值
     */
    public function updateWorkContactEmployeeByContactIds(array $contactIds, array $data): int;

    /**
     * 查询多条 - 根据客户id.
     * @param int $contactId ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeesByContactId(int $contactId, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据多个客户id.
     * @param array $contactIds ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeesByContactIds(array $contactIds, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据备注（模糊搜索）.
     * @param string $remark
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeesByRemark($remark, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据来源.
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeesByAddWay(array $addWay, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据成员编号获取客户信息.
     */
    public function getWorkContactEmployeesByEmployeeIds(array $employeeIds, array $columns = ['*']): array;

    /**
     * 删除多条 - 根据客户id和员工id.
     * @param int $contactId 客户id
     * @param array $employeeIds 员工id
     * @return int 返回值
     */
    public function deleteWorkContactEmployeesByOtherIds($contactId, array $employeeIds): int;

    /**
     * 删除单条- 根据客户Id和成员id.
     * @param int $contactId 客户id
     * @param int $employeeId 成员id
     * @return int 返回值
     */
    public function deleteWorkContactEmployeesByOtherId($contactId, $employeeId): int;

    /**
     * 删除多条 - 根据客户ID.
     * @param array $contactIds 客户id
     * @return int 返回值
     */
    public function deleteWorkContactEmployeesByContactIds(array $contactIds): int;

    /**
     * 查询单条 - 根据员工id和客户id.
     * @param int $employeeId 员工id
     * @param int $contactId 客户id
     * @param array $columns 字段
     * @return array 返回值
     */
    public function getWorkContactEmployeeByOtherId($employeeId, $contactId, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据公司ID和成员ID.
     * @param int $employeeId 员工id
     * @param int $corpId 企业id
     * @param array $columns 参数
     * @return array 返回值
     */
    public function getWorkContactEmployeeByCorpIdEmployeeId(int $employeeId, int $corpId, array $columns = ['*']): array;

    /**
     * 修改多条 - 根据员工id和客户id.
     * @param int $employeeId 员工id
     * @param array $contactIds 客户id
     * @param array $data 参数
     * @return int 返回值
     */
    public function updateWorkContactEmployeesByOtherId(int $employeeId, array $contactIds, array $data): int;

    /**
     * 删除多条 - 根据客户id和员工id.
     * @param int $employeeId 客户id
     * @param array $contactIds 员工id
     * @return int 返回值
     */
    public function deleteWorkContactEmployeesByOther(int $employeeId, array $contactIds): int;

    /**
     * 多条分页 - 客户列表分页搜索.
     * @param array $where 条件
     * @param array $columns 字段
     * @param int $perPage 每页显示数
     * @param int $page 页码
     * @param array $orderBy 排序
     * @return array 返回值
     */
    public function getWorkContactEmployeeIndex(array $where, array $columns = ['*'], int $perPage, int $page, $orderBy = [['work_contact_employee.create_time', 'desc']]): array;

    /**
     * 查询多条 - 根据企业自定义的state参数.
     * @param array $state 企业自定义的state参数
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeesByStates(array $state, array $columns = ['*']): array;

    /**
     * 计算员工持有客户数.
     * @param array $employeeIds 员工id
     * @return array 返回值
     */
    public function countWorkContactEmployeesByEmployee(array $employeeIds): array;

    /**
     * 查询多条 - 根据企业自定义的state参数.
     * @param string $state 企业自定义的state参数
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactEmployeesByState(string $state, array $columns = ['*']): array;

    /**
     * 计算企业某段时间内新增客户数.
     * @param int $corpId 企业id
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return int 返回值
     */
    public function countWorkContactEmployeesByCorpIdTime(int $corpId, string $startTime, string $endTime): int;

    /**
     * 计算企业某段时间内流失客户数.
     * @param int $corpId 企业id
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return int 返回值
     */
    public function countLossWorkContactEmployeesByCorpIdTime(int $corpId, string $startTime, string $endTime): int;

    /**
     * 计算企业总客户数.
     * @param int $corpId 企业id
     * @param array $status 状态
     * @return int 返回值
     */
    public function countWorkContactEmployeesByCorpId(int $corpId, array $status): int;
}
