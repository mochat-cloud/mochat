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

use App\Contract\MediumGroupServiceInterface;
use App\Model\MediumGroup;
use MoChat\Framework\Service\AbstractService;

class MediumGroupService extends AbstractService implements MediumGroupServiceInterface
{
    /**
     * @var MediumGroup
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getMediumGroupById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getMediumGroupsById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getMediumGroupList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createMediumGroup(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createMediumGroups(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateMediumGroupById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteMediumGroup(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteMediumGroups(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询单条 - 根据名称.
     * @param string $name 分组名称
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getMediumGroupByName(string $name, array $columns = ['*']): array
    {
        $data          = $this->model::query()->where('name', $name)->first($columns);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 查询单条 - 唯一查询.
     * @param string $name 分组名称
     * @param int $id 排除ID
     * @return bool 是否存在
     */
    public function existMediumGroupByName(string $name, ?int $id = null): bool
    {
        $data        = $this->model::query()->where('name', $name);
        $id && $data = $data->where('id', '<>', $id);
        return $data->exists();
    }

    /**
     * 查询多条 - 根据 corpId.
     * @param int $corpId corpId
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getMediumGroupsByCorpId(int $corpId, array $columns = ['*']): array
    {
        $data          = $this->model::query()->where('corp_id', $corpId)->get($columns);
        $data || $data = collect([]);
        return $data->toArray();
    }
}
