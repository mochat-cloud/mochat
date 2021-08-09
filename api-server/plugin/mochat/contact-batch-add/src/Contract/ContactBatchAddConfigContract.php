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

interface ContactBatchAddConfigContract
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactBatchAddConfigById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactBatchAddConfigsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getContactBatchAddConfigList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createContactBatchAddConfig(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createContactBatchAddConfigs(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateContactBatchAddConfigById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteContactBatchAddConfig(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteContactBatchAddConfigs(array $ids): int;

    /**
     * 修改配置 - 根据企业（如果不存在则创建）.
     * @param int $corpId 企业ID
     * @param array $values 修改值
     */
    public function updateContactBatchAddConfigByCorpId(int $corpId, array $values = []): bool;

    /**
     * 查询单条 - 根据企业ID.
     * @param int $corpId 企业ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactBatchAddConfigByCorpId(int $corpId, array $columns = ['*']): array;

    /**
     * 查询多条 - 选项查询.
     * @param array $where 查询条件
     * @param string[] $options 可选项 ['orderByRaw'=> 'id asc', 'skip' => 15, 'take' => 5]
     * @param array $columns 字段
     */
    public function getContactBatchAddConfigOptionWhere(array $where, array $options = [], array $columns = ['*']): array;
}
