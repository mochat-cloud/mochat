<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\ContactField;

use App\Action\ContactField\Traits\RequestTrait;
use App\Action\ContactField\Traits\UpdateTrait;
use App\Contract\ContactFieldServiceInterface;
use App\Middleware\PermissionMiddleware;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 批量修改.
 * @Controller
 */
class BatchUpdate extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;
    use UpdateTrait;

    /**
     * @var ContactFieldServiceInterface
     */
    private $client;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/contactField/batchUpdate", methods="PUT")
     */
    public function handle(): array
    {
        $this->client = $this->container->get(ContactFieldServiceInterface::class);

        ## 请求参数处理
        $editData = array_map(function ($param) {
            return $this->paramsHandle($param);
        }, $this->request->input('update'));
        $delData = $this->request->input('destroy', []);

        ## 模型操作
        Db::beginTransaction();
        try {
            $this->client->updateContactFieldsCaseIds($editData);
            empty($delData) || $this->client->deleteContactFields($delData);

            Db::commit();
        } catch (\Throwable $ex) {
            Db::rollBack();
            throw new CommonException(ErrorCode::SERVER_ERROR, '批量修改失败');
        }

        return [];
    }

    protected function paramsHandle(array $param)
    {
        ## 请求参数过滤
        $fields = ['id' => 0, 'label' => 0, 'type' => 0, 'options' => 0, 'order' => 0, 'status' => 0];
        $param  = array_filter($param, static function ($item) use ($fields) {
            return isset($fields[$item]);
        }, ARRAY_FILTER_USE_KEY);

        ## 类型验证
        $this->validated($param, 'update');

        ## 业务验证
        $param['order'] === '' && $param['order'] = 0;
        $data                                     = $this->client->getContactFieldById($param['id'], ['id', 'label', 'type', 'options', 'is_sys', 'name']);
        return $this->handleUpdateParam($param, $data);
    }
}
