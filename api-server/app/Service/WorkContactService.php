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

use App\Contract\WorkContactServiceInterface;
use App\Model\WorkContact;
use Hyperf\DbConnection\Db;
use MoChat\Framework\Service\AbstractService;

class WorkContactService extends AbstractService implements WorkContactServiceInterface
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
     * @return array 数组
     */
    public function getWorkContactsById(array $ids, array $columns = ['*']): array
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
     * @return int 修改条数
     */
    public function updateWorkContactById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
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
        $data    = $this->model::query()
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
     * @return array 响应数组
     */
    public function getWorkContactByCorpIdWxExternalUserId(int $corpId, $wxExternalUserId, array $columns = ['*']): array
    {
        $data = $this->model::query()
            ->where('corp_id', $corpId)
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
}
