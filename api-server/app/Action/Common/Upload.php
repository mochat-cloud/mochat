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

/**
 * 文件上传.
 * @Controller
 */
class Upload extends AbstractAction
{
    /**
     * @RequestMapping(path="/common/upload", methods="POST")
     * @param Filesystem $filesystem ..
     * @return array ...
     */
    public function handle(Filesystem $filesystem): array
    {
        $file     = $this->request->file('file');
        $path     = $this->request->post('path', date('Y/m/d/His'));
        $fileName = $this->request->post('name', false) ?: $file->getClientFilename();

        $pathFileName = $path . '/' . $fileName;
        try {
            $filesystem->write($pathFileName, $file->getStream());
//            $filesystem->setVisibility($pathFileName, 'public');
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
}
