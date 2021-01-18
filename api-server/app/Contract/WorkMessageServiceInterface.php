<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Contract;

interface WorkMessageServiceInterface
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkMessageById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkMessagesById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getWorkMessageList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkMessage(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createWorkMessages(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkMessageById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteWorkMessage(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteWorkMessages(array $ids): int;

    /**
     * 获取内部联系人、外部联第人、群的最近一条信息.
     * @param string $from 发送方id(userId)
     * @param array $toIds 接收方id(userId/externalContactUserId/roomId)
     * @param int $toType 接收方类型 0通讯录 1外部联系人 3群
     * @param array|string[] $columns ...
     * @return array ...
     */
    public function getWorkMessagesLast(string $from, array $toIds, int $toType = 0, array $columns = ['*']): array;

    /**
     * 获取最近一条seq.
     * @return int ...
     */
    public function getWorkMessageIndexEndSeq(): int;

    /**
     * 查询多条 - 根据微信会话唯一标识.
     * @param array $msgIds 微信会话唯一标识
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkMessagesByMsgId(array $msgIds, array $columns = ['*']): array;

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
    public function getWorkMessagesRangeByCorpId(int $corpId, string $from, string $tolist, int $messageId, string $operator, int $limit, string $order, array $columns = ['*']): array;

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
    public function getWorkMessagesRangeByCorpIdWxRoomId(int $corpId, string $wxRoomId, int $messageId, string $operator, int $limit, string $order, array $columns = ['*']): array;
}
