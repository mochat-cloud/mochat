<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Lottery\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\User\Contract\UserContract;
use MoChat\App\Utils\Url;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Plugin\Lottery\Contract\LotteryContactContract;
use MoChat\Plugin\Lottery\Contract\LotteryContract;
use MoChat\Plugin\Lottery\Contract\LotteryPrizeContract;

/**
 * 抽奖活动-列表.
 *
 * Class IndexLogic
 */
class IndexLogic
{
    /**
     * @Inject
     * @var LotteryContract
     */
    protected $lotteryService;

    /**
     * @Inject
     * @var LotteryPrizeContract
     */
    protected $lotteryPrizeService;

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
     * @var UserContract
     */
    protected $userService;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 查询数据
        return $this->getLotteryList($user, $params);
    }

    /**
     * 获取抽奖活动列表.
     * @param array $params 参数
     * @return array 响应数组
     */
    private function getLotteryList(array $user, array $params): array
    {
        $lotteryList = $this->lotteryService->getLotteryListBySearch($user, $params);
        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($lotteryList['data']) ? $data : $this->handleData($lotteryList);
    }

    /**
     * 数据处理.
     * @param array $lotteryList 抽奖活动列表数据
     * @return array 响应数组
     */
    private function handleData(array $lotteryList): array
    {
        $list = [];
        foreach ($lotteryList['data'] as $key => $val) {
            //处理创建者信息
            $username = $this->userService->getUserById($val['createUserId']);
            $time = '永久有效';
            if ($val['timeType'] === 2) {
                $time = $val['startTime'] . '-' . $val['endTime'];
            }
            $status = '进行中';
            $date = date('Y-m-d H:i:s');
            if ($val['timeType'] === 2) {
                if ($val['startTime'] >= $date) {
                    $status = '未开始';
                }
                if ($val['startTime'] < $date && $val['endTime'] > $date) {
                    $status = '进行中';
                }
                if ($val['endTime'] <= $date) {
                    $status = '已结束';
                }
            }

            $list[$key] = [
                'id' => $val['id'],
                'name' => $val['name'],
                'contact_clock_tags' => empty($val['contactClockTags']) ? '' : array_column(json_decode($val['contactClockTags']), 'tags'),
                'nickname' => isset($username['name']) ? $username['name'] : '',
                'total_user' => $this->lotteryContactService->countLotteryContactByLotteryIdDrawNum((int) $val['id']),
                'time' => $time,
                'created_at' => $val['createdAt'],
                'status' => $status,
                'share_link' => Url::getAuthRedirectUrl(2, $val['id'], ['source' => 'from_pc']),
            ];
        }
        $data['page']['total'] = $lotteryList['total'];
        $data['page']['totalPage'] = $lotteryList['last_page'];
        $data['list'] = $list;

        return $data;
    }
}
