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

use App\Action\Medium\Traits\RequestTrait;
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
 * 分组修改 - 动作.
 * @Controller
 */
class GroupUpdate extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/medium/groupUpdate", methods="PUT")
     */
    public function handle(): array
    {
        $id      = $this->request->input('id');
        $groupId = $this->request->input('mediumGroupId');

        $this->validated(['id' => $id, 'mediumGroupId' => $groupId], 'groupUpdate');

        $client = $this->container->get(MediumServiceInterface::class);
        $res    = $client->updateMediumById($id, ['mediumGroupId' => $groupId]);
        if (! $res) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '移动失败');
        }

        return [];
    }
}
