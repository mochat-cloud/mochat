<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Common;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use League\Flysystem\FileExistsException;
use League\Flysystem\Filesystem;
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
     * @RequestMapping(path="/common/upload", methods="POST")
     * @param Filesystem $filesystem ..
     * @return array ...
     */
    public function handle(Filesystem $filesystem): array
    {
        $file = $this->request->file('file');
        $path = $this->request->post('path', date('Y/m/d/His'));

        // 验证
        $this->validated(['file' => $file, 'path' => $path]);
        $fileName = $this->request->post('name', false) ?: $file->getClientFilename();
        if (preg_match('/[\x{4e00}-\x{9fa5}]+/u', $fileName) || strlen($fileName) > 32) {
            $fileName = md5($fileName);
        }

        $pathFileName = $path . '/' . $fileName;
        try {
            $filesystem->write($pathFileName, $file->getStream());
        } catch (FileExistsException $e) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '上传失败:已经存在此文件名的文件');
        } catch (\Exception $e) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '上传失败:' . $e->getMessage());
        }

        return [
            'name'     => $fileName,
            'type'     => $file->getMimeType(),
            'path'     => $pathFileName,
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
            'path' => '文件路径',
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
            'path' => [
                'not_regex:/\.\.\//',
            ],
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    protected function messages(): array
    {
        return [
            'file.mimetypes' => '状态必须为数字',
            'path.not_regex' => '文件路径非法',
        ];
    }
}
