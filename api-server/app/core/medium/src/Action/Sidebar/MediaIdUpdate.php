<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Medium\Action\Sidebar;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\SidebarAuthMiddleware;
use MoChat\App\Medium\Action\Dashboard\Traits\RequestTrait;
use MoChat\App\Medium\Constants\Type;
use MoChat\App\Medium\Contract\MediumContract;
use MoChat\App\Utils\Media;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 查询 - 列表.
 * @Controller
 */
class MediaIdUpdate extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

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
     * @var MediumContract
     */
    private $mediumService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @Middlewares({
     *     @Middleware(SidebarAuthMiddleware::class)
     * })
     * @RequestMapping(path="/sidebar/medium/mediaIdUpdate", methods="GET")
     */
    public function handle(): array
    {
        ## 参数验证
        $mediumId = (int) $this->request->input('mediumId');
        $medium = $this->mediumService->getMediumById($mediumId, ['id', 'media_id', 'last_upload_time', 'type', 'content']);

        if (time() - $medium['lastUploadTime'] <= 60 * 60 * 24 * 3 - 60 * 60 * 2) {
            return [
                'mediaId' => $medium['mediaId'],
            ];
        }

        $uploadFile = json_decode($medium['content'], true);
        $mediaType = self::wxMediaType($medium['type']);
        $path = isset($uploadFile[$mediaType . 'Path']) ? $uploadFile[$mediaType . 'Path'] : '';
        $filename = isset($uploadFile[$mediaType . 'Name']) ? $uploadFile[$mediaType . 'Name'] : '';
        if (empty($path)) {
            return [
                'mediaId' => $medium['mediaId'],
            ];
        }

        if (! $this->filesystem->fileExists($path)) {
            return [
                'mediaId' => $medium['mediaId'],
            ];
        }

        try {
            // TODO 语音转换amr
            $corpId = user()['corpId'];
            $mediaId = $this->media->upload($corpId, $mediaType, $path, $filename);
            $dbData = [
                'id' => $medium['id'],
                'media_id' => $mediaId,
                'last_upload_time' => time(),
            ];
            $this->mediumService->updateMediumById($medium['id'], $dbData);
            return [
                'mediaId' => $mediaId,
            ];
        } catch (\Throwable $e) {
            ## 记录错误日志
            $this->logger->error(sprintf('%s [%s] %s', '定时同步临时媒体文件失败', date('Y-m-d H:i:s'), $e->getMessage()));
        }

        return [
            'mediaId' => $medium['media_id'],
        ];
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
