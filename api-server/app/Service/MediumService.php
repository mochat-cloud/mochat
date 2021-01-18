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

use App\Constants\Medium\Type;
use App\Contract\MediumServiceInterface;
use App\Model\Medium;
use MoChat\Framework\Service\AbstractService;

class MediumService extends AbstractService implements MediumServiceInterface
{
    /**
     * @var Medium
     */
    protected $model;

    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getMediumById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getMediaById(array $ids, array $columns = ['*']): array
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
    public function getMediumList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createMedium(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createMedia(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateMediumById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteMedium(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteMedia(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

    /**
     * 修改多条 - 根据 groupId.
     * @param int $groupId 分组ID
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateMediaByGroupId(int $groupId, array $data): int
    {
        return $this->model::query()->where('medium_group_id', $groupId)->update($data);
    }

    /**
     * 添加签名文件url.
     * @param array $content ...
     * @param int $type ...
     * @return array ...
     */
    public function addFullPath(array $content, int $type): array
    {
        switch ($type) {
            case Type::PICTURE:
                $content['imageFullPath'] = file_full_url($content['imagePath']);
                break;
            case Type::PICTURE_TEXT:
                $content['imageFullPath'] = file_full_url($content['imagePath']);
                break;
            case Type::VOICE:
                $content['voiceFullPath'] = file_full_url($content['voicePath']);
                break;
            case Type::VIDEO:
                $content['videoFullPath'] = file_full_url($content['videoPath']);
                break;
            case Type::MINI_PROGRAM:
                $content['imageFullPath'] = file_full_url($content['imagePath']);
                break;
            case Type::FILE:
                $content['fileFullPath'] = file_full_url($content['filePath']);
                break;
        }
        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMediaCaseIds(array $data): int
    {
        return $this->model->batchUpdateByIds($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getMediaByUpdatingMediaId(int $corpId, array $columns = ['*']): array
    {
        $data = $this->model::query()
            ->where('corp_id', $corpId)
            ->where('last_upload_time', '<', time() - 60 * 60 * 24 * 2.5)
            ->whereIn('type', [Type::PICTURE, Type::VOICE, Type::VIDEO, Type::FILE])
            ->get($columns);
        $data || $data = collect([]);
        return $data->toArray();
    }
}
