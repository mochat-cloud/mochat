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

use App\Contract\ContactEmployeeTrackServiceInterface;
use App\Model\ContactEmployeeTrack;
use MoChat\Framework\Service\AbstractService;

class ContactEmployeeTrackService extends AbstractService implements ContactEmployeeTrackServiceInterface
{
    /**
     * @var ContactEmployeeTrack
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactEmployeeTrackById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactEmployeeTracksById(array $ids, array $columns = ['*']): array
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
    public function getContactEmployeeTrackList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createContactEmployeeTrack(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createContactEmployeeTracks(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateContactEmployeeTrackById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteContactEmployeeTrack(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteContactEmployeeTracks(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询多条 - 根据客户ID.
     * @param int $contactId 客户ID
     * @param array|string[] $columns 查询字段
     * @param array $orderBy 排序方式
     * @return array 数组
     */
    public function getContactEmployeeTracksByContactId(int $contactId, array $columns = ['*'], array $orderBy = [['contact_employee_track.created_at', 'desc']]): array
    {
        $res = $this->model::query()
            ->where('contact_id', $contactId);
        if (! empty($orderBy) && is_array($orderBy)) {
            foreach ($orderBy as $v) {
                $res->orderBy($v[0], $v[1]);
            }
        }

        $res = $res->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }
}
