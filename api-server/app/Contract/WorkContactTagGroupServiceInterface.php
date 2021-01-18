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

interface WorkContactTagGroupServiceInterface
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagGroupById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagGroupsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getWorkContactTagGroupList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkContactTagGroup(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createWorkContactTagGroups(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkContactTagGroupById(int $id, array $data): int;

    /**
     * 修改多条
     * @param array $values 参数
     * @return int 返回值
     */
    public function updateWorkContactTagGroup(array $values): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContactTagGroup(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContactTagGroups(array $ids): int;

    /**
     * 查询多条 - 根据企业ID.
     * @param array $corpId 企业ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagGroupsByCorpId(array $corpId, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据分组名称.
     * @param $groupName
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagGroupsByName($groupName, array $columns = ['*']): array;

    /**
     * 查询单条 - 根据企业id和分组名称.
     * @param int $corpId 企业id
     * @param string $groupName 分组名称
     * @param array $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagGroupByCorpIdName($corpId, $groupName, array $columns = ['*']): array;

    /**
     * 查询单条 - 根据微信分组id.
     * @param string $wxGroupId 微信分组id
     * @param array $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagGroupByWxGroupId(string $wxGroupId, array $columns = ['*']): array;
}
