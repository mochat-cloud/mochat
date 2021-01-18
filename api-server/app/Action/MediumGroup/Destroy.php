<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\MediumGroup;

use App\Contract\MediumGroupServiceInterface;
use App\Contract\MediumServiceInterface;
use App\Middleware\PermissionMiddleware;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 删除 - 动作.
 * @Controller
 */
class Destroy extends AbstractAction
{
    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/mediumGroup/destroy", methods="DELETE")
     */
    public function handle(): array
    {
        $id = $this->request->post('id', false);
        if (! $id) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '唯一标识ID必须');
        }
        $groupClient  = $this->container->get(MediumGroupServiceInterface::class);
        $mediumClient = $this->container->get(MediumServiceInterface::class);

        Db::beginTransaction();
        try {
            $groupClient->deleteMediumGroup($id);
            $mediumClient->updateMediaByGroupId($id, ['medium_group_id' => 0]);

            Db::commit();
        } catch (\Throwable $ex) {
            Db::rollBack();
            throw new CommonException(ErrorCode::SERVER_ERROR, '删除失败');
        }
        return [];
    }
}
