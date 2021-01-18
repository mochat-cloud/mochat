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
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 修改 - 页面.
 * @Controller
 */
class Update extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;
    use UpdateTrait;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/contactField/update", methods="PUT")
     */
    public function handle(): array
    {
        ## 请求参数
        $params = $this->request->inputs(
            ['id', 'label', 'type', 'options', 'order', 'status'],
            ['options' => [], 'order' => 0]
        );

        ## 类型验证
        $this->validated($params, 'update');

        ## 数据处理
        $params['order'] = (int) $params['order'];
        $this->client    = $this->container->get(ContactFieldServiceInterface::class);
        ## 业务验证
        $data = $this->client->getContactFieldById($params['id'], ['id', 'label', 'type', 'options', 'is_sys', 'name']);
        if (empty($data)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '数据不存在');
        }
        $dbData = $this->handleUpdateParam($params, $data);

        try {
            $this->client->updateContactFieldById($params['id'], $dbData);
        } catch (\Exception $e) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '修改失败');
        }

        return [];
    }
}
