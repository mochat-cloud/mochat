<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ShopCode\Contract;

interface ShopCodeContract
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getShopCodeById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getShopCodesById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getShopCodeList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createShopCode(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createShopCodes(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateShopCodeById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteShopCode(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteShopCodes(array $ids): int;

    /**
     * 查询.
     */
    public function getShopCodeByCorpIdType(int $corpId, int $type, string $province = '', array $columns = ['*']): array;

    /**
     * 查询多条
     * @param array|string[] $columns
     */
    public function getShopCodeByCorpIdTypeStatus(int $corpId, int $type, int $status, array $columns = ['*']): array;

    /**
     * 查询一条
     * @param array|string[] $columns
     */
    public function getShopCodeByCorpIdTypeStatusProvince(int $corpId, int $type, int $status, string $province, string $city, array $columns = ['*']): array;

    /**
     * 查询一条
     * @param array|string[] $columns
     */
    public function getShopCodeByCorpIdTypeStatusName(int $corpId, int $type, int $status, string $name, array $columns = ['*']): array;

    /**
     * 查询一条
     * @param array|string[] $columns
     */
    public function getShopCodeByCorpIdTypeStatusProvinceName(int $corpId, int $type, int $status, string $province, string $city, string $name, array $columns = ['*']): array;

    /**
     * 查询一条
     * @param array|string[] $columns
     */
    public function getShopCodeByNameAddress(int $corpId, string $address, int $id = 0, array $columns = ['*']): array;
}
