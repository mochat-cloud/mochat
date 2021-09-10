<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Common\QueueService;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

class AsyncFileUpload
{
    /**
     * @AsyncQueueMessage(pool="file", maxAttempts=1)
     * @param array $files 文件路径(path=>file.ext) 如 [
     *                     ["URL或本地文件路径", "oss存储路径"],
     *                     ['https://example.com/path/demo.jpg' ,'path/demo.jpg'],
     *                     ['/tmp/path/demo.jpg' ,'path/demo.jpg'],
     *                     ]
     * @throws \League\Flysystem\FileExistsException ...
     */
    public function upload(array $files): void
    {
        $fileSystem = make(\League\Flysystem\Filesystem::class);

        foreach ($files as $paths) {
            try {
                if (count($paths) < 2) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '异步上传文件参数错误');
                }
                [$localPath, $targetPath] = $paths;
                $isUnlink = $paths[2] ?? 0;
                if (! $localPath) {
                    continue;
                }

                ## url资源
                if (strpos($localPath, 'http') === 0) {
                    $ctx = stream_context_create([
                        'http' => [
                            'timeout' => 180,
                        ],
                    ]);
                    $fileSystem->write($targetPath, file_get_contents($localPath, false, $ctx));
                } else {
                    $fileSystem->writeStream($targetPath, fopen($localPath, 'rb'));
                    $isUnlink && file_exists($localPath) && unlink($localPath);
                }
            } catch (\Throwable $e) {
                dump($e->getMessage());
            }
        }
    }
}
