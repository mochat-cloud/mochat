<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomTagPull\Service;

use MoChat\Framework\Service\AbstractService;
use MoChat\Plugin\RoomTagPull\Contract\RoomTagPullContactContract;
use MoChat\Plugin\RoomTagPull\Model\RoomTagPullContact;

class RoomTagPullContactService extends AbstractService implements RoomTagPullContactContract
{
    /**
     * @var RoomTagPullContact
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRoomTagPullContactById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRoomTagPullContactsById(array $ids, array $columns = ['*']): array
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
    public function getRoomTagPullContactList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createRoomTagPullContact(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createRoomTagPullContacts(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateRoomTagPullContactById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteRoomTagPullContact(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteRoomTagPullContacts(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询.
     */
    public function getRoomTagPullContactByCorpId(int $CorpId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $CorpId)
            ->get($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }

    /**
     * 查询数量.
     */
    public function countRoomTagPullContactByRoomTagPullIdSendStatus(int $roomTagPullId, array $sendStatus): int
    {
        return $this->model::query()
            ->where('room_tag_pull_id', $roomTagPullId)
            ->whereIn('send_status', $sendStatus)
            ->count('id');
    }

    /**
     * 查询数量.
     */
    public function countRoomTagPullContactByRoomTagPullIdJoinRoom(int $roomTagPullId, int $joinRoom): int
    {
        return $this->model::query()
            ->where('room_tag_pull_id', $roomTagPullId)
            ->where('is_join_room', $joinRoom)
            ->count('id');
    }

    /**
     * 更新发送状态
     */
    public function updateRoomTagPullContactByRoomTagPullIdUseridExternalUserid(int $room_tag_pull_id, string $userid, string $external_userid, int $status): int
    {
        return $this->model::query()
            ->where('room_tag_pull_id', $room_tag_pull_id)
            ->where('wx_user_id', $userid)
            ->where('wx_external_userid', $external_userid)
            ->update(['send_status' => $status]);
    }

    /**
     * 查询一条
     * @param array|string[] $columns
     */
    public function getRoomTagPullContactByRoomTagPullIdSendStatus(int $room_tag_pull_id, int $status, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('room_tag_pull_id', $room_tag_pull_id)
            ->where('send_status', $status)
            ->first($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }

    /**
     * 获取客户数量.
     */
    public function countRoomTagPullContactByRoomTagPullIdUserid(int $room_tag_pull_id, string $userid): int
    {
        return $this->model::query()
            ->where('room_tag_pull_id', $room_tag_pull_id)
            ->where('wx_user_id', $userid)
            ->count('id');
    }

    /**
     * 查询数量.
     */
    public function countRoomTagPullContactByRoomTagPullIdUseridSendStatus(int $roomTagPullId, string $userid, array $sendStatus): int
    {
        return $this->model::query()
            ->where('room_tag_pull_id', $roomTagPullId)
            ->where('wx_user_id', $userid)
            ->whereIn('send_status', $sendStatus)
            ->count('id');
    }
}
