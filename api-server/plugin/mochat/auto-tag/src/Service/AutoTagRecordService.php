<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\AutoTag\Service;

use Hyperf\Database\Model\Builder;
use MoChat\App\WorkContact\Model\WorkContact;
use MoChat\App\WorkContact\Model\WorkContactRoom;
use MoChat\App\WorkEmployee\Model\WorkEmployee;
use MoChat\App\WorkRoom\Model\WorkRoom;
use MoChat\Framework\Service\AbstractService;
use MoChat\Plugin\AutoTag\Contract\AutoTagRecordContract;
use MoChat\Plugin\AutoTag\Model\AutoTagRecord;

class AutoTagRecordService extends AbstractService implements AutoTagRecordContract
{
    /**
     * @var AutoTagRecord
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getAutoTagRecordById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getAutoTagRecordsById(array $ids, array $columns = ['*']): array
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
    public function getAutoTagRecordList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createAutoTagRecord(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createAutoTagRecords(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateAutoTagRecordById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteAutoTagRecord(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteAutoTagRecords(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询.
     */
    public function getAutoTagRecordByCorpId(int $CorpId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $CorpId)
            ->get($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }

    /**
     * 查询单条
     * @param array|string[] $columns
     */
    public function getAutoTagRecordByCorpIdWxExternalUseridAutoTagId(int $corpId, string $wxExternalUserid, int $autoTagId, int $tagRuleId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $corpId)
            ->where('wx_external_userid', $wxExternalUserid)
            ->where('auto_tag_id', $autoTagId)
            ->where('tag_rule_id', $tagRuleId)
            ->first($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getAutoTagRecordByKeyWordSearch(array $params): array
    {
        $auto_tag_id = $params['auto_tag_id'];
        $contactName = $params['contact_name'];
        $wxUserId = $params['employee'] ?? '';
        $starTime = $params['start_time'];
        $endTime = $params['end_time'];
        $page = (int) $params['page'];
        $perPage = (int) $params['perPage'];
        $data = $this->model::from($this->model::query()->getModel()->getTable() . ' as auto_tag_record')
            ->join(WorkContact::query()->getModel()->getTable() . ' as c', 'auto_tag_record.contact_id', 'c.id')
            ->join(WorkEmployee::query()->getModel()->getTable() . ' as e', 'auto_tag_record.employee_id', 'e.id')
            ->where('auto_tag_record.auto_tag_id', '=', $auto_tag_id)
            ->when(! empty($contactName), function (Builder $query) use ($contactName) {
                return $query->where('c.name', 'like', "%{$contactName}%")
                    ->orWhere('c.nick_name', 'like', "%{$contactName}%");
            })
            ->when(! empty($wxUserId), function (Builder $query) use ($wxUserId) {
                return $query->where('e.wx_user_id', '=', $wxUserId);
            })
            ->when(! empty($starTime), function (Builder $query) use ($starTime, $endTime) {
                return $query->whereBetween('c.created_at', [$starTime, $endTime]);
            })
            ->paginate($perPage, [
                'auto_tag_record.id',
                'auto_tag_record.keyword',
                'auto_tag_record.created_at as createTagTime',
                'c.id as contactId',
                'c.name as contactName',
                'c.nick_name as contactNickName',
                'c.avatar as contactAvatar',
                'c.created_at as createTime',
                'e.id as employeeId',
                'e.name as employeeName',
            ], 'page', $page);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 查询客户id为空记录.
     * @param array|string[] $columns
     */
    public function getAutoTagRecord(array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('contact_id', '=', 0)
            ->get($columns);

        $res || $res = collect([]);
        return $res->toArray();
    }

    /**
     * 查询打标签客户总数-corpId-autoTagId.
     */
    public function countAutoTagRecordByCorpIdAutoTagId(int $corpId, int $autoTagId): int
    {
        return $this->model::query()
            ->where('corp_id', $corpId)
            ->where('auto_tag_id', $autoTagId)
            ->count('id');
    }

    /**
     * 查询今日打标签客户总数-corpId-autoTagId.
     */
    public function countAutoTagRecordTodayByCorpIdAutoTagId(int $corpId, int $autoTagId): int
    {
        return $this->model::query()
            ->where('corp_id', $corpId)
            ->where('auto_tag_id', $autoTagId)
            ->where('created_at', '>', date('Y-m-d'))
            ->count('id');
    }

    /**
     * 检索 - 多条
     * @return mixed
     */
    public function getAutoTagRecordByRoomSearch(array $params): array
    {
        $auto_tag_id = $params['auto_tag_id'];
        $contactName = $params['contact_name'];
        $wxUserId = $params['employee'] ?? '';
        $roomName = $params['room_name'];
        $joinScene = $params['join_scene'];
        $starTime = $params['start_time'];
        $endTime = $params['end_time'];
        $page = (int) $params['page'];
        $perPage = (int) $params['perPage'];

        $data = $this->model::from($this->model::query()->getModel()->getTable() . ' as auto_tag_record')
            ->join(WorkContact::query()->getModel()->getTable() . ' as c', 'auto_tag_record.contact_id', 'c.id')
            ->join(WorkEmployee::query()->getModel()->getTable() . ' as e', 'auto_tag_record.employee_id', 'e.id')
            ->leftJoin(WorkContactRoom::query()->getModel()->getTable() . ' as cr', function ($join) {
                $join->on('auto_tag_record.contact_id', '=', 'cr.contact_id')->on('auto_tag_record.contact_room_id', '=', 'cr.room_id');
            })
            ->join(WorkRoom::query()->getModel()->getTable() . ' as r', 'auto_tag_record.contact_room_id', 'r.id')
            ->where('auto_tag_record.auto_tag_id', '=', $auto_tag_id)
            ->when(! empty($contactName), function (Builder $query) use ($contactName) {
                return $query->where('c.name', 'like', "%{$contactName}%")
                    ->orWhere('c.nick_name', 'like', "%{$contactName}%");
            })
            ->when(! empty($wxUserId), function (Builder $query) use ($wxUserId) {
                return $query->where('e.wx_user_id', '=', $wxUserId);
            })
            ->when(! empty($roomName), function (Builder $query) use ($roomName) {
                return $query->where('r.name', 'like', "%{$roomName}%");
            })
            ->when(! empty($joinScene), function (Builder $query) use ($joinScene) {
                return $query->where('cr.join_scene', '=', $joinScene);
            })
            ->when(! empty($starTime), function (Builder $query) use ($starTime, $endTime) {
                return $query->whereBetween('cr.join_time', [$starTime, $endTime]);
            })
            ->paginate($perPage, [
                'c.id as contactId',
                'c.name as contactName',
                'c.nick_name as contactNickName',
                'c.avatar as contactAvatar',
                'r.name as roomName',
                'e.id as employeeId',
                'e.name as employeeName',
                'cr.join_scene',
                'cr.join_time',
            ], 'page', $page);

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 检索 - 多条
     * @return mixed
     */
    public function getAutoTagRecordByTimeSearch(array $params): array
    {
        $auto_tag_id = $params['auto_tag_id'];
        $contactName = $params['contact_name'];
        $wxUserId = $params['employee'] ?? '';
        $ruleId = $params['rule_id'];
        $starTime = $params['start_time'];
        $endTime = $params['end_time'];
        $page = (int) $params['page'];
        $perPage = (int) $params['perPage'];

        $data = $this->model::from($this->model::query()->getModel()->getTable() . ' as auto_tag_record')
            ->join(WorkContact::query()->getModel()->getTable() . ' as c', 'auto_tag_record.contact_id', 'c.id')
            ->join(WorkEmployee::query()->getModel()->getTable() . ' as e', 'auto_tag_record.employee_id', 'e.id')
            ->where('auto_tag_record.auto_tag_id', '=', $auto_tag_id)
            ->when(! empty($contactName), function (Builder $query) use ($contactName) {
                return $query->where('c.name', 'like', "%{$contactName}%")
                    ->orWhere('c.nick_name', 'like', "%{$contactName}%");
            })
            ->when(! empty($wxUserId), function (Builder $query) use ($wxUserId) {
                return $query->where('e.wx_user_id', '=', $wxUserId);
            })
            ->when(! empty($ruleId), function (Builder $query) use ($ruleId) {
                return $query->where('auto_tag_record.tag_rule_id', '=', $ruleId);
            })
            ->when(! empty($starTime), function (Builder $query) use ($starTime, $endTime) {
                return $query->whereBetween('c.created_at', [$starTime, $endTime]);
            })
            ->paginate($perPage, [
                'auto_tag_record.created_at',
                'auto_tag_record.tag_rule_id',
                'c.id as contactId',
                'c.name as contactName',
                'c.nick_name as contactNickName',
                'c.avatar as contactAvatar',
                'e.id as employeeId',
                'e.name as employeeName',
            ], 'page', $page);

        $data || $data = collect([]);
        return $data->toArray();
    }
}
