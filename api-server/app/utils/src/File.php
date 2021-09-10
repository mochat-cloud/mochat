<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Utils;

use League\Flysystem\Filesystem;

class File
{
    /**
     * [将Base64图片转换为本地图片并保存].
     * @param $base64ImageContent
     * @param $pathFileName
     * @throws \League\Flysystem\FileExistsException
     */
    public static function uploadBase64Image($base64ImageContent, $pathFileName): string
    {
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64ImageContent, $result)) {
            $filesystem = make(Filesystem::class);
            try {
                ## 文件上传
                $filesystem->write($pathFileName, base64_decode(str_replace($result[1], '', $base64ImageContent)));
            } catch (\Exception $e) {
                static::logger()->error(sprintf('%s [%s] %s', '图片上传失败', date('Y-m-d H:i:s'), $e->getMessage()));
                static::logger()->error($e->getTraceAsString());
            }
        }
        return $pathFileName;
    }

    /**
     * url图片上传.
     * @param $url
     * @param $pathFileName
     */
    public static function uploadUrlImage($url, $pathFileName): string
    {
        $filesystem = make(Filesystem::class);
        $stream = file_get_contents($url, true);
        try {
            $filesystem->write($pathFileName, $stream);
        } catch (\Exception $e) {
            static::logger()->error(sprintf('%s [%s] %s', '图片上传失败', date('Y-m-d H:i:s'), $e->getMessage()));
            static::logger()->error($e->getTraceAsString());
        }
        return $pathFileName;
    }

    /**
     * 下载远程文件.
     *
     * @return string
     */
    public static function download(string $url, string $localPath)
    {
        $fileSystem = make(\Hyperf\Filesystem\FilesystemFactory::class)->get('local');
        $fileSystem->write($localPath, file_get_contents($url));
        $root = config('file.storage.local.root');
        return realpath(rtrim($root, '/') . '/' . $localPath);
    }

    /**
     * 生成完整的文件名.
     *
     * @return string
     */
    public static function generateFullFilename(string $extension, string $path = '')
    {
        if (empty($path)) {
            $path = date('Y/md/Hi');
        }

        $filename = strval(microtime(true) * 10000) . uniqid() . '.' . $extension;
        $pathFileName = $path . '/' . $filename;
        return ltrim($pathFileName, '/');
    }

    protected static function logger(): \Psr\Log\LoggerInterface
    {
        $loggerFactory = \Hyperf\Utils\ApplicationContext::getContainer()->get(\Hyperf\Logger\LoggerFactory::class);
        return $loggerFactory->get('file');
    }
}
