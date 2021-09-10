<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactBatchAdd\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use League\Flysystem\Filesystem;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\ContactBatchAdd\Logic\ImportStoreLogic;

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
     * @Inject
     * @var ImportStoreLogic
     */
    protected $importStoreLogic;

    /**
     * @RequestMapping(path="/dashboard/contactBatchAdd/importStore", methods="post")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return array 返回数组
     */
    public function handle(Filesystem $filesystem): array
    {
        //接收参数
        $params['tags'] = $this->request->input('tags', []);
        $params['title'] = $this->request->input('title', '');
        $params['allotEmployee'] = $this->request->input('allotEmployee', []);
        $params['file'] = $this->request->file('file');
        $params['tags'] = json_decode($params['tags'], true, 512, JSON_THROW_ON_ERROR);
        $params['allotEmployee'] = json_decode($params['allotEmployee'], true, 512, JSON_THROW_ON_ERROR);
        //验证参数
        $this->validated($params);

        $user = user();
        $file = $this->request->file('file');
        $contact = $this->validationExcelByFile($file->toArray()['tmp_file']); ## 获取excel里的客户信息

        if ($file->getMimeType() !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '文件类型错误');
        }
        $ext = strrchr($file->getClientFilename(), '.'); //文件扩展名
        $fileName = md5(openssl_random_pseudo_bytes(64)) . $ext;
        try {
            $stream = fopen($file->getRealPath(), 'r+');
            $filesystem->writeStream('image/contact_batch_add/' . $fileName, $stream);
        } catch (\Exception $e) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '上传失败:' . $e->getMessage());
        }

        $params['fileName'] = $fileName;
        $params['fileUrl'] = file_full_url($fileName);
        $result = $this->importStoreLogic->handle($params, $contact, $user);
        return [
            'successNum' => $result['number'],
        ];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'tags' => 'array',
            'title' => 'required',
            'allotEmployee' => 'required|array|min:1',
            'file' => 'required|file',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * 验证Excel格式.
     * @param string $path
     */
    protected function validationExcelByFile(string $filePath): array
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();
        if (count($data) <= 1) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '表格内容错误:至少拥有一个客户信息');
        }
        array_shift($data); ## 去掉表头
        foreach ($data as $key => $item) {
            $phoneLen = mb_strlen((string) $item[0]);
            $remarkLen = isset($item[1]) ? mb_strlen((string) $item[1]) : 0;
            if ($phoneLen < 5 || $phoneLen > 255) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '表格内容错误:客户号码错误，行数' . ($key + 2));
            }
            if ($remarkLen > 255) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '表格内容错误:客户备注应小于255个，行数' . ($key + 2));
            }
        }
        return $data;
    }
}
