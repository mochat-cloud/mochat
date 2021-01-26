<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Corp;

use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Utils\ApplicationContext;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 企业微信授权- 登录用户绑定企业信息.
 *
 * Class Bind.
 * @Controller
 */
class Bind extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @RequestMapping(path="/corp/bind", methods="post")
     * @return array 返回数组
     */
    public function handle()
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 获取登录用户信息
        $user = user();
        ## 接收参数
        $corpId     = $this->request->input('corpId');
        $employeeId = 0;

        ## 验证当前用户是否归属绑定企业
        if (! $user['isSuperAdmin']) {
            ## 查询当前用户归属的公司
            $employees = $this->workEmployeeService->getWorkEmployeesByLogUserId((int) $user['id'], ['corp_id']);
            $corpIds   = array_column($employees, 'corpId');
            if (! in_array($corpId, $corpIds)) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '当前用户不归属该企业，不可操作');
            }
            ## 查询登录用户通讯录信息
            $employee   = $this->workEmployeeService->getWorkEmployeeByCorpIdLogUserId((int) $corpId, (int) $user['id'], ['id']);
            $employeeId = $employee['id'];
        }

        ## 存入缓存(key:mc:user.userId   value:corpId-workEmployeeId
        $container = ApplicationContext::getContainer();
        $redis     = $container->get(\Hyperf\Redis\Redis::class);
        $redis->set('mc:user.' . $user['id'], $corpId . '-' . $employeeId);

        return $data = [];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'corpId' => 'required | integer| min:0 | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'corpId.required' => '企业授信ID 必填',
            'corpId.integer'  => '企业授信ID 必需为整数',
            'corpId.min'      => '企业授信ID 不可小于1',
        ];
    }
}
