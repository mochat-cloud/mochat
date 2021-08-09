<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\OfficialAccount\Service;

use MoChat\App\OfficialAccount\Contract\OfficialAccountContract;
use MoChat\App\OfficialAccount\Model\OfficialAccount;
use MoChat\Framework\Service\AbstractService;

class OfficialAccountService extends AbstractService implements OfficialAccountContract
{
    /**
     * @var OfficialAccount
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getOfficialAccountById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getOfficialAccountsById(array $ids, array $columns = ['*']): array
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
    public function getOfficialAccountList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createOfficialAccount(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createOfficialAccounts(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateOfficialAccountById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteOfficialAccount(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteOfficialAccounts(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询.
     */
    public function getOfficialAccountsByCorpId(int $corpId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $corpId)
            ->where('authorized_status', '<>', 3)
            ->get($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }

    /**
     * 查询一条
     * @param array|string[] $columns
     */
    public function getOfficialAccountByAppIdAuthorizerAppid(string $appId, string $authorizerAppid, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('appid', $appId)
            ->where('authorizer_appid', $authorizerAppid)
            ->first($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }

    /**
     * 查询一条
     */
    public function getOfficialAccountByCorpId(int $corpId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $corpId)
            ->where('authorized_status', '<>', 3)
            ->first($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }

    /**
     * 查询一条
     * @param array|string[] $columns
     */
    public function getOfficialAccountByAppIdAuthorizerAppidCorpId(string $appId, string $authorizerAppid, int $corpId, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('appid', $appId)
            ->where('authorizer_appid', $authorizerAppid)
            ->where('corp_id', $corpId)
            ->first($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }
}
