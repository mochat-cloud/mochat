<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\WorkFission\Service;

use Hyperf\Database\Model\Builder;
use MoChat\Framework\Service\AbstractService;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContactContract;
use MoChat\Plugin\WorkFission\Model\WorkFissionContact;

class WorkFissionContactService extends AbstractService implements WorkFissionContactContract
{
    /**
     * @var WorkFissionContact
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkFissionContactById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getWorkFissionContactsById(array $ids, array $columns = ['*']): array
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
    public function getWorkFissionContactList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 计算客户总数.
     * @param array $fissionId 活动id
     * @return int 返回值
     */
    public function countWorkFissionContactsByFissionId(array $fissionId): int
    {
        return $this->model::query()
            ->whereIn('fission_id', $fissionId)
            ->count();
    }

    /**
     * 计算流失客户总数.
     * @param array $fissionId 活动id
     * @return int 返回值
     */
    public function countWorkFissionContactsLossByFissionId(array $fissionId): int
    {
        return $this->model::query()
            ->where('loss', 1)
            ->whereIn('fission_id', $fissionId)
            ->count();
    }

    /**
     * 计算今日新增客户总数.
     * @param array $fissionId 活动id
     * @return int 返回值
     */
    public function countWorkFissionContactsTodayByFissionId(array $fissionId): int
    {
        return $this->model::query()
            ->where('created_at', '>', date('Y-m-d'))
            ->whereIn('fission_id', $fissionId)
            ->count();
    }

    /**
     * 获取裂变用户总数.
     * @param int $fissionId
     */
    public function countWorkFissionContactsByLevel(array $fissionId, int $level): int
    {
        return $this->model::query()
            ->where('level', $level)
            ->whereIn('fission_id', $fissionId)
            ->count();
    }

    /**
     * 客户参与-上级.
     * @param int $parentId 活动id
     * @param array $columns 字段
     * @return array 数组
     */
    public function getWorkFissionContactByParent(int $parentId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('contact_superior_user_parent', $parentId)
            ->first($columns);

        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * @param string $unionId 客户微信id
     * @param array|string[] $columns 字段
     * @return array 数组
     */
    public function getWorkFissionContactByUnionId(string $unionId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('union_id', $unionId)
            ->first($columns);

        if (empty($res)) {
            return [];
        }
        return $res->toArray();
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createWorkFissionContact(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateWorkFissionContactById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 计算新客户总数.
     * @param array $fissionId 活动id
     * @return int 返回值
     */
    public function countWorkFissionContactsNewByFissionId(array $fissionId): int
    {
        return $this->model::query()
            ->where('is_new', 1)
            ->whereIn('fission_id', $fissionId)
            ->count();
    }

    /**
     * 计算新客户流失总数.
     * @param array $fissionId 活动id
     * @return int 返回值
     */
    public function countWorkFissionContactsNewLossByFissionId(array $fissionId): int
    {
        return $this->model::query()
            ->where('is_new', 1)
            ->where('loss', 1)
            ->whereIn('fission_id', $fissionId)
            ->count();
    }

    /**
     * 计算新客户流失总数.
     * @param array $fissionId 活动id
     * @return string 返回值
     */
    public function sumWorkFissionContactsInviteByFissionId(array $fissionId)
    {
        return $this->model::query()
            ->whereIn('fission_id', $fissionId)
            ->sum('invite_count');
    }

    /**
     * 计算用户邀请新用户总数.
     * @param int $parent_id 邀请人id
     */
    public function countWorkFissionContactNewByParent(int $parent_id): int
    {
        return $this->model::query()
            ->where('contact_superior_user_parent', $parent_id)
            ->where('is_new', 1)
            ->count();
    }

    /**
     * 计算用户邀请用户流失总数.
     * @param int $parent_id 邀请人id
     */
    public function countWorkFissionContactLossByParent(int $parent_id): int
    {
        return $this->model::query()
            ->where('contact_superior_user_parent', $parent_id)
            ->where('loss', 1)
            ->count();
    }

    /**
     * 计算一级客户总数.
     * @param array $fissionId 活动id
     */
    public function countWorkFissionContactByFissionIds(array $fissionId): int
    {
        return $this->model::query()
            ->where('contact_superior_user_parent', 0)
            ->whereIn('fission_id', $fissionId)
            ->count();
    }

    /**
     * 计算一级客户总数.
     * @param int $fissionId 活动id
     */
    public function countWorkFissionContactByFissionId(int $fissionId): int
    {
        return $this->model::query()
            ->where('fission_id', $fissionId)
            ->count();
    }

    /**
     * 获取邀请客户数量.
     */
    public function countWorkFissionContactByParent(int $parent, int $fissionId): int
    {
        return $this->model::query()
            ->where('contact_superior_user_parent', $parent)
            ->where('fission_id', $fissionId)
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
     * 查询客户信息-unionId&fissionId.
     * @param array|string[] $columns
     */
    public function getWorkFissionContactByFissionIDUnionId(int $fissionID, string $unionId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('fission_id', $fissionID)
            ->where('union_id', $unionId)
            ->first($columns);

        if (empty($res)) {
            return [];
        }

        return $res->toArray();
    }

    /**
     * 获取邀请客户数量.
     */
    public function countWorkFissionContactByParentLoss(int $parent, int $fissionId, string $loss = ''): int
    {
        return $this->model::query()
            ->where('contact_superior_user_parent', $parent)
            ->where('fission_id', $fissionId)
            ->when($loss !== '', function (Builder $query) use ($loss) {
                return $query->where('loss', (int) $loss);
            })
            ->count();
    }
}
