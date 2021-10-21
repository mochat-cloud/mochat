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

use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Db;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Model\WorkContact;
use MoChat\App\WorkContact\Model\WorkContactEmployee;
use MoChat\App\WorkContact\Model\WorkContactTagPivot;
use MoChat\App\WorkContact\QueueService\SendWelcome;
use MoChat\App\WorkEmployee\Model\WorkEmployee;
use MoChat\Framework\Service\AbstractService;
use MoChat\Plugin\ShopCode\Model\ShopCode;
use Psr\SimpleCache\CacheInterface;

class WorkContactService extends AbstractService implements WorkContactContract
{
    /**
     * @var WorkContact
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @param bool $withTrashed 是否包含软删除
     * @return array 数组
     */
    public function getWorkContactsById(array $ids, array $columns = ['*'], bool $withTrashed = false): array
    {
        $query = $this->model::newModel();
        if ($withTrashed) {
            $query = $query->withTrashed();
        }

        $data = $query->find($ids, $columns);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getWorkContactList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkContact(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createWorkContacts(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @param bool $withTrashed 是否包含软删除
     * @return int 修改条数
     */
    public function updateWorkContactById(int $id, array $data, bool $withTrashed = false): int
    {
        $newData = $this->model->columnsFormat($data, true, true);
        $query = $this->model::newModel();
        if ($withTrashed) {
            $query = $query->withTrashed();
        }

        return $query->where('id', $id)->update($newData);
    }

    /**
     * 修改多条
     */
    public function updateWorkContact(array $values): int
    {
        return $this->model->batchUpdateByIds($values);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContact(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContacts(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询多条 - 根据企业ID和联系人名称.
     * @param int $corpId 企业ID
     * @param string $name 联系人名称（模糊搜索）
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactsByCorpIdName(int $corpId, $name, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $corpId)
            ->where('name', 'like', "%{$name}%")
            ->get($columns);

        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 查询多条 - 根据企业ID和客户编号.
     * @param int $corpId 企业ID
     * @param string $businessNo 客户编号
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactsByCorpIdBusinessNo(int $corpId, $businessNo, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $corpId)
            ->where('business_no', $businessNo)
            ->get($columns);

        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 查询多条 - 根据企业ID和性别.
     * @param int $corpId 企业ID
     * @param int $gender 性别
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactsByCorpIdGender(int $corpId, $gender, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $corpId)
            ->where('gender', $gender)
            ->get($columns);

        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkContactsByNameAlias(string $name, int $corpId, array $columns = ['*'], int $limit = 20): array
    {
        $columns = array_map(function ($item) {
            return is_array($item) ? Db::raw($item[0]) : $item;
        }, $columns);

        $nameStr = '%' . $name . '%';
        $data = $this->model::query()
            ->where('corp_id', $corpId)
            ->where(function ($query) use ($nameStr) {
                $query->where('name', 'LIKE', $nameStr)
                    ->orWhere('nick_name', 'LIKE', $nameStr);
            })
            ->limit($limit)
            ->get($columns);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 查询多条 - 根据企业ID.
     * @param int $corpId 企业ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactsByCorpId($corpId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $corpId)
            ->get($columns);
        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询单条 - 根据企业微信客户id.
     * @param string $wxExternalUserId 企业微信客户id
     * @param array $columns 字段
     * @return array 返回值
     */
    public function getWorkContactByWxExternalUserId($wxExternalUserId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('wx_external_userid', $wxExternalUserId)
            ->first($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * @param int $corpId 公司授信ID
     * @param string $wxExternalUserId 微信外部联系人ID
     * @param array $columns 查询字段
     * @param bool $withTrashed 是否包含软删除
     * @return array 响应数组
     */
    public function getWorkContactByCorpIdWxExternalUserId(int $corpId, $wxExternalUserId, array $columns = ['*'], bool $withTrashed = false): array
    {
        $query = $this->model::query();

        if ($withTrashed) {
            $query = $query->withTrashed();
        }

        $data = $query->where('corp_id', $corpId)
            ->where('wx_external_userid', $wxExternalUserId)
            ->first($columns);

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * @param int $corpId 公司授信ID
     * @param array $wxExternalUserIds 微信外部联系人ID
     * @param array $columns 查询字段
     * @return array 响应数组
     */
    public function getWorkContactByCorpIdWxExternalUserIds(int $corpId, $wxExternalUserIds, array $columns = ['*']): array
    {
        $data = $this->model::query()
            ->where('corp_id', $corpId)
            ->whereIn('wx_external_userid', $wxExternalUserIds)
            ->get($columns);

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 查询单条 - 根据企业微信unionid.
     * @param string $unionid 企业微信客户id
     * @param array $columns 字段
     * @return array 返回值
     */
    public function getWorkContactByUnionId(string $unionid, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('unionid', $unionid)
            ->first($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 获取客户总数.
     */
    public function countWorkContactsByIdGender(array $Id, int $gender): int
    {
        return $this->model::query()
            ->whereIn('id', $Id)
            ->where('gender', $gender)
            ->count();
    }

    /**
     * 获取客户总数.
     */
    public function countWorkContactsById(array $Id): int
    {
        return $this->model::query()
            ->whereIn('id', $Id)
            ->count();
    }

    /**
     * 判断用户是否有师傅.
     */
    public function getWorkContactByWxExternalUserIdParent(int $parent, string $externalUserID, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('contact_superior_user_parent', $parent)
            ->where('external_user_id', $externalUserID)
            ->first($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询单条-CorpIdUnionId.
     * @param array|string[] $columns
     */
    public function getWorkContactByCorpIdUnionId(int $corpId, string $unionId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $corpId)
            ->where('unionid', $unionId)
            ->first($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function updateWorkContactsCaseIds(array $data): int
    {
        return $this->model->batchUpdateByIds($data);
    }

    /**
     * 检索 - 多条
     */
    public function getWorkContactsByEmployeeIdSearch(int $corpId, int $employeeId, array $params): array
    {
        $isAll = $params['is_all'];
        $gender = '';
        $starTime = '';
        $endTime = '';
        $tagIds = '';
        if ($isAll === 1) {
            $gender = isset($params['gender']) ? (int)$params['gender'] : '';
            $starTime = isset($params['start_time']) ? $params['start_time'] : '';
            $endTime = isset($params['end_time']) ? $params['end_time'] : '';
            $tagIds = isset($params['tag_ids']) ? $params['tag_ids'] : '';
        }
        $data = $this->model::from($this->model::query()->getModel()->getTable() . ' as work_contact')
            ->join(WorkContactEmployee::query()->getModel()->getTable() . ' as ce', 'work_contact.id', 'ce.contact_id')
            ->join(WorkContactTagPivot::query()->getModel()->getTable() . ' as ctp', 'work_contact.id', 'ctp.contact_id')
            ->where('work_contact.corp_id', $corpId)
            ->where('ce.employee_id', $employeeId)
            ->when(is_numeric($gender), function (Builder $query) use ($gender) {
                return $query->where('work_contact.gender', '=', $gender);
            })
            ->when(!empty($starTime), function (Builder $query) use ($starTime) {
                return $query->where('work_contact.created_at', '>', "'" . $starTime . "'");
            })
            ->when(!empty($endTime), function (Builder $query) use ($endTime) {
                return $query->where('work_contact.created_at', '<', "'" . $endTime . "'");
            })
            ->when(!empty($tagIds), function (Builder $query) use ($tagIds) {
                return $query->whereIn('ctp.contact_tag_id', $tagIds);
            })
            ->distinct()
            ->get([
                'work_contact.id',
                'work_contact.name',
                'work_contact.wx_external_userid',
            ]);

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 检索 - 多条
     */
    public function getWorkContactsBySearch(int $corpId, array $employeeIds, array $params): array
    {
        $isAll = $params['is_all'];
        $gender = '';
        $starTime = '';
        $endTime = '';
        $tagIds = '';
        if ($isAll === 1) {
            $gender = (isset($params['gender']) && $params['gender'] !== '') ? (int)$params['gender'] : '';
            $starTime = isset($params['start_time']) ? $params['start_time'] : '';
            $endTime = isset($params['end_time']) ? $params['end_time'] : '';
            $tagIds = isset($params['tag_ids']) ? $params['tag_ids'] : '';
        }

        $data = $this->model::from($this->model::query()->getModel()->getTable() . ' as work_contact')
            ->join(WorkContactEmployee::query()->getModel()->getTable() . ' as ce', 'work_contact.id', 'ce.contact_id')
            ->join(WorkEmployee::query()->getModel()->getTable() . ' as e', 'e.id', 'ce.employee_id')
            ->join(WorkContactTagPivot::query()->getModel()->getTable() . ' as ctp', 'work_contact.id', 'ctp.contact_id')
            ->where('work_contact.corp_id', $corpId)
            ->whereIn('ce.employee_id', $employeeIds)
            ->when(is_numeric($gender), function (Builder $query) use ($gender) {
                return $query->where('work_contact.gender', '=', $gender);
            })
            ->when(!empty($starTime), function (Builder $query) use ($starTime) {
                return $query->where('work_contact.created_at', '>', "'" . $starTime . "'");
            })
            ->when(!empty($endTime), function (Builder $query) use ($endTime) {
                return $query->where('work_contact.created_at', '<', "'" . $endTime . "'");
            })
            ->when(!empty($tagIds), function (Builder $query) use ($tagIds) {
                return $query->whereIn('ctp.contact_tag_id', $tagIds);
            })
            ->distinct()
            ->get([
                'work_contact.id as contact_id',
                'work_contact.wx_external_userid',
                'work_contact.name as contact_name',
                'work_contact.nick_name',
                'work_contact.avatar',
                'work_contact.unionid',
                'e.id as employee_id',
                'e.wx_user_id',
            ]);

        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 检索 - 条数.
     */
    public function countWorkContactsBySearch(int $corpId, array $params): int
    {
        $isAll = (int)$params['is_all'];
        $employeeIds = $params['employees'];
        $gender = '';
        $starTime = '';
        $endTime = '';
        $tagIds = '';
        if ($isAll === 1) {
            $gender = (isset($params['gender']) && $params['gender'] !== '') ? (int)$params['gender'] : '';
            $starTime = isset($params['start_time']) ? $params['start_time'] : '';
            $endTime = isset($params['end_time']) ? $params['end_time'] : '';
            $tagIds = isset($params['tag_ids']) ? $params['tag_ids'] : '';
        }

        return $this->model::from($this->model::query()->getModel()->getTable() . ' as work_contact')
            ->join(WorkContactEmployee::query()->getModel()->getTable() . ' as ce', 'work_contact.id', 'ce.contact_id')
            ->join(WorkEmployee::query()->getModel()->getTable() . ' as e', 'e.id', 'ce.employee_id')
            ->join(WorkContactTagPivot::query()->getModel()->getTable() . ' as ctp', 'work_contact.id', 'ctp.contact_id')
            ->where('work_contact.corp_id', $corpId)
            ->whereIn('ce.employee_id', $employeeIds)
            ->when(is_numeric($gender), function (Builder $query) use ($gender) {
                return $query->where('work_contact.gender', '=', $gender);
            })
            ->when(!empty($starTime), function (Builder $query) use ($starTime) {
                return $query->where('work_contact.created_at', '>', "'" . $starTime . "'");
            })
            ->when(!empty($endTime), function (Builder $query) use ($endTime) {
                return $query->where('work_contact.created_at', '<', "'" . $endTime . "'");
            })
            ->when(!empty($tagIds), function (Builder $query) use ($tagIds) {
                return $query->whereIn('ctp.contact_tag_id', $tagIds);
            })
            ->distinct()
            ->count('work_contact.id');
    }

    /**
     * 查询客户数量.
     */
    public function countWorkContactsByCorpIdStateStatus(int $corpId, array $state, int $status, string $day = ''): int
    {
        return $this->model::from($this->model::query()->getModel()->getTable() . ' as work_contact')
            ->join(WorkContactEmployee::query()->getModel()->getTable() . ' as ce', 'work_contact.id', 'ce.contact_id')
            ->where('work_contact.corp_id', $corpId)
            ->where('ce.status', $status)
            ->whereIn('ce.state', $state)
            ->when(!empty($day), function (Builder $query) use ($day) {
                return $query->where('work_contact.created_at', '>', "'" . $day . "'");
            })
            ->distinct()
            ->count('work_contact.id');
    }

    /**
     * 检索 - 多条
     */
    public function getWorkContactsByStateSearch(int $corpId, array $state, array $params): array
    {
        $contactName = empty($params['contactName']) ? '' : $params['contactName'];
        $employeeId = empty($params['employeeId']) ? '' : $params['employeeId'];
        $starTime = empty($params['start_time']) ? '' : $params['start_time'];
        $endTime = empty($params['end_time']) ? '' : $params['end_time'];
        $shopName = empty($params['shopName']) ? '' : $params['shopName'];
        $status = empty($params['status']) ? '' : $params['status'];
        $province = empty($params['province']) ? '' : $params['province'];
        $city = empty($params['city']) ? '' : $params['city'];
        $res = $this->model::from($this->model::query()->getModel()->getTable() . ' as work_contact')
            ->join(WorkContactEmployee::query()->getModel()->getTable() . ' as ce', 'work_contact.id', 'ce.contact_id')
            ->join(WorkEmployee::query()->getModel()->getTable() . ' as e', 'e.id', 'ce.employee_id')
            ->join(ShopCode::query()->getModel()->getTable() . ' as sc', 'e.id', 'sc.employee->id')
            ->where('work_contact.corp_id', $corpId)
            ->whereIn('ce.state', $state)
            ->when(!empty($contactName), function (Builder $query) use ($contactName) {
                return $query->where('work_contact.name', 'like', '%' . $contactName . '%');
            })
            ->when(is_numeric($employeeId), function (Builder $query) use ($employeeId) {
                return $query->where('sc.employee->id', '=', $employeeId);
            })
            ->when(!empty($starTime), function (Builder $query) use ($starTime, $endTime) {
                return $query->whereBetween('work_contact.created_at', [$starTime, $endTime]);
            })
            ->when(!empty($shopName), function (Builder $query) use ($shopName) {
                return $query->where('sc.name', 'like', '%' . $shopName . '%');
            })
            ->when(is_numeric($status), function (Builder $query) use ($status) {
                return $query->where('ce.status', '=', $status);
            })
            ->when(!empty($province), function (Builder $query) use ($province, $city) {
                return $query->where([['sc.province', '=', $province], ['sc.city', '=', $city]]);
            })
            ->distinct()
            ->paginate((int)$params['perPage'], [
                'work_contact.id as contactId',
                'work_contact.name as contactName',
                'work_contact.avatar',
                'work_contact.created_at',
                'ce.status',
                'sc.employee',
                'sc.name as shopName',
                'sc.province',
                'sc.address',
                'sc.city',
            ], 'page', (int)$params['page']);
        $res || $res = collect([]);

        if ($res === null) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 发送欢迎语.
     *
     * @param int|string $corpId 企业id
     * @param array $contact 客户信息
     * @param string $welcomeCode 发送欢迎语的凭证
     * @param array $content 欢迎语内容
     */
    public function sendWelcome($corpId, array $contact, string $welcomeCode, array $content)
    {
        $this->setWelcomeStatus((int)$contact['id'], 1);
        make(SendWelcome::class)->handle($corpId, $contact, $welcomeCode, $content);
    }

    /**
     * 获取发送欢迎语状态 1-已发送 0-未发送
     *
     * @param int $contactId
     *
     * @return int
     */
    public function getWelcomeStatus(int $contactId): int
    {
        $cache = make(CacheInterface::class);
        $result = $cache->get(sprintf('contact:welcome_status:%s', (string)$contactId));
        return $result ? (int)$result : 0;
    }

    /**
     * 设置发送欢迎语状态 1-已发送 0-未发送
     *
     * @param int $contactId
     *
     * @return bool
     */
    public function setWelcomeStatus(int $contactId, int $status): bool
    {
        $cache = make(CacheInterface::class);
        return $cache->set(sprintf('contact:welcome_status:%s', (string)$contactId), $status, 60);
    }
}
