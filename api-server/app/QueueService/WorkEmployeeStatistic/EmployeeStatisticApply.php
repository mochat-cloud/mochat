<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\QueueService\WorkEmployeeStatistic;

use App\Contract\WorkEmployeeStatisticServiceInterface;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use MoChat\Framework\WeWork\WeWork;
use Psr\SimpleCache\CacheInterface;

/**
 * 同步成员统计
 * Class EmployeeStatisticApply.
 */
class EmployeeStatisticApply
{
    /**
     * @var WeWork
     */
    protected $client;

    /**
     * @var WorkEmployeeStatisticServiceInterface
     */
    protected $workEmployeeStatisticService;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @AsyncQueueMessage(pool="employee").
     */
    public function handle(): void
    {
        $this->client                       = make(WeWork::class);
        $this->logger                       = make(StdoutLoggerInterface::class);
        $this->cache                        = make(CacheInterface::class);
        $this->workEmployeeStatisticService = make(WorkEmployeeStatisticServiceInterface::class);
        //前一天
        $startTime   = strtotime(date('Y-m-d', strtotime('-1 day')) . ' 00:00:00');
        $endTime     = strtotime(date('Y-m-d', strtotime('-1 day')) . ' 23:59:59');
        $employeeIds = [];
        Db::table('work_employee')->select('id', 'wx_user_id', 'corp_id')->orderBy('id', 'desc')->chunk(100, function ($employee) use ($startTime, $endTime, $employeeIds) {
            $employeeStatistics = [];
            foreach ($employee as $ek => $ev) {
                $employeeCache = 'EMPLOYEE_STATISTICS_APPLY_' . $ev->corp_id . $ev->id . $startTime;
                if (! empty($this->cache->has($employeeCache))) {
                    continue;
                }
                $this->cache->set($employeeCache, $startTime, -1);
                $employeeStatisticsData = $this->client->provider('externalContact')->app()
                    ->external_contact_statistics->userBehavior([$ev->wx_user_id], (string) $startTime, (string) $endTime);
                if (! empty($employeeStatisticsData['errcode'])) {
                    continue;
                }
                foreach ($employeeStatisticsData['behavior_data'] as $bdk => $bdv) {
                    $employeeStatistics[$ev->id] = [
                        'corp_id'               => $ev->corp_id,
                        'employee_id'           => $ev->id,
                        'chat_cnt'              => $bdv['chat_cnt'],
                        'message_cnt'           => $bdv['message_cnt'],
                        'negative_feedback_cnt' => $bdv['negative_feedback_cnt'],
                        'new_apply_cnt'         => $bdv['new_apply_cnt'],
                        'reply_percentage'      => ! empty($bdv['reply_percentage']) ? $bdv['reply_percentage'] * 100 : 0,
                        'new_contact_cnt'       => $bdv['new_apply_cnt'],
                        'avg_reply_time'        => $bdv['avg_reply_time'],
                        'syn_time'              => date('Y-m-d', strtotime('-1 day')) . ' 00:00:00',
                        'created_at'            => date('Y-m-d H:i:s'),
                    ];
                }
            }
            //插入表
            $result = $this->workEmployeeStatisticService->createWorkEmployeeStatistics($employeeStatistics);
            if (! $result) {
                $employeeIds = empty($employeeIds) ? array_column($employee, 'id') : array_merge($employeeIds, array_column($employee, 'id'));
            }
        });
        $message = '成员统计拉取成功';
        if (! empty($employeeIds)) {
            $message = '成员统计拉取失败成员: ' . json_encode($employeeIds);
        }
        $this->logger->info($message . date('Y-m-d', strtotime('-1 day')));
    }
}
