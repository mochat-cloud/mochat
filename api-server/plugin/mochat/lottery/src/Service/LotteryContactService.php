<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Lottery\Service;

use MoChat\Framework\Service\AbstractService;
use MoChat\Plugin\Lottery\Contract\LotteryContactContract;
use MoChat\Plugin\Lottery\Model\LotteryContact;

class LotteryContactService extends AbstractService implements LotteryContactContract
{
    /**
     * @var LotteryContact
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getLotteryContactById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getLotteryContactsById(array $ids, array $columns = ['*']): array
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
    public function getLotteryContactList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createLotteryContact(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createLotteryContacts(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateLotteryContactById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 修改多条 - 根据IDs.
     * @param array $ids id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateLotteryContact(array $ids, array $data): int
    {
        return $this->model::query()->whereIn('id', $ids)->update($data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteLotteryContact(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteLotteryContacts(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询.
     */
    public function getLotteryContactByLotteryId(int $lotteryId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('lottery_id', $lotteryId)
            ->get($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }

    /**
     * 查询总参与人数-LotteryId.
     */
    public function countLotteryContactByLotteryIdDrawNum(int $lotteryId): int
    {
        return $this->model::query()
            ->where('lottery_id', $lotteryId)
            ->where('draw_num', '>', 0)
            ->count('id');
    }

    /**
     * 查询总浏览人数-LotteryId.
     */
    public function countLotteryContactByLotteryId(int $lotteryId): int
    {
        return $this->model::query()
            ->where('lottery_id', $lotteryId)
            ->count('id');
    }

    /**
     * 查询总获奖人数.
     */
    public function countLotteryContactByLotteryIdWinNum(int $lotteryId): int
    {
        return $this->model::query()
            ->where('lottery_id', $lotteryId)
            ->where('win_num', '>', 0)
            ->count('id');
    }

    /**
     * 查询今日总参与人数-LotteryId.
     */
    public function countLotteryContactTodayByLotteryIdDrawNum(int $lotteryId): int
    {
        return $this->model::query()
            ->where('updated_at', '>', date('Y-m-d'))
            ->where('lottery_id', $lotteryId)
            ->where('draw_num', '>', 0)
            ->count('id');
    }

    /**
     * 查询今日总浏览人数-LotteryId.
     */
    public function countLotteryContactTodayByLotteryId(int $lotteryId): int
    {
        return $this->model::query()
            ->where('updated_at', '>', date('Y-m-d'))
            ->where('lottery_id', $lotteryId)
            ->count('id');
    }

    /**
     * 查询今日总获奖人数.
     */
    public function countLotteryContactTodayByLotteryIdWinNum(int $lotteryId): int
    {
        return $this->model::query()
            ->where('updated_at', '>', date('Y-m-d'))
            ->where('lottery_id', $lotteryId)
            ->where('win_num', '>', 0)
            ->count('id');
    }

    /**
     * 查询一条
     * @param array|string[] $columns
     */
    public function getLotteryContactByLotteryIdUnionId(int $lotteryId, string $unionId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('lottery_id', $lotteryId)
            ->where('union_id', $unionId)
            ->first($columns);
        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }
}
