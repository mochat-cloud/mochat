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

interface WorkUnionidExternalUseridMappingContract
{
    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkMapping(array $data): int;

    /**
     * 查询单条 - 根据corp,unionid和openid.
     * @param int $corpId 企业id
     * @param string $unionid unionid
     * @param string $openid openid
     * @param array|string[] $columns 查询字段
     * @param  int $subjectType 主体类型
     * @return array 数组
     */
    public function getWorkMappingByCorpUnionidOpenidSubjectType(int $corpId, string $unionid, string $openid, int $subjectType, array $columns = ['*']): array;

    /**
     * 查询单条 - 根据corp和external_userid
     * @param int $corpId 企业id
     * @param string $externalUserid external_userid
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkMappingByCorpExternalUserid(int $corpId, string $externalUserid, array $columns = ['*']): array;

    /**
     * 查询单条 - 根据corp和pending_id
     * @param int $corpId
     * @param string $pendingId
     * @param array|string[] $columns
     * @return array
     */
    public function getWorkMappingByCorpPendingId(int $corpId, string $pendingId, array $columns = ['*']): array;

    /**
     * 修改单条 - 根据ID
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkMappingById(int $id, array $data): int;
}