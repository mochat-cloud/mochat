<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactSop\Service;

use MoChat\Framework\Service\AbstractService;
use MoChat\Plugin\ContactSop\Contract\ContactSopLogContract;
use MoChat\Plugin\ContactSop\Model\ContactSopLog;

class ContactSopLogService extends AbstractService implements ContactSopLogContract
{
    /**
     * @var ContactSopLog
     */
    protected $model;

    /**
     * 获取需要设置状态的日志.
     * @return array
     */
    public function getNeedStateLogs()
    {
        $res = $this->model::query()
            ->where('type', 1)
            ->get();

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactSopLogById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getContactSopLogsById(array $ids, array $columns = ['*']): array
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
    public function getContactSopLogList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createContactSopLog(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createContactSopLogs(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 清空表数据并添加多条数据.
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function clearTableAndCreateContactSopLogs(int $corpId, array $data): bool
    {
        //清除本企业保存的所有数据
        $this->model->where('corp_id', $corpId)->delete();
        foreach ($data as &$datum) {
            $datum['corp_id'] = $corpId;
        }
        //新增数据
        return $this->createContactSopLogs($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateContactSopLogById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteContactSopLog(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteContactSopLogs(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询多条 - 根据企业ID.
     * @param array $corpIds 企业ID
     * @param array $columns 查询字段
     * @return array 数组
     */
    public function getContactSopLogByCorpId(array $corpIds, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('corp_id', $corpIds)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据企业ID和开关状态.
     * @param array $corpIds 企业ID
     * @param int $state 开关状态
     * @param array $columns 查询字段
     * @return array 数组
     */
    public function getContactSopLogByCorpIdState(array $corpIds, int $state = 1, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('corp_id', $corpIds)
            ->where('state', $state)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 根据多种类型筛选.
     * @param int $corpId 企业id
     * @param int $status 客服类型：1离职分配 2在职分配
     * @param int $type 分配类型：1客户转接 2群聊转接
     * @param string $name 客户/群聊 名称（模糊查询）
     * @param string $employeeWxId 接替的客户id
     * @param string $createTimeStart 分配时间开始
     * @param string $createTimeEnd 分配时间结束
     * @return array
     */
    public function getLogByCorpIdStatusTypeName(int $corpId, int $status, int $type, string $name = '', string $employeeWxId = '', string $createTimeStart = '', string $createTimeEnd = '')
    {
        $query = $this->model::query()
            ->where('corp_id', $corpId)
            ->where('status', $status)
            ->where('type', $type);

        if ($name) {
            $query = $query->where('name', 'like', "%{$name}%");
        }
        if ($employeeWxId) {
            $query = $query->where('takeover_employee_id', $employeeWxId);
        }
        if ($createTimeStart && $createTimeEnd) {
            $query = $query->where('created_at', '>', $createTimeStart);
            $query = $query->where('created_at', '<', $createTimeEnd);
        }

        $res = $query->get();

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * @param string $to
     * @return string
     */
    public function getLogStateByCorpId(int $corpId, string $contactId, string $from)
    {
        $res = $this->model::query()
            ->where('corp_id', $corpId)
            ->where('contact_id', $contactId)
            ->where('handover_employee_id', $from)
            ->first();

        if (empty($res)) {
            return '';
        }

        $resText = [
            1 => '接替完毕',
            2 => '等待接替',
            3 => '客户拒绝',
            4 => '接替成员客户达到上限',
            5 => '无接替记录',
        ];

        return $resText[$res['state']];
    }
}
