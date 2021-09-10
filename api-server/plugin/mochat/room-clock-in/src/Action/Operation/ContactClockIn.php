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
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\AutoTag\Action\Dashboard\Traits\AutoContactTag;
use MoChat\Plugin\RoomClockIn\Contract\ClockInContactContract;
use MoChat\Plugin\RoomClockIn\Contract\ClockInContactRecordContract;
use MoChat\Plugin\RoomClockIn\Contract\ClockInContract;

/**
 * H5 - 用户参与打卡.
 *
 * Class ContactClockIn.
 * @Controller
 */
class ContactClockIn extends AbstractAction
{
    use ValidateSceneTrait;
    use AutoContactTag;

    /**
     * @var \Laminas\Stdlib\RequestInterface
     */
    protected $request;

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

    public function __construct(
        RequestInterface $request,
        ClockInContract $clockInService,
        ClockInContactContract $clockInContactService,
        WorkEmployeeContract $workEmployeeService,
        ClockInContactRecordContract $clockInContactRecordService,
        CorpContract $corpService,
        WorkContactContract $workContactService,
        WorkContactEmployeeContract $workContactEmployeeService
    ) {
        $this->request = $request;
        $this->clockInService = $clockInService;
        $this->clockInContactService = $clockInContactService;
        $this->workEmployeeService = $workEmployeeService;
        $this->clockInContactRecordService = $clockInContactRecordService;
        $this->corpService = $corpService;
        $this->workContactService = $workContactService;
        $this->workContactEmployeeService = $workContactEmployeeService;
    }

    /**
     * @RequestMapping(path="/operation/roomClockIn/contactClockIn", methods="put")
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $params = $this->request->all();
        $this->validated($params);

        ## 打卡验证
        $clockIn = $this->clockInService->getClockInById((int) $params['id'], ['id', 'type', 'time_type', 'start_time', 'end_time', 'tasks']);
        ## 客户打卡信息
        $contact = $this->clockInContactService->getClockInContactByClockInIdUnionId((int) $params['id'], $params['union_id'], ['id', 'contact_id', 'contact_clock_tags', 'total_day', 'series_day']);

        if (! empty($this->clockInContactRecordService->getClockInContactRecordByClockInIdContactId((int) $params['id'], $contact['id'], ['id']))) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '打卡成功');
        }
        $date = date('Y-m-d H:i:s');
        if ($clockIn['timeType'] === 2 && ($date < $clockIn['startTime'] || $date > $clockIn['endTime'])) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请在活动有效期内打卡');
        }
        ## 客户最后一次打卡时间
        $last = $this->clockInContactRecordService->getClockInContactRecordLastByClockInIdContactId((int) $params['id'], $contact['id'], ['id', 'day']);

        return $this->handleData($params, $clockIn, $contact, $last);
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

    /**
     * @throws \JsonException
     */
    private function handleData(array $params, array $clockIn, array $contact, array $last): array
    {
        $tasks = json_decode($clockIn['tasks'], true, 512, JSON_THROW_ON_ERROR);
        $lastTasks = array_pop($tasks);
        $taskCount = $lastTasks['count'];
        $status = 0;
        if ($clockIn['type'] === 1 && $contact['seriesDay'] + 1 >= $taskCount) {
            $status = 1;
        }
        if ($clockIn['type'] === 2 && $contact['totalDay'] + 1 >= $taskCount) {
            $status = 1;
        }
        $data = [
            'clock_in_id' => (int) $params['id'],
            'contact_id' => $contact['id'],
            'day' => date('Y-m-d H:i:s'),
            'type' => $clockIn['type'],
            'created_at' => date('Y-m-d H:i:s'),
        ];
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 客户打卡
            $this->clockInContactRecordService->createClockInContactRecord($data);
            ## 修改打卡天数
            $this->clockInContactService->updateClockInContactById((int) $contact['id'], ['total_day' => $contact['totalDay'] + 1, 'status' => $status]);
            $differDay = 0;
            if ($clockIn['type'] === 1) {
                if (! empty($last)) {
                    $lastDay = strtotime(date('Y-m-d', strtotime($last['day'])));
                    $today = strtotime(date('Y-m-d', time()));
                    $differDay = round(($today - $lastDay) / 3600 / 24);
                }
                if (empty($last) || $differDay !== 1) {
                    $this->clockInContactService->updateClockInContactById((int) $contact['id'], ['series_day' => 1]);
                }
                if ($differDay === 1) {
                    $this->clockInContactService->updateClockInContactById((int) $contact['id'], ['series_day' => $contact['seriesDay'] + 1]);
                }
            }
            $num = 0;
            if ($clockIn['type'] === 2) {
                $num = $contact['totalDay'] + 1;
            }
            if (empty($last) || $differDay !== 1) {
                $num = 1;
            }
            if ($differDay === 1) {
                $num = $contact['seriesDay'] + 1;
            }
            $this->tag((int) $params['id'], $contact['id'], $contact['contactId'], $num, $contact['contactClockTags']);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '客户创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'客户创建失败'
        }
        $dayCount = 0;
        $status = 0;
        foreach (json_decode($clockIn['tasks'], true, 512, JSON_THROW_ON_ERROR) as $key => $val) {
            ## 连续打卡
            if ($clockIn['type'] === 1) {
                if ((int) $val['count'] === $contact['seriesDay'] + 1) {
                    $dayCount = (int) $val['count'];
                    $status = 1;
                    break;
                }
                if ((int) $val['count'] > $contact['seriesDay'] + 1) {
                    $dayCount = (int) $val['count'];
                    break;
                }
            }
            ## 累计打卡
            if ($clockIn['type'] === 2) {
                if ((int) $val['count'] === $contact['totalDay'] + 1) {
                    $dayCount = (int) $val['count'];
                    $status = 1;
                    break;
                }
                if ((int) $val['count'] > $contact['totalDay'] + 1) {
                    $dayCount = (int) $val['count'];
                    break;
                }
            }
        }

