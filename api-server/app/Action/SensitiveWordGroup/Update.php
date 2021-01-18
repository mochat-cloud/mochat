<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\SensitiveWordGroup;

use App\Contract\CorpServiceInterface;
use App\Contract\SensitiveWordGroupServiceInterface;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 敏感词词库- 更新提交.
 *
 * Class Update.
 * @Controller
 */
class Update extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @Inject
     * @var SensitiveWordGroupServiceInterface
     */
    protected $sensitiveWordGroupService;

    /**
     * 企业ID.
     * @var int
     */
    protected $corpId;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/sensitiveWordGroup/update", methods="put")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        $id   = (int) $this->request->input('groupId');
        $name = $this->request->input('name');

        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }
        $this->corpId = $user['corpIds'][0];

        ## 检测分组是否重复
        $this->nameIsUnique($name, $id);
        ## 数据入表
        $res = $this->sensitiveWordGroupService->updateSensitiveWordGroupById($id, ['name' => $name]);
        if (! $res) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '分组更新失败');
        }
        return [];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'name'    => 'required | string | min:1 | bail',
            'groupId' => 'required | integer | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'name.required'    => '敏感词分组名称 必填',
            'name.string'      => '敏感词分组名称 必须为字符串',
            'name.min'         => '敏感词分组名称 字符串长度不可小于1',
            'groupId.required' => '敏感词分组id 必填',
            'groupId.integer'  => '敏感词分组id 必须为整型',
        ];
    }

    /**
     * 验证分组名称是否存在.
     * @param string $name 分组名称
     * @param int $id 分组id
     * @return bool
     */
    private function nameIsUnique(string $name, int $id)
    {
        $client    = $this->container->get(SensitiveWordGroupServiceInterface::class);
        $existData = $client->getSensitiveWordGroupByNameCorpId($name, (int) $this->corpId, ['id']);
        if (empty($existData)) {
            return true;
        }
        $existData = array_column($existData, null, 'id');

        unset($existData[$id]);

        if (! empty($existData)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, $name . '-该分组名称已存在');
        }
        return true;
    }
}
