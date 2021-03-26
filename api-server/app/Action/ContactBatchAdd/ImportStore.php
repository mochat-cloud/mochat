<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */

namespace App\Action\ContactBatchAdd;

use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use League\Flysystem\FileExistsException;
use League\Flysystem\Filesystem;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 导入客户-导入提交.
 *
 * Class ImportStore.
 * @Controller
 */
class ImportStore extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @RequestMapping(path="/contactBatchAdd/importStore", methods="post")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(Filesystem $filesystem): array
    {
        $file = $this->request->file('file');
        if ($file->getMimeType() !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '文件类型错误');
        }
        $ext = strrchr($file->getClientFilename(), '.'); //文件扩展名
        $fileName = md5(openssl_random_pseudo_bytes(64)) . $ext;
        try {
            $filesystem->write('contact_batch_add/' . $fileName, $file->getStream());
        } catch (FileExistsException $e) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '上传失败:已经存在此文件名的文件');
        } catch (\Exception $e) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '上传失败:' . $e->getMessage());
        }
        //file_full_url($fileName) 文件url
        // 解析
        return [];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [];
    }
}
