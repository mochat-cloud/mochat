<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\Medium;

use App\Constants\Medium\Type;
use App\Contract\MediumServiceInterface;
use App\Logic\WeWork\AppTrait;
use Hyperf\Utils\ApplicationContext;

class Medium
{
    use AppTrait;

    public function getWxMediumId(array $ids, int $corpId): array
    {
        if (empty($ids)) {
            return [];
        }
        $mediaClient = ApplicationContext::getContainer()->get(MediumServiceInterface::class);
        $media       = $mediaClient->getMediaById($ids, ['id', 'media_id', 'last_upload_time', 'type', 'content']);
        if (empty($media)) {
            return [];
        }
        $fileSystem = make(\Hyperf\Filesystem\FilesystemFactory::class)->get('local');

        $resData = [];
        $dbData  = [];
        $wxMedia = $this->wxApp($corpId, 'user')->media;
        foreach ($media as $medium) {
            if (time() - $medium['lastUploadTime'] > 60 * 60 * 24 * 2.5) {
                $uploadFile = json_decode($medium['content'], true);
                $typeText   = self::wxMediaType($medium['type']);
                if (empty($uploadFile[$typeText . 'Path'])) {
                    continue;
                }

                $tmpPath = '/_medium' . time() . $uploadFile[$typeText . 'Path'];
                $tmpFull = $fileSystem->getAdapter()->getPathPrefix() . $tmpPath;
                $fileSystem->write($tmpPath, file_get_contents(file_full_url($uploadFile[$typeText . 'Path'])));
                // 语音转换amr
                if ($medium['type'] === Type::VOICE) {
                    continue; // todo 等待ffmpeg-amr安装完成
                    $tmpFull = $this->ffmpegToAmr($tmpFull);
                }

                $wxReq = $wxMedia->{'upload' . ucfirst($typeText)}($tmpFull);
                file_exists($tmpFull) && unlink($tmpFull);

                $dbData[] = [
                    'id'               => $medium['id'],
                    'media_id'         => $wxReq['media_id'],
                    'last_upload_time' => $wxReq['created_at'],
                ];
                $medium['mediaId'] = $wxReq['media_id'];
            }
            $resData[$medium['id']] = $medium['mediaId'];
        }

        ## 更新操作
        empty($dbData) || $mediaClient->updateMediaCaseIds($dbData);

        return $resData;
    }

    /**
     * 企业微信素材库类型.
     * @param false $type...
     * @return array|string ...
     */
    protected static function wxMediaType($type = false)
    {
        $res = [
            Type::PICTURE => 'image',
            Type::VOICE   => 'voice',
            Type::VIDEO   => 'video',
            Type::FILE    => 'file',
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
     * @throws \Exception
     * @return string ...
     */
    protected function ffmpegToAmr(string $filePath, bool $isOldUnlink = false): string
    {
        try {
            ## 转amr
            $ffmpeg = \FFMpeg\FFMpeg::create();
            $audio  = $ffmpeg->open($filePath);

            $format  = new \App\Tool\FFMpeg\Format\Audio\Amr();
            $amrPath = substr($filePath, 0, strrpos($filePath, '.', -1)) . '.amr';
            $audio->save($format, $amrPath);
            $isOldUnlink && file_exists($filePath) && unlink($filePath);
            return $amrPath;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
