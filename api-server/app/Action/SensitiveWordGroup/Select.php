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

use App\Contract\SensitiveWordGroupServiceInterface;
use App\Logic\User\Traits\UserTrait;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 敏感词词库-分组列表.
 *
 * Class Select.
 * @Controller
 */
class Select extends AbstractAction
{
    //use ValidateSceneTrait;
    //use UserTrait;

    /**
     * @Inject
     * @var SensitiveWordGroupServiceInterface
     */
    protected $sensitiveWordGroupService;

    /**
     * @RequestMapping(path="/sensitiveWordGroup/select", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 判断用户绑定企业信息
        $user = user();
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }
        $corpId = (int) $user['corpIds'][0];

//        if (empty($user['dataPermission'])) {
//            ## 企业敏感词
//            $sensitiveWordRes = $this->sensitiveWordGroupService->getSensitiveWordGroupsByCorpId($corpId, ['id', 'name']);
//        } else {
//            ## 员工敏感词
//            $sensitiveWordRes = $this->sensitiveWordGroupService->getSensitiveWordGroupsByEmployeeId($user['deptEmployeeIds'], ['id', 'name']);
//        }

        ## 企业敏感词
        $sensitiveWordRes = $this->sensitiveWordGroupService->getSensitiveWordGroupsByCorpId($corpId, ['id', 'name']);

        ## 返回数据处理
        if (empty($sensitiveWordRes)) {
            return [];
        }
        array_walk($sensitiveWordRes, function (&$v) {
            $v['groupId'] = $v['id'];
            unset($v['id']);
        });
        //array_unshift($sensitiveWordRes, ['name' => '全部', 'groupId' => 0]);

        return $sensitiveWordRes;
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [];
    }
}
