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

use App\Contract\WorkContactTagServiceInterface;
use App\Model\WorkContactTag;
use MoChat\Framework\Service\AbstractService;

class WorkContactTagService extends AbstractService implements WorkContactTagServiceInterface
{
    /**
     * @var WorkContactTag
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagsById(array $ids, array $columns = ['*']): array
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
    public function getWorkContactTagList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkContactTag(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createWorkContactTags(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkContactTagById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 修改多条
     */
    public function updateWorkContactTag(array $values): int
    {
        return $this->model->batchUpdateByIds($values);
    }

    /**
     * 修改多条 - 根据ID.
     * @param array $ids id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateAllByIds(array $ids, array $data): int
    {
        return $this->model::query()
            ->whereIn('id', $ids)
            ->update($data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContactTag(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteWorkContactTags(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询多条 - 根据标签名称.
     * @param string $name 标签名称
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagsByName($name, array $columns = ['*']): array
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
     * 查询多条 - 根据多个标签名称和分组id.
     * @param array $names 标签名称
     * @param int $groupId 分组id
     * @param array $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagsByNamesGroupId(array $names, $groupId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('name', $names)
            ->where('contact_tag_group_id', $groupId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据分组id.
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagsByGroupIds(array $groupId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('contact_tag_group_id', $groupId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 修改单条 - 根据标签名称.
     * @param string $name 标签名称
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkContactTagByName(string $name, array $data): int
    {
        return $this->model::query()
            ->where('name', $name)
            ->update($data);
    }

    /**
     * 修改单条 - 根据微信标签id.
     * @param string $wxTagId 微信标签id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkContactTagByWxTagId(string $wxTagId, array $data): int
    {
        return $this->model::query()
            ->where('wx_contact_tag_id', $wxTagId)
            ->update($data);
    }

    /**
     * 查询多条 - 根据企业id和分组id.
     * @param array $corpId 企业id
     * @param array $groupId 分组id
     * @param array $columns 查询字段
     * @return array 返回值
     */
    public function getWorkContactTagsByCorpIdGroupIds(array $corpId, array $groupId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('corp_id', $corpId)
            ->whereIn('contact_tag_group_id', $groupId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据企业id.
     * @param array $corpId 企业id
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagsByCorpId(array $corpId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('corp_id', $corpId)
            ->whereNull('deleted_at')
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据企业id和企业微信标签id.
     * @param array $corpId 企业id
     * @param array $wxTagId 企业微信标签id
     * @param array $columns 查询字段
     * @return array 返回值
     */
    public function getWorkContactTagsByCorpIdWxTagId(array $corpId, array $wxTagId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('corp_id', $corpId)
            ->whereIn('wx_contact_tag_id', $wxTagId)
            ->get($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询单条 - 根据企业微信标签id.
     * @param string $wxTagId 企业微信标签id
     * @param array $columns 查询字段
     * @return array 返回值
     */
    public function getWorkContactTagsByWxTagId(string $wxTagId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('wx_contact_tag_id', $wxTagId)
            ->first($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询单条 - 根据群组id和标签名称.
     * @param int $groupId 群组id
     * @param string $name 标签名称
     * @param array $columns 查询字段
     * @return array 返回值
     */
    public function getWorkContactTagByGroupIdName($groupId, $name, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('contact_tag_group_id', $groupId)
            ->where('name', $name)
            ->first($columns);
        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 查询多条 - 根据ID（包括软删数据）.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkContactTagsSoftById(array $ids, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->withTrashed()
            ->whereIn('id', $ids)
            ->get($columns);
        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }
}
