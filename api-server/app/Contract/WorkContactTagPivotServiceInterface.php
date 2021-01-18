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

interface WorkContactTagPivotServiceInterface
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getWorkContactTagPivotList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkContactTagPivot(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createWorkContactTagPivots(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkContactTagPivotById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContactTagPivot(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContactTagPivots(array $ids): int;

    /**
     * 查询标签下的客户数.
     * @param array $tagIds 标签ID
     */
    public function countWorkContactTagPivotsByTagIds(array $tagIds);

    /**
     * 查询多条 - 根据多个客户ID.
     * @param array $contactIds 客户ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotsByContactIds(array $contactIds, array $columns = ['*']): array;

    /**
     * 查询多条（包含软删数据） - 根据多个客户ID.
     * @param array $contactIds 客户ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotsSoftByContactIds(array $contactIds, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据多个客户ID和标签ID.
     * @param array $contactIds 客户ID
     * @param array $tagIds 标签ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotsByContactIdsTagIds(array $contactIds, array $tagIds, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据客户id.
     * @param int $contactId ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotsByContactId($contactId, array $columns = ['*']): array;

    /**
     * 删除 - 多条 根据标签id.
     * @param array $tagId 标签id
     * @return int 删除条数
     */
    public function deleteWorkContactTagPivotsByTagId(array $tagId): int;

    /**
     * 查询多条 - 根据客户id和员工id.
     * @param int $contactId ID
     * @param int $employeeId ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotsByOtherId($contactId, $employeeId, array $columns = ['*']): array;

    /**
     * 查询多条（包含软删数据） - 根据客户id和员工id.
     * @param int $contactId ID
     * @param int $employeeId ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotsSoftByOtherId($contactId, $employeeId, array $columns = ['*']): array;

    /**
     * 删除多条 根据客户id和员工id和标签id.
     * @param int $contactId 客户id
     * @param int $employeeId 员工id
     * @param array $tagIds 标签id
     * @return int 返回多条
     */
    public function deleteWorkContactTagPivotsByOtherId($contactId, $employeeId, array $tagIds): int;

    /**
     * 删除多条 根据客户id.
     * @param array $contactIds 客户id
     * @return int 返回值
     */
    public function deleteWorkContactTagPivotsByContactIds(array $contactIds): int;

    /**
     * 查询多条 - 根据客户ID和员工ID.
     * @param array $contactIds 客户ID
     * @param int $employeeId 员工ID
     * @param array $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotsByContactIdEmployeeId(array $contactIds, int $employeeId, array $columns = ['*']): array;

    /**
     * 删除多条 根据客户id和员工id.
     * @param array $contactId 客户id
     * @param int $employeeId 员工id
     * @return int 返回多条
     */
    public function deleteWorkContactTagPivotsByOther(array $contactId, int $employeeId): int;
}
