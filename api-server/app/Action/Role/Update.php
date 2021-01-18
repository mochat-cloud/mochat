<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Role;

use App\Contract\CorpServiceInterface;
use App\Logic\Role\UpdateLogic;
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
     * @var UpdateLogic
     */
    private $updateLogic;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/role/update", methods="put")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 获取当前登录用户
        $user = user();

        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 参数验证
        $params = $this->request->all();
        $this->validated($this->request->all(), 'update');

        return $this->updateLogic->handle($user, $params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'roleId'         => 'required | integer | bail',
            'name'           => 'required | string | bail',
            'remarks'        => 'required | string | bail',
            'dataPermission' => 'required | integer | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'roleId.required'         => '角色id 必填',
            'roleId.integer'          => '角色id 必须为整型',
            'name.required'           => '角色名称 必填',
            'name.string'             => '角色名称 必须为字符串',
            'remarks.required'        => '角色描述 必填',
            'remarks.string'          => '角色描述 必须为文本',
            'dataPermission.required' => '数据权限 必填',
            'dataPermission.string'   => '数据权限 值必须在列表内：[1,2]',
        ];
    }
}
