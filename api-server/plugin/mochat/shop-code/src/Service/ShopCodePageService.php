<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ShopCode\Service;

use MoChat\Framework\Service\AbstractService;
use MoChat\Plugin\ShopCode\Contract\ShopCodePageContract;
use MoChat\Plugin\ShopCode\Model\ShopCodePage;

class ShopCodePageService extends AbstractService implements ShopCodePageContract
{
    /**
     * @var ShopCodePage
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getShopCodePageById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getShopCodePagesById(array $ids, array $columns = ['*']): array
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
    public function getShopCodePageList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createShopCodePage(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createShopCodePages(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateShopCodePageById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteShopCodePage(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteShopCodePages(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 查询.
     */
    public function getShopCodePageByCorpIdType(int $corpId, int $type, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->where('corp_id', $corpId)
            ->where('type', $type)
            ->first($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }

    /**
     * 查询多条
     * @param array|string[] $columns
     */
    public function getShopCodePageByCorpIdStatus(array $corpIds, int $status, array $columns = ['*']): array
    {
        $res = $this->model::query()
            ->whereIn('corp_id', $corpIds)
            ->where('status', $status)
            ->get($columns);

        $res || $res = collect([]);

        return $res->toArray();
    }
}
