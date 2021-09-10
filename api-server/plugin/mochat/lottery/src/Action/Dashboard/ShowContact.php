<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Lottery\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\Lottery\Contract\LotteryContactContract;
use MoChat\Plugin\Lottery\Contract\LotteryContactRecordContract;
use MoChat\Plugin\Lottery\Contract\LotteryContract;
use MoChat\Plugin\Lottery\Contract\LotteryPrizeContract;
use Psr\Container\ContainerInterface;

/**
 * 抽奖活动- 客户数据详情.
 *
 * Class ShowContactKeyWord.
 * @Controller
 */
class ShowContact extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var LotteryContract
     */
    protected $lotteryService;

    /**
     * @Inject
     * @var LotteryContactContract
     */
    protected $lotteryContactService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var LotteryContactRecordContract
     */
    protected $lotteryContactRecordService;

    /**
     * @Inject
     * @var LotteryPrizeContract
     */
    protected $lotteryPrizeService;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * Show constructor.
     */
    public function __construct(RequestInterface $request, ContainerInterface $container)
    {
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * @RequestMapping(path="/dashboard/lottery/showContact", methods="get")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $params = [
            'id' => $this->request->input('id'),
            'status' => $this->request->input('status'),
            'nickname' => $this->request->input('nickname', null),
            'page' => $this->request->input('page', 1),
            'perPage' => $this->request->input('perPage', 10000),
        ];
        ## 处理请求参数
        $params = $this->handleParams($params);
        ## 查询数据
        return $this->getContactList($params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'id' => 'required | integer | min:0 | bail',
            'status' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'id.required' => '活动ID 必填',
            'id.min  ' => '活动ID 不可小于1',
            'status' => 'status 必填',
        ];
    }

    /**
     * 处理参数.
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParams(array $params): array
    {
        $where['lottery_id'] = (int) $params['id'];
        if (! empty($params['nickname'])) {
            $where[] = ['nickname', 'LIKE', '%' . $params['nickname'] . '%'];
        }

        if ((int) $params['status'] === 1) {
            $where[] = ['win_num', '>', 0];
        }
        if ((int) $params['status'] === 2) {
            $where['win_num'] = 0;
            $where[] = ['draw_num', '>', 0];
        }
        if ((int) $params['status'] === 3) {
            $where['draw_num'] = 0;
        }
        $options = [
            'perPage' => $params['perPage'],
            'page' => $params['page'],
            'orderByRaw' => 'id desc',
        ];

        return ['where' => $where, 'options' => $options];
    }

    /**
     * 获取客户列表.
     * @param array $params 参数
     * @throws \JsonException
     * @return array 响应数组
     */
    private function getContactList(array $params): array
    {
        $columns = ['id', 'lottery_id', 'contact_id', 'nickname', 'avatar', 'employee_ids', 'city', 'source', 'grade', 'contact_tags', 'draw_num', 'win_num', 'write_off'];
        $contactList = $this->lotteryContactService->getLotteryContactList($params['where'], $columns, $params['options']);

        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($contactList['data']) ? $data : $this->handleData($contactList);
    }

    /**
     * 数据处理.
     * @param $contactList
     * @throws \JsonException
     */
    private function handleData($contactList): array
    {
        $list = [];
        foreach ($contactList['data'] as $key => $val) {
            $list[$key] = [
                'id' => $val['id'],
                'contact_id' => $val['contactId'],
                'nickname' => $val['nickname'],
                'avatar' => file_full_url($val['avatar']),
                'type' => $val['contactId'] > 0 ? '企业客户' : '微信客户',
                'source' => $val['source'] === 'from_qr' ? '分享弹窗' : $val['source'],
                'city' => $val['city'],
                'employee' => $this->employees($val['employeeIds']),
                'grade' => $val['grade'],
                'contact_tags' => empty($val['contactTags']) ? '' : json_decode($val['contactTags'], true, 512, JSON_THROW_ON_ERROR),
                'draw_num' => $val['drawNum'],
                'status' => $this->status($val['drawNum'], $val['winNum']),
                'win_record' => $this->lotteryContactRecordService->getLotteryContactRecordByLotteryIdContactId((int) $val['lotteryId'], (int) $val['id'], ['prize_name']),
                'write_off' => $val['writeOff'],
            ];
        }
        $data['page']['total'] = $contactList['total'];
        $data['page']['totalPage'] = $contactList['last_page'];
        $data['list'] = $list;

        return $data;
    }

    /**
     * 所属员工.
     * @param $ids
     */
    private function employees($ids): array
    {
        $employee = $this->workEmployeeService->getWorkEmployeesById(explode(',', $ids), ['name', 'avatar']);
        foreach ($employee as $k => $v) {
            $employee[$k]['avatar'] = file_full_url($v['avatar']);
        }
        return $employee;
    }

    /**
     * 参与程度.
     */
    private function status(int $drawNum, int $winNum): string
    {
        $status = '已浏览未参与';
        if ($drawNum > 0 && $winNum == 0) {
            $status = '已参与';
        }
        if ($winNum > 0) {
            $status = '已获奖';
        }
        return $status;
    }
}
