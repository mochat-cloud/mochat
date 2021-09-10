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
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\User\Contract\UserContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\Lottery\Contract\LotteryContactContract;
use MoChat\Plugin\Lottery\Contract\LotteryContactRecordContract;
use MoChat\Plugin\Lottery\Contract\LotteryContract;
use MoChat\Plugin\Lottery\Contract\LotteryPrizeContract;

/**
 * 抽奖活动- 详情.
 *
 * Class Show.
 * @Controller
 */
class Show extends AbstractAction
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
     * @Inject
     * @var UserContract
     */
    protected $userService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/dashboard/lottery/show", methods="get")
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
        $id = $this->request->input('id');
        ## 查询数据
        return $this->handleData($id);
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
            'id.integer' => '活动ID 必需为整数',
            'id.min  ' => '活动ID 不可小于1',
        ];
    }

    /**
     * @param $id
     * @throws \JsonException
     */
    private function handleData($id): array
    {
        ## 数据统计
        $dataStatistics['total_browse_user'] = $this->lotteryContactService->countLotteryContactByLotteryId((int) $id);
        $dataStatistics['total_draw_user'] = $this->lotteryContactService->countLotteryContactByLotteryIdDrawNum((int) $id);
        $dataStatistics['total_win_user'] = $this->lotteryContactService->countLotteryContactByLotteryIdWinNum((int) $id);
        $dataStatistics['today_browse_user'] = $this->lotteryContactService->countLotteryContactTodayByLotteryId((int) $id);
        $dataStatistics['today_draw_user'] = $this->lotteryContactService->countLotteryContactTodayByLotteryIdDrawNum((int) $id);
        $dataStatistics['today_win_user'] = $this->lotteryContactService->countLotteryContactTodayByLotteryIdWinNum((int) $id);

        return [
            'lottery' => $this->lottery($id),
            'data_statistics' => $dataStatistics,
        ];
    }

    /**
     * 抽奖活动信息.
     * @param $id
     * @throws \JsonException
     */
    private function lottery($id): array
    {
        ## 抽奖活动信息
        $lottery = $this->lotteryService->getlotteryById((int) $id, ['id', 'name', 'description', 'start_time', 'end_time', 'time_type', 'contact_tags', 'create_user_id', 'created_at']);

        $lottery['time'] = '永久有效';
        if ($lottery['timeType'] === 2) {
            $lottery['time'] = $lottery['startTime'] . '-' . $lottery['endTime'];
        }
        $lottery['status'] = '进行中';
        $date = date('Y-m-d H:i:s');
        if ($lottery['timeType'] === 2) {
            if ($lottery['startTime'] >= $date) {
                $lottery['status'] = '未开始';
            }
            if ($lottery['startTime'] < $date && $lottery['endTime'] > $date) {
                $lottery['status'] = '进行中';
            }
            if ($lottery['endTime'] <= $date) {
                $lottery['status'] = '已结束';
            }
        }
        $lottery['contactTags'] = empty($lottery['contactTags']) ? '' : json_decode($lottery['contactTags'], true, 512, JSON_THROW_ON_ERROR);
        //处理创建者信息
        $username = $this->userService->getUserById($lottery['createUserId']);
        $lottery['nickname'] = isset($username['name']) ? $username['name'] : '';
        unset($lottery['startTime'], $lottery['endTime'], $lottery['timeType'], $lottery['createUserId']);
        $prize = $this->lotteryPrizeService->getLotteryPrizeByLotteryId((int) $lottery['id'], ['id', 'prize_set', 'exchange_set', 'draw_set', 'win_set']);
        $lottery['draw_set'] = json_decode($prize['drawSet'], true, 512, JSON_THROW_ON_ERROR);
        $lottery['win_set'] = json_decode($prize['winSet'], true, 512, JSON_THROW_ON_ERROR);
        $lottery['prize_set'] = $this->prizeSet(json_decode($prize['prizeSet'], true, 512, JSON_THROW_ON_ERROR), json_decode($prize['exchangeSet'], true, 512, JSON_THROW_ON_ERROR), (int) $id);
        return $lottery;
    }

    /**
     * 奖励明细.
     */
    private function prizeSet(array $prizeSet, array $exchangeSet, int $lotteryId): array
    {
        foreach ($prizeSet as $key => $val) {
            $prizeSet[$key]['win_num'] = $this->lotteryContactRecordService->countLotteryContactRecordByLotteryIdPrizeName((int) $lotteryId, $val['name']);
            $prizeSet[$key]['differ_num'] = (int) $val['num'] - (int) $prizeSet[$key]['win_num'];
            if ($val['name'] === '谢谢参与') {
                $prizeSet[$key]['num'] = '-';
                $prizeSet[$key]['differ_num'] = '-';
                break;
            }
            $prizeSet[$key]['type'] = $this->exchangeSet($val['name'], $exchangeSet);
            unset($prizeSet[$key]['rate'], $prizeSet[$key]['image']);
        }
        return $prizeSet;
    }

    /**
     * 兑换方式.
     */
    private function exchangeSet(string $name, array $exchangeSet): string
    {
        $type = '';
        foreach ($exchangeSet as $k => $v) {
            if ($v['name'] == $name) {
                $type = $v['type'] == 1 ? '客服二维码' : '兑换码';
                break;
            }
        }
        return $type;
    }
}
