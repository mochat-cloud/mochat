<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Statistic\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\Statistic\Logic\EmployeesLogic;

/**
 * 查询 - 列表.
 * @Controller
 */
class EmployeeCounts extends AbstractAction
{
    use AppTrait;

    /**
     * @var EmployeesLogic
     */
    protected $employeesLogic;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    public function __construct(EmployeesLogic $employeesLogic, RequestInterface $request)
    {
        $this->employeesLogic = $employeesLogic;
        $this->request = $request;
    }

    /**
     * 按员工查看 有分页.
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/statistic/employeeCounts", methods="GET")
     */
    public function handle(): array
    {
        $params['startTime'] = $this->request->input('startTime');
        $params['endTime'] = $this->request->input('endTime');
        $params['page'] = $this->request->input('page');

        $params['startTime'] = strtotime($params['startTime']);
        $params['endTime'] = strtotime($params['endTime']);

        if ($params['endTime'] - $params['startTime'] > 2592000) {
            //大于30天 不行
            throw new CommonException(ErrorCode::INVALID_PARAMS, '日期范围不能超过30天');
        }
        $user = user();
        $employeeList = $this->employeesLogic->getEmployees($user['corpIds'][0]);
        //总人数
        $employeeTotal = count($employeeList);

        $pageSize = 5;
        $indexStart = ((int) $params['page'] - 1) * $pageSize;
        $employeeList = array_slice($employeeList, $indexStart, $pageSize);
        ##EasyWeChat获取「联系客户统计」数据
        $wx = $this->wxApp($user['corpIds'][0], 'contact')->external_contact_statistics;
        $table = [];
        foreach ($employeeList as $employee) {
            $res = $wx->userBehavior([$employee['wxUserId']], (string) $params['startTime'], (string) $params['endTime']);
            if ($res['errcode'] !== 0) {
                $this->logger->error(sprintf('获取「联系客户统计」数据 失败::[%s]', json_encode($res, JSON_THROW_ON_ERROR)));
            }
            $temp = $res['behavior_data'];
            $one = [
                'id' => $employee['id'],
                'name' => $employee['name'],
                'avatar' => file_full_url($employee['avatar']),
                'chat_cnt' => 0,
                'message_cnt' => 0,
                'reply_percentage' => 0,
                'avg_reply_time' => 0,
            ];
            //计算一个人的总和
            foreach ($temp as $item) {
                $one['chat_cnt'] += @$item['chat_cnt'] ? $item['chat_cnt'] : 0;
                $one['message_cnt'] += @$item['message_cnt'] ? $item['message_cnt'] : 0;
                $one['reply_percentage'] += @$item['reply_percentage'] ? $item['reply_percentage'] : 0;
                $one['avg_reply_time'] += @$item['avg_reply_time'] ? $item['avg_reply_time'] : 0;
            }

            $one['reply_percentage'] = round($one['reply_percentage'] / count($temp), 2);
            $one['avg_reply_time'] = round($one['avg_reply_time'] / count($temp), 2);

            $table[] = $one;
        }

        return [
            'total' => $employeeTotal,
            'table' => $table,
        ];
    }
}
