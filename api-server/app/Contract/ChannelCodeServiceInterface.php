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

interface ChannelCodeServiceInterface
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getChannelCodeById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getChannelCodesById(array $ids, array $columns = ['*']): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createChannelCode(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createChannelCodes(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateChannelCodeById(int $id, array $data): int;

    /**
     * 修改多条
     * @param array $values 参数
     * @return int 返回值
     */
    public function updateChannelCode(array $values): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteChannelCode(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteChannelCodes(array $ids): int;

    /**
     * 查询多条 - 根据企业ID.
     * @param array $corpId 企业ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getChannelCodesByCorpId(array $corpId, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据分组名称.
     * @param string $name 分组名
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getChannelCodesByName($name, array $columns = ['*']): array;

    /**
     * 查询所有渠道码
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getChannelCodes(array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getChannelCodeList(array $where, array $columns = ['*'], array $options = []): array;
}
