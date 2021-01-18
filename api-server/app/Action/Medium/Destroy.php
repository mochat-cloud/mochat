<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Medium;

use App\Action\ContactField\Traits\RequestTrait;
use App\Contract\MediumServiceInterface;
use App\Middleware\PermissionMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 删除 - 动作.
 * @Controller
 */
class Destroy extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/medium/destroy", methods="DELETE")
     */
    public function handle(): array
    {
        $id = $this->request->post('id');
        $this->validated(['id' => $id], 'destroy');

        $client = $this->container->get(MediumServiceInterface::class);
        $res    = $client->deleteMedium($id);
        if (! $res) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '删除失败');
        }

        return [];
    }
}
