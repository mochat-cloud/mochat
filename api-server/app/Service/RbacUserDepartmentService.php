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

use App\Contract\RbacUserDepartmentServiceInterface;
use App\Model\RbacUserDepartment;
use MoChat\Framework\Service\AbstractService;

class RbacUserDepartmentService extends AbstractService implements RbacUserDepartmentServiceInterface
{
    /**
     * @var RbacUserDepartment
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRbacUserDepartmentById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getRbacUserDepartmentsById(array $ids, array $columns = ['*']): array
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
    public function getRbacUserDepartmentList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createRbacUserDepartment(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createRbacUserDepartments(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateRbacUserDepartmentById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteRbacUserDepartment(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteRbacUserDepartments(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询多条 - 根据用户ID、公司ID.
     * @param int $userId 用户ID
     * @param int $corpId 公司ID
     * @param array $columns 查询字段
     * @return array 数组
     */
    public function getRbacUserDepartmentsByUserIdCorpId(int $userId, int $corpId, array $columns = ['*']): array
    {
        $data = $this->model::query()
            ->where('user_id', $userId)
            ->where('corp_id', $corpId)
            ->get($columns);

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 删除 - 多条
     * @param int $userId 用户ID
     * @param int $corpId 公司ID
     * @return int 删除条数
     */
    public function deleteRbacUserDepartmentsByUserIdCorpId(int $userId, int $corpId): int
    {
        return $this->model::query()
            ->where('user_id', $userId)
            ->where('corp_id', $corpId)
            ->delete();
    }
}
