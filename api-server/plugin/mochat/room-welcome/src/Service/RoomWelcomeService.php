<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomWelcome\Service;

use MoChat\Framework\Service\AbstractService;
use MoChat\Plugin\RoomWelcome\Contract\RoomWelcomeContract;
use MoChat\Plugin\RoomWelcome\Model\RoomWelcome;

class RoomWelcomeService extends AbstractService implements RoomWelcomeContract
{
    /**
     * @var RoomWelcome
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRoomWelcomeById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRoomWelcomesById(array $ids, array $columns = ['*']): array
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
    public function getRoomWelcomeList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createRoomWelcome(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createRoomWelcomes(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateRoomWelcomeById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteRoomWelcome(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteRoomWelcomes(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param string $name 公司名称
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRoomWelcomesByIdName(array $ids, string $name, array $columns = ['*']): array
    {
        $res = $this->model::query()->whereIn('id', $ids);
        if (! empty($name)) {
            $res = $res->where('name', 'like', "%{$name}%");
        }

        $res = $res->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询单条 - 根据ID.
     * @param string $wxRoomWelcomeId 企业微信ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRoomWelcomesByWxRoomWelcomeId(string $wxRoomWelcomeId, array $columns = ['*']): array
    {
        return $this->model::query()->where('wx_RoomWelcomeid', $wxRoomWelcomeId)->first()->toArray();
    }

    /**
     * 查询多条
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRoomWelcomes(array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function getRoomWelcomesByTenantId(int $tenantId, array $columns = ['*']): array
    {
        $res = $this->model::query()->where('tenant_id', $tenantId)->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }
}
