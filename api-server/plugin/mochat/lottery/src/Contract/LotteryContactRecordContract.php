<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Lottery\Contract;

interface LotteryContactRecordContract
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getLotteryContactRecordById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getLotteryContactRecordsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getLotteryContactRecordList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createLotteryContactRecord(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createLotteryContactRecords(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateLotteryContactRecordById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteLotteryContactRecord(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteLotteryContactRecords(array $ids): int;

    /**
     * 查询单条
     */
    public function getLotteryContactRecordByLotteryId(int $lotteryId, array $columns = ['*']): array;

    /**
     * 查询活动中奖数量--lotteryId.
     */
    public function countLotteryContactRecordByLotteryId(int $lotteryId): int;

    /**
     * 查询奖品抽中数量.
     */
    public function countLotteryContactRecordByLotteryIdPrizeName(int $lotteryId, string $prizeName): int;

    /**
     * 查询多条-客户获奖记录.
     * @param array|string[] $columns
     */
    public function getLotteryContactRecordByLotteryIdContactId(int $lotteryId, int $contactId, array $columns = ['*']): array;

    /**
     * 查询客户抽奖总次数.
     */
    public function countLotteryContactRecordByLotteryIdContactId(int $lotteryId, int $contactId): int;

    /**
     * 查询客户今日抽奖总次数.
     */
    public function countLotteryContactRecordTodayByLotteryIdContactId(int $lotteryId, int $contactId): int;

    /**
     * 查询客户中奖总次数.
     */
    public function countLotteryContactRecordWinByLotteryIdContactId(int $lotteryId, int $contactId): int;

    /**
     * 查询客户今日中奖总次数.
     */
    public function countLotteryContactRecordWinTodayByLotteryIdContactId(int $lotteryId, int $contactId): int;

    /**
     * 获取礼品兑换码
     * @param array|string[] $columns
     */
    public function countLotteryContactRecordReceiveCodeByLotteryIdPrizeName(int $lotteryId, string $prizeName, array $columns = ['*']): array;
}
