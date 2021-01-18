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
use App\Constants\ContactField\Options;
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
 * 查询 - 详情.
 * @Controller
 */
class Show extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/contactField/show", methods="GET")
     */
    public function handle(): array
    {
        ## 请求参数
        $id = (int) $this->request->input('id');

        ## 验证
        $this->validated(['id' => $id], 'show');

        ## 契约模型
        $client = $this->container->get(ContactFieldServiceInterface::class);
        $data   = $client->getContactFieldById($id, ['id', 'name', 'label', 'type', 'options', 'status', 'order', 'is_sys']);
        if (empty($data)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '无此条信息');
        }
        $data['typeText'] = Options::getMessage($data['type']);

        return $data;
    }
}
