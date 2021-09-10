<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomClockIn\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\User\Contract\UserContract;
use MoChat\App\Utils\Url;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Plugin\RoomClockIn\Contract\ClockInContactContract;
use MoChat\Plugin\RoomClockIn\Contract\ClockInContract;

class IndexLogic
{
    /**
     * @Inject
     * @var ClockInContract
     */
    protected $clockInService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var ClockInContactContract
     */
    protected $clockInContactService;

    /**
     * @Inject
     * @var UserContract
     */
    protected $userService;

    /**
     * @var int
     */
    protected $perPage;

    public function __construct(ClockInContract $clockInService, WorkEmployeeContract $workEmployeeService, ClockInContactContract $clockInContactService)
    {
        $this->clockInService = $clockInService;
        $this->workEmployeeService = $workEmployeeService;
        $this->clockInContactService = $clockInContactService;
    }

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理请求参数
//        $params = $this->handleParams($user, $params);

        ## 查询数据
        return $this->getClockInList($user, $params);
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParams(array $user, array $params): array
    {
        $where = [];
        if (! empty($params['name'])) {
            $where[] = ['name', 'LIKE', '%' . $params['name'] . '%'];
        }
        if ($params['status'] > 0) {
            $where['time_type'] = 2;
        }
        $date = date('Y-m-d H:i:s');
        if ($params['status'] == 1) {
            $where[] = ['start_time', '<',  $date];
            $where[] = ['end_time', '>',  $date];
        }
        if ($params['status'] == 2) {
            $where[] = ['start_time', '>',  $date];
        }
        if ($params['status'] == 3) {
            $where[] = ['end_time', '<',  $date];
        }
        $where['corp_id'] = $user['corpIds'][0];
        $options = [
            'perPage' => $params['perPage'],
            'page' => $params['page'],
            'orderByRaw' => 'id desc',
        ];

        return ['where' => $where, 'options' => $options];
    }

    /**
     * 获取群打卡列表.
     * @param array $params 参数
     * @throws \JsonException
     * @return array 响应数组
     */
    private function getClockInList(array $user, array $params): array
    {
//        $columns = ['id', 'name','start_time', 'end_time', 'type','time_type','contact_clock_tags', 'create_user_id', 'created_at'];
//        $clockInList = $this->clockInService->getClockInList($params['where'], $columns, $params['options']);
        $clockInList = $this->clockInService->getClockInListBySearch($user, $params);

        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($clockInList['data']) ? $data : $this->handleData($clockInList);
    }

    /**
     * 数据处理.
     * @param array $clockInList 群打卡列表数据
     * @throws \JsonException
     * @return array 响应数组
     */
    private function handleData(array $clockInList): array
    {
        $list = [];
        foreach ($clockInList['data'] as $key => $val) {
            //处理创建者信息
            $username = $this->userService->getUserById($val['createUserId']);
            $totalDay = $this->clockInContactService->sumClockInContactTotalDayByClockInId((int) $val['id']);
            $totalUser = $this->clockInContactService->countClockInContactByClockInId((int) $val['id']);
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
                'contact_clock_tags' => empty($val['contactClockTags']) ? '' : array_column(json_decode($val['contactClockTags'], true, 512, JSON_THROW_ON_ERROR), 'tags'),
                'nickname' => isset($username['name']) ? $username['name'] : '',
                'type' => $val['type'] === 1 ? '连续打卡' : '累计打卡',
                'average_day' => $totalDay > 0 ? ceil($totalDay / $totalUser) : 0,
                'total_user' => $totalUser,
                'time' => $time,
                'created_at' => $val['createdAt'],
                'status' => $status,
                'share_link' => Url::getAuthRedirectUrl(1, $val['id']),
            ];
        }
        $data['page']['total'] = $clockInList['total'];
        $data['page']['totalPage'] = $clockInList['last_page'];
        $data['list'] = $list;

        return $data;
    }
}
