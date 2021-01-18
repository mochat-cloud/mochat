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
use App\Logic\User\Traits\UserTrait;
use App\Middleware\PermissionMiddleware;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 添加 - 动作.
 * @Controller
 */
class Store extends AbstractAction
{
    use UserTrait;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/mediumGroup/store", methods="POST")
     */
    public function handle(): array
    {
        ## 数据验证
        $corpId = $this->corpId();
        $name   = $this->request->post('name', false);
        if (! $name) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请输入分组名称');
        }
        $client    = $this->container->get(MediumGroupServiceInterface::class);
        $existData = $client->getMediumGroupByName($name, ['id']);
        if (! empty($existData)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '分组名称已存在');
        }

        $insertId = $client->createMediumGroup(['name' => $name, 'corp_id' => $corpId]);
        if (! $insertId) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '添加失败');
        }

        return ['id' => $insertId];
    }
}
