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

use App\Contract\CorpServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 企业微信授权-企业下拉列表.
 *
 * Class Select.
 * @Controller
 */
class Select extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @RequestMapping(path="/corp/select", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 获取当前登录用户
        $user = user();
        ## 接收参数
        $corpName = $this->request->input('corpName', '');

        ## 超级管理员
        if ($user['isSuperAdmin']) {
            $tenantCorps = $this->corpService->getCorpsByTenantId($user['tenantId'], ['id']);
            $corpIds     = array_column($tenantCorps, 'id');
        } else {
            ## 获取当前登录用户所归属的所有企业通讯录信息
            $employeeList = $this->getEmployeeList((int) $user['id']);
            $corpIds      = array_column($employeeList, 'corpId');
        }

        ## 企业详情
        $res = $this->corpService->getCorpsByIdName($corpIds, $corpName, ['id', 'name']);

        ## 组织响应数据
        $data = [];
        if (! empty($res)) {
            foreach ($res as $v) {
                $data[] = [
                    'corpId'   => $v['id'],
                    'corpName' => $v['name'],
                ];
            }
        }
        return $data;
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

    /**
     * @param int $userId 用户表ID
     * @return array 响应数组
     */
    private function getEmployeeList(int $userId): array
    {
        $data = $this->workEmployeeService->getWorkEmployeeByLogUserId($userId, ['corp_id']);

        return $data ?? [];
    }
}
