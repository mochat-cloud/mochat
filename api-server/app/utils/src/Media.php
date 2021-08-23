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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

class Media
{
    use AppTrait;

    /**
     * @Inject()
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @Inject()
     * @var \League\Flysystem\Filesystem
     */
    protected $filesystem;

    /**
     * 上传临时图片媒体素材
     *
     * @param int|string $corpId 企业id
     * @param string $path 上传路径
     *
     * @return string
     * @throws \Throwable
     */
    public function uploadImage($corpId, string $path): string
    {
        return $this->upload($corpId, 'image', $path);
    }

    /**
     * 上传临时语音媒体素材
     *
     * @param int|string $corpId 企业id
     * @param string $path 上传路径
     *
     * @return string
     * @throws \Throwable
     */
    public function uploadVoice($corpId, string $path): string
    {
        return $this->upload($corpId, 'voice', $path);
    }

    /**
     * 上传临时视频媒体素材
     *
     * @param int|string $corpId 企业id
     * @param string $path 上传路径
     *
     * @return string
     * @throws \Throwable
     */
    public function uploadVideo($corpId, string $path): string
    {
        return $this->upload($corpId, 'video', $path);
    }

    /**
     * 上传临时文件媒体素材
     *
     * @param int|string $corpId 企业id
     * @param string $path 上传路径
     *
     * @return string
     * @throws \Throwable
     */
    public function uploadFile($corpId, string $path): string
    {
        return $this->upload($corpId, 'file', $path);
    }

    /**
     * 上传临时媒体素材
     *
     * @param int|string $corpId 企业id
     * @param string $type 上传类型
     * @param string $path 上传路径
     *
     * @return string
     * @throws \Throwable
     */
    public function upload($corpId, string $type, string $path): string
    {
        try {
            $mediaService = $this->wxApp($corpId, 'user')->media;
            $fileContent = $this->filesystem->read($path);
            $tempFile = tempnam(sys_get_temp_dir(), 'Media');
            file_put_contents($tempFile, $fileContent, FILE_USE_INCLUDE_PATH);
            $wxMediaRes = $mediaService->upload($type, $tempFile);
            if ($wxMediaRes['errcode'] != 0) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, sprintf('请求数据：%s 响应结果：%s', $path, json_encode($wxMediaRes)));
            }
            return $wxMediaRes['media_id'];
        } catch (\Throwable $e) {
            ## 记录错误日志
            $this->logger->error(sprintf('%s [%s] %s', '请求微信上传临时素材失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw $e;
        } finally {
            isset($tempFile) && file_exists($tempFile) && unlink($tempFile);
        }
    }
}