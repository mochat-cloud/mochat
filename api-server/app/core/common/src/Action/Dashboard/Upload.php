<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Common\Action\Dashboard;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use League\Flysystem\Filesystem;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Utils\File;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 文件上传.
 * @Controller
 */
class Upload extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/common/upload", methods="POST")
     * @param Filesystem $filesystem ..
     * @return array ...
     */
    public function handle(Filesystem $filesystem): array
    {
        $file = $this->request->file('file');

        // 验证
        $this->validated(['file' => $file]);
        $originalFilename = $this->request->post('name', false) ?: $file->getClientFilename();

        // 不再支持自定义path
        $extension = $file->getExtension();
        $pathFileName = File::generateFullFilename($extension);

        try {
            $stream = fopen($file->getRealPath(), 'r+');
            $filesystem->writeStream($pathFileName, $stream);
        } catch (\Exception $e) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '上传失败:' . $e->getMessage());
        }

        return [
            'name' => $originalFilename,
            'type' => $file->getMimeType(),
            'path' => $pathFileName,
            'fullPath' => file_full_url($pathFileName),
        ];
    }

    /**
     * 属性替换.
     * @return array|string[] ...
     */
    public function attributes(): array
    {
        return [
            'file' => '文件',
        ];
    }

    /**
     * 验证规则.
     */
    protected function rules(): array
    {
        $allowMimes = 'jpg,png,jpeg,svg,gif,psd,bmp,mp4,avi,3gp,flv,mp3,amr,mp4,wav,ogg,mov,rmvb,wma,mkv,doc,docx,xls';
        $allowMimes .= ',xlsx,csv,ppt,pptx,txt,pdf,Xmind';
        return [
            'file' => 'required|mimes:' . $allowMimes,
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    protected function messages(): array
    {
        return [
            'file.mimetypes' => '文件类型不合法',
        ];
    }
}
