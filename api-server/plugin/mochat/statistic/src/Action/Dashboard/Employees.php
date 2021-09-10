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
class Employees extends AbstractAction
{
    use AppTrait;

    /**
     * @var EmployeesLogic
     */
    protected $employeesLogic;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(EmployeesLogic $employeesLogic, RequestInterface $request)
    {
        $this->employeesLogic = $employeesLogic;
        $this->request = $request;
    }

    /**
     * 联系客户数据.
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/statistic/employees", methods="GET")
     */
    public function handle(): array
    {
        $params['startTime'] = $this->request->input('startTime');
        $params['endTime'] = $this->request->input('endTime');

        $params['startTime'] = strtotime($params['startTime']);
        $params['endTime'] = strtotime($params['endTime']);

        if ($params['endTime'] - $params['startTime'] > 2592000) {
            //大于30天 不行
            throw new CommonException(ErrorCode::INVALID_PARAMS, '日期范围不能超过30天');
        }

        $user = user();
        $info = $this->employeesLogic->getCorpTokenInfo($user['corpIds'][0]);

        //取出这个企业的所有成员
        $employeeList = $this->employeesLogic->getEmployees($user['corpIds'][0]);

        //取出微信字母id
        $userIds = [];
        foreach ($employeeList as $item) {
            $userIds[] = $item['wxUserId'];
        }

        ##EasyWeChat获取「联系客户统计」数据
        $wx = $this->wxApp($user['corpIds'][0], 'contact')->external_contact_statistics;
        //获取某些成员的某日期的统计数据
        $res = $wx->userBehavior($userIds, (string) $params['startTime'], (string) $params['endTime']);
        if ($res['errcode'] !== 0) {
            $this->logger->error(sprintf('获取「联系客户统计」数据 失败::[%s]', json_encode($res, JSON_THROW_ON_ERROR)));
        }
        $res = $res['behavior_data'];
        $result = [
            'chat_cnt' => 0,
            'message_cnt' => 0,
            'reply_percentage' => 0,
            'avg_reply_time' => 0,
        ];

        foreach ($res as $re) {
            $result['chat_cnt'] += @$re['chat_cnt'];
            $result['message_cnt'] += @$re['message_cnt'];
            $result['reply_percentage'] += @$re['reply_percentage'];
            $result['avg_reply_time'] += @$re['avg_reply_time'];
        }

        $result['reply_percentage'] = round($result['reply_percentage'] / count($res), 2);
        $result['avg_reply_time'] = round($result['avg_reply_time'] / count($res), 2);

        return $result;
    }
}
