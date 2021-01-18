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

use App\Contract\MediumServiceInterface;
use App\Logic\User\Traits\UserTrait;
use App\Middleware\PermissionMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 查询 - 详情.
 * @Controller
 */
class Show extends AbstractAction
{
    use UserTrait;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/medium/show", methods="GET")
     */
    public function handle(): array
    {
        ## 参数验证
        $this->corpId();
        $id = (int) $this->request->query('id', false);
        if (! $id) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '唯一标识ID必须');
        }

        ## 模型查询
        $client = $this->container->get(MediumServiceInterface::class);
        $data   = $client->getMediumById($id, ['id', 'media_id', 'type', 'content', 'corp_id', 'medium_group_id', 'user_id', 'user_name']);
        if (empty($data)) {
            return [];
        }
        $data['content'] = $client->addFullPath(json_decode($data['content'], true), $data['type']);
        return $data;
    }
}
