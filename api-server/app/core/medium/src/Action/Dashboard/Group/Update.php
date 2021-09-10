<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Medium\Action\Dashboard\Group;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Medium\Contract\MediumGroupContract;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\User\Logic\Traits\UserTrait;
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
    use UserTrait;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/mediumGroup/update", methods="PUT")
     */
    public function handle(): array
    {
        ## 数据验证
        $corpId = $this->corpId();
        $params = $this->request->inputs(['id', 'name']);
        $this->validated($params);

        $client = $this->container->get(MediumGroupContract::class);
        $existData = $client->existMediumGroupByName($params['name'], $params['id']);
        if (! empty($existData)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '分组名称已存在');
        }

        $res = $client->updateMediumGroupById($params['id'], ['name' => $params['name'], 'corp_id' => $corpId]);
        if (! $res) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '修改失败');
        }

        return [];
    }

    /**
     * 属性替换.
     * @return array|string[] ...
     */
    public function attributes(): array
    {
        return [
            'id' => 'ID',
            'label' => '分组名称',
        ];
    }

    /**
     * 验证规则.
     */
    protected function rules(): array
    {
        return [
            'id' => 'required|integer',
            'name' => 'required',
        ];
    }
}
