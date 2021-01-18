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

interface WorkEmployeeServiceInterface
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeeById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getWorkEmployeeList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkEmployee(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createWorkEmployees(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkEmployeeById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteWorkEmployee(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteWorkEmployees(array $ids): int;

    /**
     * 查询多条 - 根据企业ID.
     * @param int $corpId 企业ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesByCorpId($corpId, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据企业ID和状态.
     * @param int $corpId 企业ID
     * @param int $status 状态
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesByCorpIdStatus($corpId, int $status, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据企业ID和成员名称.
     * @param int $corpId 企业ID
     * @param string $name 成员名称（模糊搜索）
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesByCorpIdName(int $corpId, $name, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据企业ID、成员名称和状态.
     * @param int $corpId 企业ID
     * @param string $name 成员名称（模糊搜索）
     * @param int $status 状态
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesByCorpIdNameStatus(int $corpId, string $name, int $status, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据成员id和状态.
     * @param array $id 企业ID
     * @param int $status 状态
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesByIdStatus(array $id, int $status, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据企业ID.
     * @param array $corpIds 企业ID数组
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesByCorpIds(array $corpIds, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据用户ID.
     * @param int $logUserId 用户表ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeeByLogUserId(int $logUserId, array $columns = ['*']): array;

    /**
     * 查询单条 - 根据企业ID、用户表ID.
     * @param int $corpId 企业ID
     * @param int $logUserId 用户表ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeeByCorpIdLogUserId(int $corpId, int $logUserId, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据公司ID.
     * @param string $corpId 企业id
     * @param array $columns 字段
     * @return array 返回值
     */
    public function getWorkEmployeeDepartmentsByCorpId(string $corpId, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据公司ID和成员微信ID.
     */
    public function getWorkEmployeesByCorpIdsWxUserId(array $corpIds, array $wxUserIds, array $columns = ['*']): array;

    /**
     * 模糊查询员工 - 根据名称与别名.
     * @param string $name ..
     * @param int $corpId ...
     * @param array|string[] $columns ..
     * @param int $limit ..
     * @return array ..
     */
    public function getWorkEmployeesByNameAlias(string $name, int $corpId, array $columns = ['*'], int $limit = 20): array;

    /**
     * 查询wx_user_id不为空的成员信息 - 根据企业id.
     * @param array $corpIds 企业id
     * @param array $columns 参数
     * @return array 返回值
     */
    public function getWorkEmployeesByCorpIdWxUserIdNotNull(array $corpIds, array $columns = ['*']): array;

    /**
     * 查询单条 - 根据微信成员UserId.
     */
    public function getWorkEmployeeByWxUserId(string $wxUserId, array $columns = ['*']): array;

    /**
     * 修改多条 - 根据Ids批量修改.
     */
    public function batchUpdateByIds(array $values, bool $transToSnake = false, bool $isColumnFilter = false): int;

    /**
     * 查询单条 - 根据公司ID和成员微信ID.
     */
    public function getWorkEmployeeByCorpIdWxUserId(string $corpId, string $wxUserId, array $columns = ['*']): array;

    /**
     * 查询多条 - 用户表ID.
     * @param int $logUserId 用户表ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesByLogUserId(int $logUserId, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID和名称.
     * @param array $ids ...
     * @param string $name ...
     * @param array|string[] $columns ...
     * @return array ...
     */
    public function getWorkEmployeesByIdName(array $ids, string $name = '', array $columns = ['*']): array;

    /**
     * 查询多条 - 根据手机号.
     * @param string $phone ...
     * @param array|string[] $columns ...
     * @return array ...
     */
    public function getWorkEmployeesByMobile(string $phone, array $columns = ['*']): array;

    /**
     * 批量修改 - 根据IDs.
     * @param array $data 数据
     * @return int 修改条数
     */
    public function updateWorkEmployeesCaseIds(array $data): int;

    /**
     * 查询多条 - 用户表ID.
     * @param array $logUserIds 用户表ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkEmployeesByLogUserIds(array $logUserIds, array $columns = ['*']): array;

    /**
     * @param array $where 角色ID
     * @param array $options 分页操作
     * @param array $columns
     * @return array 数组
     */
    public function getWorkEmployeesByRoleId(array $where, array $options, $columns = ['*']): array;

    /**
     * 根据角色id和企业id获取成员.
     * @param array $where 角色ID ['roleId' => 1, 'corpId' => 2]
     */
    public function getWorkEmployeesCountByRoleId(array $where): int;

    /**
     * 计算企业已激活的总成员人数.
     * @param int $corpId 企业id
     * @return int 返回值
     */
    public function countWorkEmployeesByCorpId(int $corpId): int;
}
