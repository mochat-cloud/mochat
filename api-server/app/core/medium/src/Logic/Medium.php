<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Medium\Logic;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\ApplicationContext;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Medium\Constants\Type;
use MoChat\App\Medium\Contract\MediumContract;
use MoChat\App\Utils\Media;

class Medium
{
    use AppTrait;

    /**
     * @Inject
     * @var \League\Flysystem\Filesystem
     */
    protected $filesystem;

    /**
     * @Inject
     * @var Media
     */
    protected $media;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function getWxMediumId(array $ids, int $corpId): array
    {
        if (empty($ids)) {
            return [];
        }
        $mediaService = ApplicationContext::getContainer()->get(MediumContract::class);
        $media = $mediaService->getMediaById($ids, ['id', 'media_id', 'last_upload_time', 'type', 'content']);

        if (empty($media)) {
            return [];
        }

        $resData = [];
        $dbData = [];
        foreach ($media as $medium) {
            if (time() - $medium['lastUploadTime'] <= 60 * 60 * 24 * 3 - 60 * 60 * 2) {
                continue;
            }

            $uploadFile = json_decode($medium['content'], true);
            $mediaType = self::wxMediaType($medium['type']);
            $path = isset($uploadFile[$mediaType . 'Path']) ? $uploadFile[$mediaType . 'Path'] : '';
            $filename = isset($uploadFile[$mediaType . 'Name']) ? $uploadFile[$mediaType . 'Name'] : '';
            if (empty($path)) {
                continue;
            }

            if (! $this->filesystem->fileExists($path)) {
                continue;
            }

            try {
                // TODO 语音转换amr
                $mediaId = $this->media->upload($corpId, $mediaType, $path, $filename);
                $dbData[] = [
                    'id' => $medium['id'],
                    'media_id' => $mediaId,
                    'last_upload_time' => time(),
                ];
                $medium['mediaId'] = $mediaId;
            } catch (\Throwable $e) {
                ## 记录错误日志
                $this->logger->error(sprintf('%s [%s] %s', '定时同步临时媒体文件失败', date('Y-m-d H:i:s'), $e->getMessage()));
            }

            $resData[$medium['id']] = $medium['mediaId'];
        }

        ## 更新操作
        empty($dbData) || $mediaService->updateMediaCaseIds($dbData);

        return $resData;
    }

    /**
     * 企业微信素材库类型.
     * @param false $type ...
     * @return array|string ...
     */
    protected static function wxMediaType($type = false)
    {
        $res = [
            Type::PICTURE => 'image',
            Type::VOICE => 'voice',
            Type::VIDEO => 'video',
            Type::FILE => 'file',
        ];

        if ($type === false) {
            return $res;
        }
        if (isset($res[$type])) {
            return $res[$type];
        }

        return [];
    }

    /**
     * 音频转amr.
     * @param string $filePath ...
     * @param bool $isOldUnlink 是否删除原文件
     */
    protected function ffmpegToAmr(string $filePath, bool $isOldUnlink = true): string
    {
        try {
            ## 转amr
            $ffmpeg = \FFMpeg\FFMpeg::create();
            $audio = $ffmpeg->open($filePath);

            $format = new \MoChat\App\Utils\FFMpeg\Format\Audio\Amr();
            $amrPath = substr($filePath, 0, strrpos($filePath, '.', -1)) . '.amr';
            $audio->save($format, $amrPath);
            $isOldUnlink && file_exists($filePath) && unlink($filePath);
            return $amrPath;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
