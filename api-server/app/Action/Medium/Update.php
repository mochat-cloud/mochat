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
 * 修改 - 页面.
 * @Controller
 */
class Update extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/medium/update", methods="PUT")
     */
    public function handle(): array
    {
        ## 请求参数
        $params = $this->request->inputs(
            ['id', 'type', 'isSync', 'content', 'mediumGroupId'],
            ['isSync' => 1]
        );

        ## 验证
        $this->validated($params, 'update');

        ## 数据整理
        $userInfo = user();
        $params   = array_merge($params, [
            'user_id'   => $userInfo['id'],
            'user_name' => $userInfo['name'],
            'content'   => json_encode($params['content'], JSON_UNESCAPED_UNICODE),
        ]);

        ## 修改
        $id = $params['id'];
        unset($params['id']);
        try {
            $this->container->get(MediumServiceInterface::class)->updateMediumById($id, $params);
        } catch (\Exception $e) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '修改失败');
        }
        return [];
    }
}
