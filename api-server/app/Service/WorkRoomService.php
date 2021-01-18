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

use App\Contract\WorkRoomServiceInterface;
use App\Model\WorkRoom;
use MoChat\Framework\Service\AbstractService;

class WorkRoomService extends AbstractService implements WorkRoomServiceInterface
{
    /**
     * @var WorkRoom
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkRoomById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkRoomsById(array $ids, array $columns = ['*']): array
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
    public function getWorkRoomList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkRoom(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createWorkRooms(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkRoomById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteWorkRoom(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteWorkRooms(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 计算客户群聊总数.
     * @param array $corpIds 企业id
     * @return int 返回值
     */
    public function countWorkRoomByCorpIds(array $corpIds): int
    {
        return $this->model::query()
            ->whereIn('corp_id', $corpIds)
            ->count();
    }

    /**
     * 计算客户群聊总数.
     * @param array $corpIds 企业id
     * @param string $name 群聊名称
     * @return int 返回值
     */
    public function countWorkRoomByCorpIdsName(array $corpIds, $name): int
    {
        return $this->model::query()
            ->whereIn('corp_id', $corpIds)
            ->where('name', 'like', "%{$name}%")
            ->count();
    }

    /**
     * 查询多条 根据名称.
     * @param array $corpId 企业ID
     * @param string $name 群名称（模糊搜索）
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkRoomByCorpIdName(array $corpId, $name, array $columns = ['*']): array
    {
        $res = $this->model::query()->whereIn('corp_id', $corpId);
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
     * 修改多条 - 根据IDs.
     * @param array $ids id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkRoomsById(array $ids, array $data): int
    {
        return $this->model::query()->whereIn('id', $ids)->update($data);
    }

    /**
     * 查询多条 根据名称.
     * @param int $corpId 企业ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkRoomsByCorpId(int $corpId, array $columns = ['*']): array
    {
        return $this->model::query()->where('corp_id', $corpId)->get($columns)->toArray();
    }

    /**
     * 批量修改 - case...then...根据ID.
     * @param array $values 修改数据(必须包含ID)
     * @param bool $transToSnake 是否key转snake
     * @param bool $isColumnFilter 是否过滤不存在于表内的字段数据
     * @return int 影响条数
     */
    public function batchUpdateByIds(array $values, bool $transToSnake = false, bool $isColumnFilter = false): int
    {
        return $this->model->batchUpdateByIds($values, $transToSnake, $isColumnFilter);
    }

    /**
     * 修改多条 - 根据客户群聊分组.
     * @param int $roomGroupId 分组ID
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkRoomsByRoomGroupId(int $roomGroupId, array $data): int
    {
        return $this->model::query()->where('room_group_id', $roomGroupId)->update($data);
    }

    /**
     * 查询多条 根据微信群聊ID.
     * @param array $wxChatIds 微信群聊ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkRoomsByWxChatId(array $wxChatIds, array $columns = ['*']): array
    {
        return $this->model::query()->whereIn('wx_chat_id', $wxChatIds)->get($columns)->toArray();
    }

    /**
     * 查询多条 根据名称.
     * @param array $corpId 企业ID
     * @param array $params 名称、分组参数
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkRoomByCorpIdNameGroupId(array $corpId, array $params = [], array $columns = ['*']): array
    {
        $res = $this->model::query()->whereIn('corp_id', $corpId);
        if (! empty($params)) {
            ! isset($params['name']) || $res          = $res->where('name', 'like', "%{$params['name']}%");
            ! isset($params['room_group_id']) || $res = $res->where('room_group_id', $params['room_group_id']);
        }
        $res = $res->get($columns);
        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 删除 - 单条
     * @param string $wxChatId 客户群微信ID
     * @return int 删除条数
     */
    public function deleteWorkRoomByWxChatId(string $wxChatId): int
    {
        return $this->model::query()->where('wx_chat_id', $wxChatId)->delete();
    }

    /**
     * 计算某段时间内新增群聊数.
     * @param int $corpId 企业id
     * @param string $startTime 开始时间
     * @param string $endTime 结束时间
     * @return int 返回值
     */
    public function countAddWorkRoomsByCorpIdTime(int $corpId, string $startTime, string $endTime): int
    {
        return $this->model::query()
            ->where('corp_id', $corpId)
            ->where('created_at', '>', $startTime)
            ->where('created_at', '<', $endTime)
            ->count();
    }
}
