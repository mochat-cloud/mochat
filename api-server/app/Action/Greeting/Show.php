<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Greeting;

use App\Contract\GreetingServiceInterface;
use App\Contract\MediumServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
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
 * 欢迎语-详情.
 *
 * Class Show.
 * @Controller
 */
class Show extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var GreetingServiceInterface
     */
    private $greetingService;

    /**
     * @Inject
     * @var MediumServiceInterface
     */
    private $mediumService;

    /**
     * @RequestMapping(path="/greeting/show", methods="get")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 获取当前登录用户
        $user = user();
        ## 接收参数
        $greetingId = $this->request->input('greetingId');
        ## 获取欢迎语详情
        $greeting = $this->greetingService->getGreetingById((int) $greetingId);
        if (empty($greeting)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '此欢迎语不存在');
        }
        ## 处理数据
        return [
            'greetingId'    => $greeting['id'],
            'rangeType'     => $greeting['rangeType'],
            'employees'     => empty(json_decode($greeting['employees'], true)) ? [] : $this->getEmployees(json_decode($greeting['employees'], true)),
            'words'         => $greeting['words'],
            'mediumId'      => $greeting['mediumId'],
            'mediumContent' => empty($greeting['mediumId']) ? [] : $this->getMediumContent((int) $greeting['mediumId']),
        ];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'greetingId' => 'required | integer | min:0 | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'greetingId.required' => '欢迎语ID 必填',
            'greetingId.integer'  => '欢迎语ID 必需为整数',
            'greetingId.min  '    => '欢迎语ID 不可小于1',
        ];
    }

    /**
     * @param array $employeeIdArr 员工通讯录ID数组
     * @return array 响应数组
     */
    private function getEmployees(array $employeeIdArr): array
    {
        $employeeList = $this->workEmployeeService->getWorkEmployeesById($employeeIdArr, ['id', 'name']);
        return empty($employeeList) ? [] : array_map(function ($employee) {
            return [
                'employeeId'   => $employee['id'],
                'employeeName' => $employee['name'],
            ];
        }, $employeeList);
    }

    /**
     * @param int $greetingId 欢迎语ID
     * @return array 响应数组
     */
    private function getMediumContent(int $greetingId): array
    {
        $mediumInfo = $this->mediumService->getMediumById($greetingId, ['id', 'type', 'content']);

        if (empty($mediumInfo)) {
            return [];
        }

        return $this->mediumService->addFullPath(json_decode($mediumInfo['content'], true), $mediumInfo['type']);
    }
}
