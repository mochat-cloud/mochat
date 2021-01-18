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

use App\Contract\ChannelCodeGroupServiceInterface;
use App\Model\ChannelCodeGroup;
use MoChat\Framework\Service\AbstractService;

class ChannelCodeGroupService extends AbstractService implements ChannelCodeGroupServiceInterface
{
    /**
     * @var ChannelCodeGroup
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getChannelCodeGroupById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getChannelCodeGroupsById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createChannelCodeGroup(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createChannelCodeGroups(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateChannelCodeGroupById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 修改多条
     * @param array $values 参数
     * @return int 返回值
     */
    public function updateChannelCodeGroup(array $values): int
    {
        return $this->model->batchUpdateByIds($values);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteChannelCodeGroup(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteChannelCodeGroups(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询多条 - 根据企业ID.
     * @param array $corpId 企业ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getChannelCodeGroupsByCorpId(array $corpId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('corp_id', $corpId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据分组名称.
     * @param string $name 分组名
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getChannelCodeGroupsByName($name, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('name', $name)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据分组名称.
     * @param array $name 分组名
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getChannelCodeGroupsByNames(array $name, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('name', $name)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }
}
