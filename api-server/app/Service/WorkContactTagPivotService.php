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

use App\Contract\WorkContactTagPivotServiceInterface;
use App\Model\WorkContactTagPivot;
use Hyperf\DbConnection\Db;
use MoChat\Framework\Service\AbstractService;

class WorkContactTagPivotService extends AbstractService implements WorkContactTagPivotServiceInterface
{
    /**
     * @var WorkContactTagPivot
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotsById(array $ids, array $columns = ['*']): array
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
    public function getWorkContactTagPivotList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkContactTagPivot(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createWorkContactTagPivots(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkContactTagPivotById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContactTagPivot(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContactTagPivots(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询标签下的客户数.
     * @param array $tagIds 标签id
     * @return array 返回值
     */
    public function countWorkContactTagPivotsByTagIds(array $tagIds)
    {
        $res = Db::table('work_contact_tag_pivot')
            ->whereIn('contact_tag_id', $tagIds)
            ->selectRaw('contact_tag_id')
            ->selectRaw('count(contact_id) as total')
            ->groupBy('contact_tag_id')
            ->get();

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据多个客户ID.
     * @param array $contactIds 企业ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotsByContactIds(array $contactIds, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('contact_id', $contactIds)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条（包含软删数据） - 根据多个客户ID.
     * @param array $contactIds 客户ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotsSoftByContactIds(array $contactIds, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->withTrashed()
            ->whereIn('contact_id', $contactIds)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据多个客户ID和标签ID.
     * @param array $contactIds 客户ID
     * @param array $tagIds 标签ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotsByContactIdsTagIds(array $contactIds, array $tagIds, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('contact_id', $contactIds)
            ->whereIn('contact_tag_id', $tagIds)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据客户id.
     * @param int $contactId ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotsByContactId($contactId, array $columns = ['*']): array
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
     * 删除 - 多条 根据标签id.
     * @return int 删除条数
     */
    public function deleteWorkContactTagPivotsByTagId(array $tagId): int
    {
        return $this->model::query()
            ->whereIn('contact_tag_id', $tagId)
            ->delete();
    }

    /**
     * 查询多条 - 根据客户id和员工id.
     * @param int $contactId 客户id
     * @param int $employeeId 员工id
     * @param array $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotsByOtherId($contactId, $employeeId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('contact_id', $contactId)
            ->where('employee_id', $employeeId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条（包含软删数据） - 根据客户id和员工id.
     * @param int $contactId ID
     * @param int $employeeId ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotsSoftByOtherId($contactId, $employeeId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->withTrashed()
            ->where('contact_id', $contactId)
            ->where('employee_id', $employeeId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 删除多条 根据客户id和员工id和标签id.
     * @param int $contactId 客户id
     * @param int $employeeId 员工id
     * @param array $tagIds 标签id
     * @return int 返回多条
     */
    public function deleteWorkContactTagPivotsByOtherId($contactId, $employeeId, array $tagIds): int
    {
        return $this->model::query()
            ->where('contact_id', $contactId)
            ->where('employee_id', $employeeId)
            ->whereIn('contact_tag_id', $tagIds)
            ->delete();
    }

    /**
     * 删除多条 根据客户id.
     * @param array $contactIds 客户id
     * @return int 返回值
     */
    public function deleteWorkContactTagPivotsByContactIds(array $contactIds): int
    {
        return $this->model::query()
            ->whereIn('contact_id', $contactIds)
            ->delete();
    }

    /**
     * 查询多条 - 根据客户ID和员工ID.
     * @param array $contactIds 客户ID
     * @param int $employeeId 员工ID
     * @param array $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagPivotsByContactIdEmployeeId(array $contactIds, int $employeeId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('contact_id', $contactIds)
            ->where('employee_id', $employeeId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 删除多条 根据客户id和员工id.
     * @param array $contactId 客户id
     * @param int $employeeId 员工id
     * @return int 返回多条
     */
    public function deleteWorkContactTagPivotsByOther(array $contactId, int $employeeId): int
    {
        return $this->model::query()
            ->whereIn('contact_id', $contactId)
            ->where('employee_id', $employeeId)
            ->delete();
    }
}
