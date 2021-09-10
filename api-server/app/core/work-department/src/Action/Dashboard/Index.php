<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkDepartment\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\WorkDepartment\Contract\WorkDepartmentContract;
use MoChat\App\WorkEmployee\Constants\Status;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 获取全部部门、成员.
 *
 * Class Index
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkDepartmentContract
     */
    private $departmentService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    private $employeeService;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/workDepartment/index", methods="GET")
     * @return array 响应数据
     */
    public function handle()
    {
        //接收参数
        $params['searchKeyWords'] = $this->request->input('searchKeyWords');

        $corpId = user()['corpIds'];
        if (count($corpId) > 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请先选择企业');
        }

        //验证参数
        $this->validated($params);

        //部门信息
        $data['department'] = $this->getDepartment($params);
        //成员信息
        $data['employee'] = $this->getEmployee($params);

        return $data;
    }

    /**
     * 获取部门信息.
     * @param $params
     * @return array
     */
    private function getDepartment($params)
    {
        if (! empty($params['searchKeyWords'])) {
            //部门信息
            $department = $this->departmentService->getWorkDepartmentsByCorpIdName([user()['corpIds'][0]], $params['searchKeyWords']);
        } else {
            //部门信息
            $department = $this->departmentService->getWorkDepartmentsByCorpId(user()['corpIds'][0]);
        }

        //处理子部门
        $items = [];
        foreach ($department as $v) {
            $items[$v['id']] = $v;
        }
        $tree = [];
        foreach ($items as $k => $item) {
            if (isset($items[$item['parentId']])) {
                $items[$item['parentId']]['son'][] = &$items[$k];
            } else {
                $tree[] = &$items[$k];
            }
        }

        unset($items);

        return $tree;
    }

    /**
     * 获取成员信息.
     * @param $params
     * @return array
     */
    private function getEmployee($params)
    {
        $columns = [
            'id',
            'name',
            'wx_user_id',
            'avatar',
        ];

        if (! empty($params['searchKeyWords'])) {
            $employee = $this->employeeService->getWorkEmployeesByCorpIdNameStatus(
                (int) user()['corpIds'][0],
                $params['searchKeyWords'],
                (int) Status::ACTIVE,
                $columns
            );
        } else {
            $employee = $this->employeeService->getWorkEmployeesByCorpIdStatus(user()['corpIds'][0], (int) Status::ACTIVE, $columns);
        }

        array_walk($employee, function (&$item) {
            $item['employeeId'] = $item['id'];
            $item['avatar'] = file_full_url($item['avatar']);
        });

        return $employee;
    }
}
