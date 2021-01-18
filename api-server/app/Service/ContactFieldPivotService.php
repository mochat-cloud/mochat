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

use App\Contract\ContactFieldPivotServiceInterface;
use App\Model\ContactFieldPivot;
use MoChat\Framework\Service\AbstractService;

class ContactFieldPivotService extends AbstractService implements ContactFieldPivotServiceInterface
{
    /**
     * @var ContactFieldPivot
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactFieldPivotById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactFieldPivotsById(array $ids, array $columns = ['*']): array
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
    public function getContactFieldPivotList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createContactFieldPivot(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createContactFieldPivots(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateContactFieldPivotById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteContactFieldPivot(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteContactFieldPivots(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 根据客户id查询多条
     * @param int $contactId 客户id
     * @param array $columns 查询字段
     * @return array 返回值
     */
    public function getContactFieldPivotsByContactId($contactId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('contact_id', $contactId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 根据客户高级属性id、用户画像值和类型查询多条
     * @param int $fieldId 高级属性id
     * @param int|string $value 用户画像值
     * @param array $columns 查询字段
     * @return array 返回值
     */
    public function getContactFieldPivotsByFieldIdValue($fieldId, $value, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('contact_field_id', $fieldId)
            ->where('value', 'like', "%{$value}%")
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 根据客户id和客户属性id查询多条
     * @param int $contactId 客户id
     * @param array $fieldId 客户属性id
     * @param array $columns 查询字段
     * @return array 返回值
     */
    public function getContactFieldPivotsByOtherId($contactId, array $fieldId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('contact_id', $contactId)
            ->whereIn('contact_field_id', $fieldId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }
}
