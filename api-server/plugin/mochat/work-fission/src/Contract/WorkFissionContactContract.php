<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\WorkFission\Contract;

interface WorkFissionContactContract
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkFissionContactById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkFissionContactsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getWorkFissionContactList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 计算客户总数.
     * @param array $fissionId 活动id
     * @return int 返回值
     */
    public function countWorkFissionContactsByFissionId(array $fissionId): int;

    /**
     * 计算流失客户总数.
     * @param array $fissionId 活动id
     * @return int 返回值
     */
    public function countWorkFissionContactsLossByFissionId(array $fissionId): int;

    /**
     * 计算今日新增客户总数.
     * @param array $fissionId 活动id
     * @return int 返回值
     */
    public function countWorkFissionContactsTodayByFissionId(array $fissionId): int;

    /**
     * 获取裂变用户总数.
     */
    public function countWorkFissionContactsByLevel(array $fissionId, int $level): int;

    /**
     * 客户参与-上级.
     * @param int $parentId 活动id
     * @param array $columns 字段
     * @return array 数组
     */
    public function getWorkFissionContactByParent(int $parentId, array $columns = ['*']): array;

    /**
     * @param string $unionId 客户微信id
     * @param array|string[] $columns 字段
     * @return array 数组
     */
    public function getWorkFissionContactByUnionId(string $unionId, array $columns = ['*']): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkFissionContact(array $data): int;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkFissionContactById(int $id, array $data): int;

    /**
     * 计算新客户总数.
     * @param array $fissionId 活动id
     * @return int 返回值
     */
    public function countWorkFissionContactsNewByFissionId(array $fissionId): int;

    /**
     * 计算新客户流失总数.
     * @param array $fissionId 活动id
     * @return int 返回值
     */
    public function countWorkFissionContactsNewLossByFissionId(array $fissionId): int;

    /**
     * 计算助力客户总数.
     * @param array $fissionId 活动id
     * @return int 返回值
     */
    public function sumWorkFissionContactsInviteByFissionId(array $fissionId);

    /**
     * 计算用户邀请新用户总数.
     * @param int $parent_id 邀请人id
     */
    public function countWorkFissionContactNewByParent(int $parent_id): int;

    /**
     * 计算用户邀请用户流失总数.
     * @param int $parent_id 邀请人id
     */
    public function countWorkFissionContactLossByParent(int $parent_id): int;

    /**
     * 计算一级客户总数.
     * @param array $fissionId 活动id
     */
    public function countWorkFissionContactByFissionIds(array $fissionId): int;

    /**
     * 计算一级客户总数.
     * @param int $fissionId 活动id
     */
    public function countWorkFissionContactByFissionId(int $fissionId): int;

    /**
     * 获取邀请客户数量.
     */
    public function countWorkFissionContactByParent(int $parent, int $fissionId): int;

    /**
     * 判断用户是否有师傅.
     * @param array|string[] $columns
     */
    public function getWorkContactByWxExternalUserIdParent(int $parent, string $externalUserID, array $columns = ['*']): array;

    /**
     * 查询客户信息-unionId&fissionId.
     * @param array|string[] $columns
     */
    public function getWorkFissionContactByFissionIDUnionId(int $fissionID, string $unionId, array $columns = ['*']): array;

    /**
     * 获取邀请客户数量.
     */
    public function countWorkFissionContactByParentLoss(int $parent, int $fissionId, string $loss = ''): int;
}
