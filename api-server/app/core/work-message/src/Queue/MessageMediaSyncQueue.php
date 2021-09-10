<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkMessage\Queue;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use League\Flysystem\Filesystem;
use MoChat\App\WorkMessage\Utils\MessageArchiveFactory;

class MessageMediaSyncQueue
{
    /**
     * @AsyncQueueMessage(pool="message_media")
     * @param int $corpId 企业ID
     * @param $extension
     */
    public function handle(int $corpId, string $msgId, string $sdkFileId, string $path, string $extension)
    {
        try {
            $messageArchive = make(MessageArchiveFactory::class)->get($corpId);
            $file = $messageArchive->getMediaData($sdkFileId, $extension);

            if ($extension === 'amr') {
                $file = $this->amrToMp3($file);
            }

            $filesystem = make(Filesystem::class);
            $filesystem->writeStream($path, fopen($file->getRealPath(), 'rb'));

            $this->updateMediaSyncStatus($corpId, $msgId);
        } catch (\Throwable $e) {
        } finally {
            isset($file) && file_exists($file->getRealPath()) && unlink($file->getRealPath());
        }
    }

    /**
     * 音频转MP3.
     * @param \SplFileInfo $file ...
     * @return \SplFileInfo ...
     */
    protected function amrToMp3(\SplFileInfo $file): \SplFileInfo
    {
        try {
            ## 转mp3
            $ffmpeg = \FFMpeg\FFMpeg::create();
            $audio = $ffmpeg->open($file->getRealPath());

            $format = new \FFMpeg\Format\Audio\Mp3();
            $mp3Path = $file->getPath() . '/' . strval(microtime(true) * 10000) . uniqid() . '.mp3';
            $audio->save($format, $mp3Path);
            return new \SplFileInfo($mp3Path);
        } catch (\Exception $e) {
            return $file;
        } finally {
            isset($mp3Path) && file_exists($mp3Path) && file_exists($file->getRealPath()) && unlink($file->getRealPath());
        }
    }

    protected function updateMediaSyncStatus(int $corpId, string $msgId)
    {
        // TODO 更新媒体文件同步状态
    }
}
