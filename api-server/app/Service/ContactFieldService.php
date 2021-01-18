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

use App\Contract\ContactFieldServiceInterface;
use App\Model\ContactField;
use MoChat\Framework\Service\AbstractService;

class ContactFieldService extends AbstractService implements ContactFieldServiceInterface
{
    /**
     * @var ContactField
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactFieldById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactFieldsById(array $ids, array $columns = ['*']): array
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
    public function getContactFieldList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createContactField(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createContactFields(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateContactFieldById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteContactField(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteContactFields(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 批量修改 - 根据IDs.
     * @param array $data 数据
     * @return int 修改条数
     */
    public function updateContactFieldsCaseIds(array $data): int
    {
        return $this->model->batchUpdateByIds($data);
    }

    /**
     * 查询多条 - 根据状态.
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactFieldsByStatus(int $status, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('status', $status)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据状态.按排序展示倒序.
     * @param int $status 状态
     * @param array $columns 查询字段
     * @return array 返回值
     */
    public function getContactFieldsByStatusOrderByOrder(int $status, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('status', $status)
            ->orderBy('order', 'desc')
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function existContactFieldsByLabel(string $label, array $where = []): bool
    {
        $model = empty($where) ? $this->model::query() : $this->model->optionWhere($where);
        return $model->where('label', $label)->exists();
    }
}
