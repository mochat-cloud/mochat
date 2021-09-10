<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomClockIn\Action\Operation;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomClockIn\Contract\ClockInContactContract;
use MoChat\Plugin\RoomClockIn\Contract\ClockInContactRecordContract;
use MoChat\Plugin\RoomClockIn\Contract\ClockInContract;

/**
 * 群打卡H5- 排行榜.
 *
 * Class ClockInRanking.
 * @Controller
 */
class ClockInRanking extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ClockInContract
     */
    protected $clockInService;

    /**
     * @Inject
     * @var ClockInContactContract
     */
    protected $clockInContactService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var ClockInContactRecordContract
     */
    protected $clockInContactRecordService;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var WorkContactEmployeeContract
     */
    protected $workContactEmployeeService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/operation/roomClockIn/clockInRanking", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $params = $this->request->all();
        ## 查询数据
        return $this->handleData($params);
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
            'union_id' => 'required',
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
            'union_id.required' => 'union_id必填',
        ];
    }

    private function handleData($params): array
    {
        $clockIn = $this->clockInService->getClockInById((int) $params['id'], ['name', 'corp_id', 'type', 'tasks']);
        $contact = $this->clockInContactService->getClockInContactByClockInIdUnionId((int) $params['id'], $params['union_id'], ['id', 'total_day', 'series_day', 'receive_level']);
        $todayRecord = [];
        if (! empty($contact)) {
            $todayRecord = $this->clockInContactRecordService->getClockInContactRecordByClockInIdContactId((int) $params['id'], $contact['id'], ['id']);
        }

        return [
            'total_user' => $this->clockInContactService->countClockInContactByClockInId((int) $params['id']),
            'clock_in_status' => empty($todayRecord) ? 0 : 1,
            'contact_ranking' => empty($contact) ? 0 : $this->contactRanking($params, $clockIn, $contact),
            'contact_list' => $this->contactList($params, $clockIn),
        ];
    }

    /**
     * 排行.
     */
    private function contactRanking(array $params, array $clockIn, array $contact): int
    {
        $contactRanking = 0;
        if ($clockIn['type'] === 1 && $contact['seriesDay'] > 0) {
            $seriesDay = $this->clockInContactService->getClockInContactListGroupSeriesDay((int) $params['id'], ['series_day']);
            foreach ($seriesDay as $val) {
                $contactRanking += $val['total'];
                if ($val['total'] === (int) $contact['seriesDay']) {
                    break;
                }
            }
        }
        if ($clockIn['type'] === 2 && $contact['totalDay'] > 0) {
            $totalDay = $this->clockInContactService->getClockInContactListGroupTotalDay((int) $params['id'], ['total_day']);
            foreach ($totalDay as $key => $val) {
                $contactRanking += $val['total'];
                if ($val['total'] === (int) $contact['totalDay']) {
                    break;
                }
            }
        }

        return $contactRanking;
    }

    /**
     * 客户排行列表.
     */
    private function contactList(array $params, array $clockIn): array
    {
        $contactList = $this->clockInContactService->getClockInContactListGroupTotalDayLimit((int) $params['id'], ['id', 'nickname', 'avatar', 'total_day', 'series_day']);
        foreach ($contactList as $key => $val) {
            $contactList[$key]['avatar'] = file_full_url($val['avatar']);
            $contactList[$key]['ranking'] = $key + 1;
            $contactList[$key]['day_count'] = $clockIn['type'] === 1 ? $val['seriesDay'] : $val['totalDay'];
            unset($contactList[$key]['id'], $contactList[$key]['seriesDay'], $contactList[$key]['totalDay']);
        }
        return $contactList;
    }
}
