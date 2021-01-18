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

/**
 * 查询 - 列表.
 * @Controller
 */
class Index extends AbstractAction
{
    use UserTrait;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/mediumGroup/index", methods="GET")
     */
    public function handle(): array
    {
        ## 企业ID
        $corpId = $this->corpId();

        $client = $this->container->get(MediumGroupServiceInterface::class);
        $data   = $client->getMediumGroupsByCorpId($corpId, ['id', 'name']);

        array_unshift($data, ['id' => 0, 'name' => '未分组']);
        return $data;
    }
}
