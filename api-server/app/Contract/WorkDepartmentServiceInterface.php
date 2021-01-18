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

interface WorkDepartmentServiceInterface
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkDepartmentById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkDepartmentsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getWorkDepartmentList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkDepartment(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createWorkDepartments(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkDepartmentById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteWorkDepartment(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteWorkDepartments(array $ids): int;

    /**
     * 查询多条 - 根据企业ID.
     * @param int $corpId 企业ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkDepartmentsByCorpId(int $corpId, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据企业ID和部门名称 根据部门排序 倒序.
     * @param array $corpId 企业ID
     * @param string $name 部门名称（模糊搜索）
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkDepartmentsByCorpIdName(array $corpId, $name, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据多个企业ID.
     * @param array $corpIds 企业id
     * @param array $columns 字段
     * @return array 返回值
     */
    public function getWorkDepartmentsByCorpIds(array $corpIds, array $columns = ['*']): array;

    /**
     * 批量修改 - 根据IDs.
     * @param array $data 数据
     * @return int 修改条数
     */
    public function updateWorkDepartmentByIds(array $data): int;

    /**
     * 根据条件查询多条.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkDepartmentsBySearch(array $where, array $columns = ['*']): array;

    /**
     * 企业id和父级id 查询多条.
     * @param int $corpId 企业id
     * @param array $path path
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkDepartmentsByCorpIdPath(int $corpId, array $path, array $columns = ['*']): array;

    /**
     * 查询多条 根据父部门id.
     * @param int $parentId 父部门id
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkDepartmentsByParentId(int $parentId, array $columns = ['*']): array;

    /**
     * 查询子部门(包含父部门).
     * @param array $deptPaths 父部门path
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkDepartmentsByParentPath(array $deptPaths, array $columns = ['*']): array;
}
