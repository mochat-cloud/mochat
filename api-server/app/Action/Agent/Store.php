<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Agent;

use App\Action\Corp\Traits\RequestTrait;
use App\Contract\WorkAgentServiceInterface;
use App\Logic\User\Traits\UserTrait;
use App\Middleware\PermissionMiddleware;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use App\Logic\Agent\StoreLogic;

/**
 * 企业应用 - 创建提交.
 * @author wangpeiyan732
 *
 * Class Store.
 * @Controller
 */
class Store extends AbstractAction
{
    use ValidateSceneTrait;
    //use RequestTrait;
    use UserTrait;

    use ValidateSceneTrait;

    /**
     * @Inject
     * @var StoreLogic
     */
    protected $storeLogic;

    /**
     *
     * @RequestMapping(path="/agent/store", methods="post")
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }
        ## 参数验证
        $params['wxAgentId']      = $this->request->input('wxAgentId');
        $params['wxSecret']       = $this->request->input('wxSecret');
        $params['type']           = $this->request->input('type');
        $this->validated($params);

        return $this->storeLogic->handle($params, $user);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'wxAgentId'    => 'required | string | bail',
            'wxSecret'     => 'required | string | bail',
            'type'         => 'required | integer | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'wxAgentId.required'    => '企业应用ID 必填',
            'wxAgentId.string'      => '企业应用ID 必须为字符串',
            'wxSecret.required'     => '企业应用secret 必填',
            'wxSecret.string'       => '企业应用secret 必须为字符串',
            'type.required'         => '企业应用类型 必填',
            'type.integer'          => '企业应用类型 必须为整型',
        ];
    }


}
