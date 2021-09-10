<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Statistic\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\User\Contract\UserContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;

/**
 * Class IndexLogic.
 */
class IndexLogic
{
    /**
     * @Inject
     * @var UserContract
     */
    protected $userService;

    /**
     * @Inject
     * @var WorkContactEmployeeContract
     */
    protected $workContactEmployeeService;

    public function todayData(): array
    {
        $user = user();

        $todayDate = date('Y-m-d', time());
        $tomorrowDate = date('Y-m-d', time() + 86400);

        //企业的客户总数
        $res['total'] = $this->workContactEmployeeService->countWorkContactEmployeesByCorpId($user['corpIds'][0], [1]);
        //企业新增的客户总数
        $res['add'] = $this->workContactEmployeeService->countWorkContactEmployeesByCorpIdTime($user['corpIds'][0], $todayDate, $tomorrowDate);
        //企业流失的客户总数
        $res['loss'] = $this->workContactEmployeeService->countLossWorkContactEmployeesByCorpIdTime($user['corpIds'][0], $todayDate, $tomorrowDate);

        //企业净增的客户总数
        $res['net'] = $res['add'] - $res['loss'];

        return $res;
    }

    /**
     * @param array $params
     */
    public function anyTimeOrEmployeesData($params): array
    {
        $user = user();

        $start = strtotime($params['startTime']);
        $end = strtotime($params['endTime']);

        $res = [];
        if ($params['employeeId']) {
            //统计员工
            for ($i = $start; $i <= $end; $i += 86400) {
                $todayText = date('Y/m/d', $i);
                $tomorrowText = date('Y/m/d', $i + 86400);

                $res[$todayText]['total'] = $this->workContactEmployeeService->countWorkContactEmployeesByEmployeeTime($params['employeeId'], $tomorrowText);
                $res[$todayText]['add'] = $this->workContactEmployeeService->countWorkContactEmployeesByEmployeeIdTime($params['employeeId'], $todayText, $tomorrowText);
                $res[$todayText]['loss'] = $this->workContactEmployeeService->countWorkContactEmployeesLossByEmployeeIdTime($params['employeeId'], $todayText, $tomorrowText);
                $res[$todayText]['net'] = $res[$todayText]['add'] - $res[$todayText]['loss'];
            }
        } else {
            //统计企业
            for ($i = $start; $i <= $end; $i += 86400) {
                $todayText = date('Y/m/d', $i);
                $tomorrowText = date('Y/m/d', $i + 86400);

                $res[$todayText]['total'] = $this->workContactEmployeeService->countWorkContactEmployeesByTime($user['corpIds'][0], [1], $tomorrowText);
                $res[$todayText]['add'] = $this->workContactEmployeeService->countWorkContactEmployeesByCorpIdTime($user['corpIds'][0], $todayText, $tomorrowText);
                $res[$todayText]['loss'] = $this->workContactEmployeeService->countLossWorkContactEmployeesByCorpIdTime($user['corpIds'][0], $todayText, $tomorrowText);
                $res[$todayText]['net'] = $res[$todayText]['add'] - $res[$todayText]['loss'];
            }
        }

        return $res;
    }
}
