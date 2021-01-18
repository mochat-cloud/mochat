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

use App\Contract\ChannelCodeServiceInterface;
use App\Model\ChannelCode;
use MoChat\Framework\Service\AbstractService;

class ChannelCodeService extends AbstractService implements ChannelCodeServiceInterface
{
    /**
     * @var ChannelCode
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getChannelCodeById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getChannelCodesById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createChannelCode(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createChannelCodes(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateChannelCodeById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 修改多条
     * @param array $values 参数
     * @return int 返回值
     */
    public function updateChannelCode(array $values): int
    {
        return $this->model->batchUpdateByIds($values);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteChannelCode(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteChannelCodes(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询多条 - 根据企业ID.
     * @param array $corpId 企业ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getChannelCodesByCorpId(array $corpId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('corp_id', $corpId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getChannelCodeList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 查询多条 - 根据分组名称.
     * @param string $name 分组名
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getChannelCodesByName($name, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('name', $name)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询所有渠道码
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getChannelCodes(array $columns = ['*']): array
    {
        $res = $this->model::query()->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 多条分页 - 客户列表分页搜索.
     * @param array $where 条件
     * @param array $columns 字段
     * @param int $perPage 每页显示数
     * @param int $page 页码
     * @param array $orderBy 排序
     * @return array 返回值
     */
    public function getWorkContactEmployeeIndex(array $where, array $columns = ['*'], int $perPage, int $page, $orderBy = [['work_contact_employee.create_time', 'desc']]): array
    {
        $model = $this->model::query();

        //标明是流失客户 需查询软删的数据
        if (! empty($where['isFlag'])) {
            $model = $model->withTrashed();
        }
        if (! empty($where['corpId'])) {
            $model = $model->whereIn('corp_id', $where['corpId']);
        }
        if (! empty($where['remark'])) {
            $model = $model->where('remark', 'like', "%{$where['remark']}%");
        }
        if (! empty($where['status'])) {
            $model = $model->where('status', $where['status']);
        }
        if (isset($where['addWay']) && is_numeric($where['addWay'])) {
            $model = $model->where('add_way', $where['addWay']);
        }
        if (! empty($where['contactId'])) {
            $model = $model->whereIn('contact_id', $where['contactId']);
        }
        if (! empty($where['noContactId'])) {
            $model = $model->whereNotIn('contact_id', $where['noContactId']);
        }
        if (! empty($where['employeeId'])) {
            if (is_array($where['employeeId'])) {
                $model = $model->whereIn('employee_id', $where['employeeId']);
            } else {
                $model = $model->where('employee_id', $where['employeeId']);
            }
        }
        if (! empty($where['startTime'])) {
            $model = $model->where('create_time', '>=', $where['startTime']);
        }
        if (! empty($where['endTime'])) {
            $model = $model->where('create_time', '<=', $where['endTime']);
        }

        if (! empty($orderBy) && is_array($orderBy)) {
            foreach ($orderBy as $v) {
                $model->orderBy($v[0], $v[1]);
            }
        }

        $res         = $model->paginate($perPage, $columns, 'page', $page);
        $res || $res = collect([]);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }
}
