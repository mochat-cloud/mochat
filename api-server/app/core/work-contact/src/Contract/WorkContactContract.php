<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Contract;

interface WorkContactContract
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactById(int $id, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @param bool $withTrashed 是否包含软删除
     * @return array 数组
     */
    public function getWorkContactsById(array $ids, array $columns = ['*'], bool $withTrashed = false): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getWorkContactList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkContact(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createWorkContacts(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @param bool $withTrashed 是否包含软删除
     * @return int 修改条数
     */
    public function updateWorkContactById(int $id, array $data, bool $withTrashed = false): int;

    /**
     * 修改多条
     */
    public function updateWorkContact(array $values): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContact(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContacts(array $ids): int;

    /**
     * 查询多条 - 根据企业ID和联系人名称.
     * @param int $corpId 企业ID
     * @param string $name 联系人名称（模糊搜索）
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactsByCorpIdName(int $corpId, $name, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据企业ID和客户编号.
     * @param int $corpId 企业ID
     * @param string $businessNo 客户编号
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactsByCorpIdBusinessNo(int $corpId, $businessNo, array $columns = ['*']): array;

    /**
     * 查询多条 - 根据企业ID和性别.
     * @param int $corpId 企业ID
     * @param int $gender 性别
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactsByCorpIdGender(int $corpId, $gender, array $columns = ['*']): array;

    /**
     * 模糊查询客户 - 根据名称与别名.
     * @param string $name ..
     * @param int $corpId ...
     * @param array|string[] $columns ..
     * @param int $limit ..
     * @return array ..
     */
    public function getWorkContactsByNameAlias(string $name, int $corpId, array $columns = ['*'], int $limit = 20): array;

    /**
     * 查询多条 - 根据企业ID.
     * @param int $corpId 企业ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactsByCorpId($corpId, array $columns = ['*']): array;

    /**
     * 查询单条 - 根据企业微信客户id.
     * @param string $wxExternalUserId 企业微信客户id
     * @param array $columns 字段
     * @return array 返回值
     */
    public function getWorkContactByWxExternalUserId($wxExternalUserId, array $columns = ['*']): array;

    /**
     * @param int $corpId 公司授信ID
     * @param string $wxExternalUserId 微信外部联系人ID
     * @param array $columns 查询字段
     * @param bool $withTrashed 是否包含软删除
     * @return array 响应数组
     */
    public function getWorkContactByCorpIdWxExternalUserId(int $corpId, $wxExternalUserId, array $columns = ['*'], bool $withTrashed = false): array;

    /**
     * @param int $corpId 公司授信ID
     * @param array $wxExternalUserIds 微信外部联系人ID
     * @param array $columns 查询字段
     * @return array 响应数组
     */
    public function getWorkContactByCorpIdWxExternalUserIds(int $corpId, $wxExternalUserIds, array $columns = ['*']): array;

    /**
     * 查询单条 - 根据企业微信unionid.
     * @param string $unionid 企业微信客户id
     * @param array $columns 字段
     * @return array 返回值
     */
    public function getWorkContactByUnionId(string $unionid, array $columns = ['*']): array;

    /**
     * 获取客户总数.
     */
    public function countWorkContactsByIdGender(array $Id, int $gender): int;

    /**
     * 批量修改 - 根据IDs.
     * @param array $data 数据
     * @return int 修改条数
     */
    public function updateWorkContactsCaseIds(array $data): int;

    /**
     * 获取客户总数.
     */
    public function countWorkContactsById(array $Id): int;

    /**
     * 判断用户是否有师傅.
     * @param array|string[] $columns
     */
    public function getWorkContactByWxExternalUserIdParent(int $parent, string $externalUserID, array $columns = ['*']): array;

    /**
     * 查询单条-CorpIdUnionId.
     * @param array|string[] $columns
     */
    public function getWorkContactByCorpIdUnionId(int $corpId, string $unionId, array $columns = ['*']): array;

    /**
     * 检索 - 多条
     */
    public function getWorkContactsByEmployeeIdSearch(int $corpId, int $employeeId, array $params): array;

    /**
     * 检索 - 多条
     */
    public function getWorkContactsBySearch(int $corpId, array $employeeIds, array $params): array;

    /**
     * 检索 - 条数.
     */
    public function countWorkContactsBySearch(int $corpId, array $params): int;

    /**
     * 查询客户数量.
     */
    public function countWorkContactsByCorpIdStateStatus(int $corpId, array $state, int $status, string $day = ''): int;

    /**
     * 检索 - 多条
     */
    public function getWorkContactsByStateSearch(int $corpId, array $state, array $params): array;

    /**
     * 发送欢迎语.
     *
     * @param int|string $corpId 企业id
     * @param array $contact 客户信息
     * @param string $welcomeCode 发送欢迎语的凭证
     * @param array $content 欢迎语内容
     */
    public function sendWelcome($corpId, array $contact, string $welcomeCode, array $content);

    /**
     * 获取发送欢迎语状态 1-已发送 0-未发送
     *
     * @param int $contactId
     *
     * @return int
     */
    public function getWelcomeStatus(int $contactId): int;

    /**
     * 设置发送欢迎语状态 1-已发送 0-未发送
     *
     * @param int $contactId
     *
     * @return int
     */
    public function setWelcomeStatus(int $contactId, int $status): bool;
}
