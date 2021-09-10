<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactBatchAdd\Contract;

interface ContactBatchAddImportContract
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactBatchAddImportById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactBatchAddImportsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getContactBatchAddImportList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createContactBatchAddImport(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createContactBatchAddImports(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateContactBatchAddImportById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteContactBatchAddImport(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteContactBatchAddImports(array $ids): int;

    /**
     * 获取最后id.
     *
     * @return int 主键ID
     */
    public function getLastId(): int;

    /**
     * 查询多条 - 选项查询.
     * @param array $where 查询条件
     * @param string[] $options 可选项 ['orderByRaw'=> 'id asc', 'skip' => 15, 'take' => 5]
     * @param array $columns 字段
     */
    public function getContactBatchAddImportOptionWhere(array $where, array $options = [], array $columns = ['*']): array;

    /**
     * 修改多条
     * @param array $values 修改数据(必须包含ID)
     * @return int 影响条数
     */
    public function updateContactBatchAddImports(array $values): int;

    /**
     * 查询多条 - 选项查询数量.
     * @param array $where 查询条件
     * @param array $groupBy group参数
     * @param array $columns 字段
     */
    public function getContactBatchAddImportOptionWhereGroup(array $where, array $groupBy, array $columns = ['*']): array;

    /**
     * 查询数量 - 根据扩展搜索条件.
     * @param array $where 搜索条件
     */
    public function getContactBatchAddImportOptionWhereCount(array $where): int;

    /**
     * 查询多条
     */
    public function getContactBatchAddImportByRecordId(int $recordId, int $status, array $columns = ['*']): array;

    /**
     * 查询单条 - 根据phone.
     * @param string $phone Phone
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactBatchAddImportByPhone(int $corpId, string $phone, array $columns = ['*']): array;

    /**
     * 查询数量 - 根据status.
     * @param array|string[] $columns 查询字段
     * @return int 数组
     */
    public function countContactBatchAddImportByStatus(int $corpId, int $status, array $columns = ['*']): int;

    /**
     * 查询多条
     * @param array|string[] $columns
     */
    public function countContactBatchAddImportByRecordId(int $recordId, array $columns = ['*']): array;

    /**
     * 查询数量.
     */
    public function countContactBatchAddImportByRecordIdEmployee(int $recordId, int $employee): int;

    /**
     * 查询多条
     * @param array|string[] $columns
     */
    public function getContactBatchAddImportByRecordIdEmployeeId(int $recordId, int $employeeId, int $status, array $columns = ['*']): array;
}
