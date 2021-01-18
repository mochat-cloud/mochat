<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Service;

use App\Contract\CorpDayDataServiceInterface;
use App\Model\CorpDayData;
use MoChat\Framework\Service\AbstractService;

class CorpDayDataService extends AbstractService implements CorpDayDataServiceInterface
{
    /**
     * @var CorpDayData
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    public function getCorpDayDataById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getCorpDayDatasById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getCorpDayDataList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createCorpDayData(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * {@inheritdoc}
     */
    public function createCorpDayDatas(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateCorpDayDataById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteCorpDayData(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteCorpDayDatas(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询多条 - 根据企业id和时间段.
     * @param int $corpId 企业id
     * @param string $startDate 开始日期
     * @param string $endDate 结束日期
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getCorpDayDatasByCorpIdTime(int $corpId, string $startDate, string $endDate, array $columns = ['*']): array
    {
        $res = $this->model->query()
            ->where('corp_id', $corpId)
            ->where('date', '>', $startDate)
            ->where('date', '<', $endDate)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询单条 - 根据企业id和日期.
     * @param int $corpId 企业id
     * @param string $date 日期
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getCorpDayDataByCorpIdDate(int $corpId, string $date, array $columns = ['*']): array
    {
        $res = $this->model->query()
            ->where('corp_id', $corpId)
            ->where('date', $date)
            ->first($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据企业id和日期(按日期正序).
     * @param int $corpId 企业id
     * @param array $date 日期
     * @param array|string[] $columns 查询字段
     * @param array $orderBy 排序
     * @param int $limit 数量(若查全部可不传该字段)
     * @return array 数组
     */
    public function getCorpDayDatasByCorpIdDateOther(int $corpId, array $date, array $columns = ['*'], array $orderBy = [['date', 'asc']], int $limit = 0): array
    {
        $res = $this->model->query()
            ->where('corp_id', $corpId)
            ->whereIn('date', $date);

        if (! empty($orderBy) && is_array($orderBy)) {
            foreach ($orderBy as $v) {
                $res->orderBy($v[0], $v[1]);
            }
        }

        if (empty($limit)) {
            $res = $res->get();
        } else {
            $res = $res->limit($limit)->get($columns);
        }

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }
}
