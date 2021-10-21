<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkMessage\Service;

use Hyperf\Database\Query\Builder;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Str;
use MoChat\App\WorkMessage\Contract\WorkMessageContract;
use MoChat\App\WorkMessage\Model\WorkMessage;
use MoChat\Framework\Service\AbstractService;
use Psr\SimpleCache\CacheInterface;

class WorkMessageService extends AbstractService implements WorkMessageContract
{
    /**
     * @var WorkMessage
     */
    protected $model;

    /**
     * @var Builder
     */
    protected $query;

    /**
     * @Inject
     * @var CacheInterface
     */
    protected $cache;

    /**
     * {@inheritdoc}
     */
    public function __construct(int $corpId)
    {
        $this->model = make(WorkMessage::class)->initTable($corpId);
        $this->query = Db::table($this->model->getTable());
    }

    protected function newQuery()
    {
        return Db::table($this->model->getTable());
    }

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkMessageById(int $id, array $columns = ['*']): array
    {
        $data = $this->newQuery()->find($id, $columns);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkMessagesById(array $ids, array $columns = ['*']): array
    {
        $data = $this->newQuery()->whereIn('id', $ids)->get($columns);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getWorkMessageList(array $where, array $columns = ['*'], array $options = []): array
    {
        $model = $this->model->optionWhere($where, $options)->from($this->model->getTable());

        ## 分页参数
        $perPage = isset($options['perPage']) ? (int) $options['perPage'] : 15;
        $pageName = $options['pageName'] ?? 'page';
        $page = isset($options['page']) ? (int) $options['page'] : null;

        ## 分页
        $data = $model->paginate($perPage, $columns, $pageName, $page);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkMessage(array $data): int
    {
        $newData = $this->model->columnsFormat($data, true, true);
        return $this->newQuery()->insertGetId($newData);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createWorkMessages(array $data): bool
    {
        $newData = array_map(function ($item) {
            return $this->model->columnsFormat($item, true, true);
        }, $data);
        return $this->newQuery()->insert($newData);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkMessageById(int $id, array $data): int
    {
        $newData = $this->model->columnsFormat($data, true, true);
        return $this->newQuery()->where('id', $id)->update($newData);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteWorkMessage(int $id): int
    {
        return (int) $this->newQuery()->where('id', $id)->delete();
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteWorkMessages(array $ids): int
    {
        return (int) $this->newQuery()->whereIn('id', $ids)->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkMessagesLast(string $from, array $toIds, int $toType = 0, array $columns = ['*']): array
    {
        $subQuery = $this->newQuery()->where('from', $from)->where('tolist_type', $toType);
        if ($toType !== 2) {
            $subQuery->where(function ($query) use ($toIds) {
                foreach ($toIds as $key => $toId) {
                    $key === 0 && $query->whereJsonContains('tolist_id', $toId);
                    $key && $query->orWhereJsonContains('tolist_id', $toId);
                }
            });
        } else {
            $subQuery->whereIn('room_id', $toIds);
        }
        $subQuery->where('action', '<>', 2)->orderBy('msg_time', 'DESC')->limit(9999);

        $data = $this->query->fromSub($subQuery, 't')->groupBy(['from'])->get($columns);
        if ($data === null) {
            return [];
        }

        $data = $data->map(function ($item) {
            return $this->toCamel((array) $item);
        });
        return $data->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkMessageIndexEndSeq(): int
    {
        return (int) $this->newQuery()->orderBy('msg_time', 'desc')->limit(1)->value('seq');
    }

    /**
     * 查询多条 - 根据微信会话唯一标识.
     * @param array $msgIds 微信会话唯一标识
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkMessagesByMsgId(array $msgIds, array $columns = ['*']): array
    {
        $data = $this->newQuery()
            ->whereIn('msg_id', $msgIds)
            ->get($columns);

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * @param int $corpId 企业ID
     * @param string $from 发送者
     * @param string $tolist 接收者
     * @param int $messageId 信息ID
     * @param string $operator 操作符
     * @param int $limit 查询条数
     * @param string $order 排序
     * @param array|string[] $columns
     * @return array 响应数组
     */
    public function getWorkMessagesRangeByCorpId(int $corpId, string $from, string $tolist, int $messageId, string $operator, int $limit, string $order, array $columns = ['*']): array
    {
        $data = $this->newQuery()
            ->where('corp_id', $corpId)
            ->where('id', $operator, $messageId)
            ->whereRaw("((`from` = ? AND `tolist`->'$[0]' = ?) OR (`from` = ? AND `tolist`->'$[0]' = ?))", [$from, $tolist, $tolist, $from])
            ->orderByRaw($order)
            ->limit($limit)
            ->get($columns);

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * @param int $corpId 企业ID
     * @param string $wxRoomId 微信群聊ID
     * @param int $messageId 信息ID
     * @param string $operator 操作符
     * @param int $limit 查询条数
     * @param string $order 排序
     * @param array|string[] $columns
     * @return array 响应数组
     */
    public function getWorkMessagesRangeByCorpIdWxRoomId(int $corpId, string $wxRoomId, int $messageId, string $operator, int $limit, string $order, array $columns = ['*']): array
    {
        $data = $this->newQuery()
            ->where('corp_id', $corpId)
            ->where('id', $operator, $messageId)
            ->where('wx_room_id', $wxRoomId)
            ->orderByRaw($order)
            ->limit($limit)
            ->get($columns);

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 查询多条.
     * @param array|string[] $columns
     */
    public function getWorkMessagesByMsgIdActionFrom(array $msgIds, array $columns = ['*']): array
    {
        $data = $this->newQuery()
            ->whereIn('msg_id', $msgIds)
            ->where('action', 0)
            ->where('from', 'like', 'wm%')
            ->where('room_id', 0)
            ->get($columns);

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 查询多条.
     * @param array|string[] $columns
     */
    public function getWorkMessagesByCorpIdActionFrom(int $corpId, string $from, string $start_time, array $columns = ['*']): array
    {
        $data = $this->newQuery()
            ->where('corp_id', $corpId)
            ->where('action', 0)
            ->where('from', $from)
            ->where('room_id', 0)
            ->where('msg_type', 1)
            ->where('created_at', '>', $start_time)
            ->get($columns);

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkMessageMaxId(): int
    {
        return (int) $this->newQuery()->orderBy('id', 'desc')->limit(1)->value('id');
    }

    /**
     * 查询多条
     */
    public function getWorkMessageByCorpIdRoomIdMsgType(int $corpId, array $roomIds, int $msgType, array $between_id, array $columns = ['*']): array
    {
        $data = $this->newQuery()
            ->whereBetween('id', $between_id)
            ->where('corp_id', $corpId)
            ->where('from', 'like', 'wm%')
            ->whereIn('room_id', $roomIds)
            ->where('msg_type', $msgType)
            ->limit(5000)
            ->get($columns);

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 查询多条
     */
    public function getWorkMessageByCorpIdRoomIdMsgTypeKeyword(int $corpId, array $roomIds, int $msgType, string $keyword, array $between_id, array $columns = ['*']): array
    {
        $data = $this->newQuery()
            ->whereBetween('id', $between_id)
            ->where('corp_id', $corpId)
            ->where('from', 'like', 'wm%')
            ->whereIn('room_id', $roomIds)
            ->where('msg_type', $msgType)
            ->where('content->content', 'like', "%{$keyword}%")
            ->limit(5000)
            ->get($columns);

        $data || $data = collect([]);
        return $data->toArray();
    }

    public function getLastMessageByEmployeeWxIdAndContactWxId(string $employeeWxId, string $contactWxId)
    {
        $data = $this->newQuery()
            ->where('tolist_type', 1)
            ->where('action', 0)
            ->where('from', $employeeWxId)
            ->where('tolist', 'like', "%{$contactWxId}%")
            ->orderByDesc('msg_time')
            ->limit(1)
            ->get();

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 最后一条群信息.
     */
    public function getWorkMessagesRangeByIdCorpIdWxRoomIdMsgTime(int $corpId, int $roomId, array $columns = ['*']): array
    {
        $data = $this->newQuery()
            ->whereRaw('action = 0')
            ->whereRaw('corp_id = ?', [$corpId])
            ->whereRaw('room_id = ?', [$roomId])
            ->orderByRaw('msg_time DESC')
            ->limit(1)
            ->get($columns);

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 获取最近一条seq.
     *
     * @return int ...
     */
    public function getWorkMessageLastSeqByCorpId(int $corpId): int
    {
        if ($seq = $this->cache->get(sprintf('mochat:messageLastSeq:%d', $corpId))) {
            return (int) $seq;
        }
        return (int) $this->newQuery()->where('corp_id', $corpId)->orderBy('msg_time', 'desc')->limit(1)->value('seq');
    }

    /**
     * 设置最后一次更新的seq.
     *
     * @return mixed
     */
    public function updateWorkMessageLastSeqByCorpId(int $corpId, int $seq)
    {
        return $this->cache->set(sprintf('mochat:messageLastSeq:%d', $corpId), $seq, -1);
    }

    protected function toCamel(array $data): array
    {
        $newData = [];
        foreach ($data as $key => $item) {
            $newData[Str::camel($key)] = $item;
        }
        return $newData;
    }
}
