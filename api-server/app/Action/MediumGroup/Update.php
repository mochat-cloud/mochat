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
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/mediumGroup/update", methods="PUT")
     */
    public function handle(): array
    {
        ## 数据验证
        $corpId = $this->corpId();
        $params = $this->request->inputs(['id', 'name']);
        $this->validated($params);

        $client    = $this->container->get(MediumGroupServiceInterface::class);
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
            'id'    => 'ID',
            'label' => '分组名称',
        ];
    }

    /**
     * 验证规则.
     */
    protected function rules(): array
    {
        return [
            'id'   => 'required|integer',
            'name' => 'required',
        ];
    }
}
