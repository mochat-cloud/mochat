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
use Laminas\Stdlib\RequestInterface;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Utils\File;
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
 * 群打卡H5- 客户数据.
 *
 * Class ContactData.
 * @Controller
 */
class ContactData extends AbstractAction
{
    use ValidateSceneTrait;
    use AutoContactTag;

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
     * @RequestMapping(path="/operation/roomClockIn/contactData", methods="get")
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
            'nickname' => 'required',
            'avatar' => 'required',
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
            'nickname.required' => 'nickname必填',
            'avatar.required' => 'avatar必填',
        ];
    }

    /**
     * @param $params
     * 
     * @return array
     */
    private function handleData($params): array
    {
        $clockIn = $this->clockInService->getClockInById((int) $params['id'], ['name', 'corp_id', 'type', 'tasks', 'employee_qrcode', 'corp_card_status', 'corp_card', 'description']);
        $contact = $this->clockInContactService->getClockInContactByClockInIdUnionId((int) $params['id'], $params['union_id'], ['id', 'total_day', 'series_day', 'receive_level']);
        $corpInfo = $clockIn['corpCardStatus'] === 1 ? json_decode($clockIn['corpCard'], true, 512, JSON_THROW_ON_ERROR) : '';
        if (! empty($corpInfo)) {
            $corpInfo['logo'] = file_full_url($corpInfo['logo']);
        }
        $data = [
            'name' => $clockIn['name'],
            'description' => $clockIn['description'],
            'corp_card_status' => $clockIn['corpCardStatus'],
            'corp_info' => $corpInfo,
            'type' => $clockIn['type'],
            'day_count' => 0,
            'clock_in_status' => 0,
            'total_day' => 0,
            'series_day' => 0,
            'receive_level' => 0,
            'employee_qrcode' => file_full_url($clockIn['employeeQrcode']),
            'day_detail' => ['01' => [], '02' => [], '03' => [], '04' => [], '05' => [], '06' => [], '07' => [], '08' => [], '09' => [], '10' => [], '11' => [], '12' => []],
        ];
        ##参与活动记录
        if (empty($contact)) {
            $this->createClockInContact($params, $clockIn['corpId']);
        } else {
            $data['total_day'] = $contact['totalDay'];
            $data['series_day'] = $contact['seriesDay'];
            $data['receive_level'] = $contact['receiveLevel'];
            $data['day_count'] = $clockIn['type'] === 1 ? $contact['seriesDay'] : $contact['totalDay'];
            $todayRecord = $this->clockInContactRecordService->getClockInContactRecordByClockInIdContactId((int) $params['id'], $contact['id'], ['id']);
            empty($todayRecord) || $data['clock_in_status'] = 1;
            $dayDetail = $this->clockInContactRecordService->getClockInContactRecordsByClockInIdContactId((int) $params['id'], $contact['id'], ['day']);
//            foreach ($dayDetail as $k=>$v){
//                $dayDetail[$k]['day'] = substr($v['day'],0,10);
//            }
//            $data['day_detail'] = empty($dayDetail) ? [] : array_column($dayDetail,'day');
            $dataDay = ['01' => [], '02' => [], '03' => [], '04' => [], '05' => [], '06' => [], '07' => [], '08' => [], '09' => [], '10' => [], '11' => [], '12' => []];
            foreach ($dayDetail as $item) {
                $month = date('m', strtotime($item['day']));
                $dataDay[$month][] = date('Y-m-d', strtotime($item['day']));
            }

            $data['day_detail'] = array_values($dataDay);
        }
        ##任务differ_
        $tasks = json_decode($clockIn['tasks'], true, 512, JSON_THROW_ON_ERROR);
        foreach ($tasks as $key => $val) {
            $tasks[$key]['task_status'] = 0;
            $tasks[$key]['receive_status'] = 0;
            if ($data['receive_level'] >= $key + 1) {
                $tasks[$key]['receive_status'] = 1;
            }
            if ($clockIn['type'] === 1 && $data['series_day'] >= $val['count']) {
                $tasks[$key]['task_status'] = 1;
            }
            if ($clockIn['type'] === 2 && $data['total_day'] >= $val['count']) {
                $tasks[$key]['task_status'] = 1;
            }
            unset($tasks[$key]['title']);
        }

        $data['differ_day'] = 0;
        foreach ($tasks as $key => $val) {
            if ((int) $val['count'] > $data['day_count']) {
                $data['differ_day'] = (int) $val['count'] - $data['day_count'];
                break;
            }
        }
        $data['task_count'] = count($tasks);
        $data['tasks'] = $tasks;
        unset($data['total_day'], $data['series_day'], $data['receive_level']);
        return $data;
    }

    /**
     * @throws \JsonException
     */
    private function createClockInContact(array $params, int $corpId): array
    {
        $contactEmployee = $this->getContactEmployee($params['union_id'], $corpId);

        $data = [
            'clock_in_id' => (int) $params['id'],
            'union_id' => $params['union_id'],
            'contact_id' => $contactEmployee['contact_id'],
            'nickname' => $params['nickname'],
            'avatar' => File::uploadUrlImage($params['avatar'], 'image/roomClockIn/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg'),
            'employee_ids' => $contactEmployee['employee_ids'],
            'city' => $params['city'],
            'created_at' => date('Y-m-d H:i:s'),
        ];
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建客户
            $id = $this->clockInContactService->createClockInContact($data);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '客户创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'客户创建失败'
        }
        ## 客户标签处理
        $this->tag((int) $params['id'], $id, $contactEmployee['contact_id']);
        return [];
    }

    /**
     * 客户标签.
     * @throws \JsonException
     */
    private function tag(int $clock_id, int $id, int $contactId): void
    {
        $clockIn = $this->clockInService->getClockInById($clock_id, ['corp_id', 'contact_clock_tags']);
        if ($contactId > 0 && ! empty($clockIn['contactClockTags'])) {
            $contactTags = json_decode($clockIn['contactClockTags'], true, 512, JSON_THROW_ON_ERROR);
            foreach ($contactTags as $item) {
                if ((int) $item['count'] === 0) {
                    $data = ['contactId' => 0, 'employeeId' => 0, 'tagArr' => array_column($item['tags'], 'tagid'), 'employeeWxUserId' => '', 'contactWxExternalUserid' => ''];
                    ## 客户id
                    $data['contactId'] = $contactId;
                    ## 员工id
                    $contactEmployee = $this->workContactEmployeeService->getWorkContactEmployeeByCorpIdContactId($contactId, (int) $clockIn['corpId'], ['employee_id']);
                    $data['employeeId'] = $contactEmployee['employeeId'];
                    ## 客户
                    $contact = $this->workContactService->getWorkContactById($contactId, ['wx_external_userid']);
                    $data['contactWxExternalUserid'] = $contact['wxExternalUserid'];
                    ## 员工
                    $employee = $this->workEmployeeService->getWorkEmployeeById($contactEmployee['employeeId'], ['wx_user_id']);
                    $data['employeeWxUserId'] = $employee['wxUserId'];
                    $data['corpId'] = $clockIn['corpId'];
//                    throw new CommonException(ErrorCode::INVALID_PARAMS, json_encode($data, JSON_THROW_ON_ERROR));
                    $this->autoTag($data);
                    $this->clockInContactService->updateClockInContactById($id, ['contact_clock_tags' => json_encode($item['tags'], JSON_THROW_ON_ERROR)]);
                    break;
                }
            }
        }
    }

    /**
     * 查询企业客户-员工.
     * @param $union_id
     * @param $corpId
     */
    private function getContactEmployee($union_id, $corpId): array
    {
        $contact = $this->workContactService->getWorkContactByCorpIdUnionId((int) $corpId, $union_id, ['id']);
        $contactEmployee = empty($contact) ? '' : $this->workContactEmployeeService->getWorkContactEmployeesByContactId((int) $contact['id'], ['employee_id']);
        $employee = empty($contactEmployee) ? '' : array_column($contactEmployee, 'employeeId');
        $employeeIds = empty($employee) ? '' : implode(',', $employee);
        return [
            'contact_id' => empty($contact) ? 0 : $contact['id'],
            'employee_ids' => $employeeIds,
        ];
    }
}