        return ['day_count' => $dayCount, 'day' => date('Y-m-d H:i:s'), 'status' => $status];
    }

    /**
     * 客户标签.
     * @param $contactClockTags
     * @throws \JsonException
     */
    private function tag(int $clock_id, int $id, int $contact_id, int $count, $contactClockTags): void
    {
        $clockIn = $this->clockInService->getClockInById($clock_id, ['corp_id', 'contact_clock_tags']);
        if ($contact_id > 0 && ! empty($clockIn['contactClockTags'])) {
            $contactTags = json_decode($clockIn['contactClockTags'], true, 512, JSON_THROW_ON_ERROR);
            foreach ($contactTags as $item) {
                if ((int) $item['count'] === $count) {
                    $data = ['contactId' => 0, 'employeeId' => 0, 'tagArr' => array_column($item['tags'], 'tagid'), 'employeeWxUserId' => '', 'contactWxExternalUserid' => ''];
                    ## 客户id
                    $data['contactId'] = $contact_id;
                    ## 员工id
                    $contactEmployee = $this->workContactEmployeeService->getWorkContactEmployeeByCorpIdContactId($contact_id, (int) $clockIn['corpId'], ['employee_id']);
                    $data['employeeId'] = $contactEmployee['employeeId'];
                    ## 客户
                    $contact = $this->workContactService->getWorkContactById($contact_id, ['wx_external_userid']);
                    $data['contactWxExternalUserid'] = $contact['wxExternalUserid'];
                    ## 员工
                    $employee = $this->workEmployeeService->getWorkEmployeeById($contactEmployee['employeeId'], ['wx_user_id']);
                    $data['employeeWxUserId'] = $employee['wxUserId'];
                    $data['corpId'] = $clockIn['corpId'];
                    $this->autoTag($data);
                    $contactTag = empty($contactClockTags) ? [] : json_decode($contactClockTags, true, 512, JSON_THROW_ON_ERROR);
                    foreach ($item['tags'] as $item_tag) {
                        $contactTag[] = $item_tag;
                    }
                    $this->clockInContactService->updateClockInContactById($id, ['contact_clock_tags' => json_encode($contactTag, JSON_THROW_ON_ERROR)]);
                    break;
                }
            }
        }
    }
}
