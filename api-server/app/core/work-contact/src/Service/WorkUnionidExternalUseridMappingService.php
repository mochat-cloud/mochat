<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Service;

use Hyperf\Database\Query\Builder;
use Hyperf\DbConnection\Db;
use MoChat\App\WorkContact\Contract\WorkUnionidExternalUseridMappingContract;
use MoChat\App\WorkContact\Model\WorkUnionidExternalUseridMapping;
use MoChat\Framework\Service\AbstractService;

class WorkUnionidExternalUseridMappingService extends AbstractService implements WorkUnionidExternalUseridMappingContract
{
    /**
     * @var WorkUnionidExternalUseridMapping
     */
    protected $model;

    public function __construct($corpId)
    {
        $this->model = make(WorkUnionidExternalUseridMapping::class)->initTable($corpId);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkMapping(array $data): int
    {
        $newData = $this->model->columnsFormat($data, true, true);
        return $this->newQuery()->insertGetId($newData);
    }

    /**
     * 查询单条 - 根据corp,unionid和openid.
     * @param int $corpId 企业id
     * @param string $unionid unionid
     * @param string $openid openid
     * @param array|string[] $columns 查询字段
     * @param  int $subjectType 主体类型
     * @return array 数组
     */
    public function getWorkMappingByCorpUnionidOpenidSubjectType(int $corpId, string $unionid, string $openid, int $subjectType, array $columns = ['*']): array
    {
        $data = $this->newQuery()
            ->where('corp_id', $corpId)
            ->where('unionid', $unionid)
            ->where('openid', $openid)
            ->where('subject_type', $subjectType)
            ->first($columns);

        $data || $data = [];
        return (array) $data;
    }

    /**
     * 查询单条 - 根据corp和external_userid
     * @param int $corpId 企业id
     * @param string $externalUserid external_userid
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkMappingByCorpExternalUserid(int $corpId, string $externalUserid, array $columns = ['*']): array
    {
        $data = $this->newQuery()
            ->where('corp_id', $corpId)
            ->where('external_userid', $externalUserid)
            ->first($columns);

        $data || $data = [];
        return (array) $data;
    }

    /**
     * 查询单条 - 根据corp和pending_id
     * @param int $corpId
     * @param string $pendingId
     * @param array|string[] $columns
     * @return array
     */
    public function getWorkMappingByCorpPendingId(int $corpId, string $pendingId, array $columns = ['*']): array
    {
        $data = $this->newQuery()
            ->where('corp_id', $corpId)
            ->where('pending_id', $pendingId)
            ->first($columns);

        $data || $data = [];
        return (array) $data;
    }

    /**
     * 修改单条 - 根据ID
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkMappingById(int $id, array $data): int
    {
        $newData = $this->model->columnsFormat($data, true, true);
        return $this->newQuery()->where('id', $id)->update($newData);
    }

    /**
     * @return Builder
     */
    protected function newQuery()
    {
        return Db::table($this->model->getTable());
    }
}