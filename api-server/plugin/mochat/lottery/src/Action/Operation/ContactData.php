<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */

namespace MoChat\Plugin\Lottery\Action\Operation;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Utils\File;
use MoChat\App\WorkAgent\Contract\WorkAgentContract;
use MoChat\App\WorkAgent\QueueService\MessageRemind;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\AutoTag\Action\Dashboard\Traits\AutoContactTag;
use MoChat\Plugin\Lottery\Contract\LotteryContactContract;
use MoChat\Plugin\Lottery\Contract\LotteryContactRecordContract;
use MoChat\Plugin\Lottery\Contract\LotteryContract;
use MoChat\Plugin\Lottery\Contract\LotteryPrizeContract;

/**
 * 抽奖活动H5- 客户数据.
 *
 * Class ContactData.
 * @Controller
 */
class ContactData extends AbstractAction
{
    use ValidateSceneTrait;
    use AutoContactTag;
    use AppTrait;

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
     * @var LotteryPrizeContract
     */
    protected $lotteryPrizeService;

    /**
     * @Inject
     * @var LotteryContactRecordContract
     */
    protected $lotteryContactRecordService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkContactEmployeeContract
     */
    protected $workContactEmployeeService;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkAgentContract
     */
    private $workAgentService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/operation/lottery/contactData", methods="GET,POST")
     * @return array 返回数组
     * @throws \JsonException
     */
    public function handle(): array
    {
        // 参数验证
        $this->validated($this->request->all());
        // 接收参数
        $params = $this->request->all();
        // 查询数据
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
            'source' => 'required',
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
            'union_id.required' => 'union_id必填，请检查所属公众号是否绑定到微信开放平台帐号',
            'nickname.required' => 'nickname必填',
            'avatar.required' => 'avatar必填',
            'source' => 'source必填',
        ];
    }

    /**
     * @param $params
     * @throws \JsonException
     */
    private function handleData($params): array
    {
        $lottery = $this->lotteryService->getLotteryById((int)$params['id'], ['name', 'description', 'corp_id', 'time_type', 'start_time', 'end_time', 'contact_tags']);
        $prize = $this->lotteryPrizeService->getLotteryPrizeByLotteryId((int)$params['id'], ['corp_card', 'prize_set', 'is_show', 'draw_set', 'exchange_set']);
        // 企业名片
        $corp = json_decode($prize['corpCard'], true, 512, JSON_THROW_ON_ERROR);
        $corp['logo'] = ! empty($corp['logo']) ? file_full_url((string) $corp['logo']) : '';
        // 奖项设置
        $prizeSet = json_decode($prize['prizeSet'], true, 512, JSON_THROW_ON_ERROR);
        foreach ($prizeSet as $key => $val) {
            $prizeSet[$key]['image'] = str_contains($val['image'], 'http') ? $val['image'] : file_full_url((string) $val['image']);
            unset($prizeSet[$key]['num'], $prizeSet[$key]['rate']);
        }
        $prize['prizeSet'] = $prizeSet;
        $corpCard = json_decode($prize['corpCard'], true, 512, JSON_THROW_ON_ERROR);
        $corpCard['logo'] = file_full_url($corpCard['logo']);
        $prize['corpCard'] = $corpCard;

        $draw = json_decode($prize['drawSet'], true, 512, JSON_THROW_ON_ERROR);
        unset($draw['type_total_num'], $draw['max_total_num'], $draw['type_every_day_num'], $draw['max_every_day_num']);

        $exchangeSet = json_decode($prize['exchangeSet'], true, 512, JSON_THROW_ON_ERROR);
        if (!empty($exchangeSet)) {
            foreach ($exchangeSet as &$val) {
                unset($val['exchange_code']);
            }
        }
        // 判断参与
        $lotteryContact = $this->lotteryContactService->getLotteryContactByLotteryIdUnionId((int)$params['id'], $params['union_id'], ['id']);
        if (empty($lotteryContact)) {
            $this->createLotteryContact($params, $lottery);
        }

        return [
            'corp' => $corp,
            'lottery' => $lottery,
            'description' => $lottery['description'],
            'time' => $lottery['timeType'] === 1 ? '永久有效' : $lottery['startTime'] . '-' . $lottery['endTime'],
            'prize' => $prize,
            'point' => $draw,
            'message' => $this->message((int)$prize['isShow'], (int)$params['id']),
            'win_list' => $this->winList($params, $lotteryContact, json_decode($prize['exchangeSet'], true, 512, JSON_THROW_ON_ERROR)),
        ];
    }

    /**
     * 中奖记录.
     */
    private function winList(array $params, array $lotteryContact, array $exchange_set): array
    {
        $winList = [];
        if (!empty($lotteryContact)) {
            $winList = $this->lotteryContactRecordService->getLotteryContactRecordByLotteryIdContactId((int)$params['id'], $lotteryContact['id'], ['id', 'prize_name', 'receive_qr', 'receive_code', 'receive_status', 'created_at']);
            foreach ($winList as $key => $val) {
                $winList[$key]['receiveQr'] = file_full_url((string) $val['receiveQr']);
            }
        }
        return $winList;
    }

    /**
     * 实时展示已中奖客户记录.
     */
    private function message(int $isShow, int $lotteryId): array
    {
        $message = [];
        if ($isShow === 1) {
            $record = $this->lotteryContactRecordService->getLotteryContactRecordByLotteryId((int)$lotteryId, ['contact_id', 'prize_name']);
            if (!empty($record)) {
                $message['prize_name'] = $record['prizeName'];
                $contact = $this->lotteryContactService->getLotteryContactById((int)$record['contactId'], ['nickname', 'avatar']);
                $message['nickname'] = $contact['nickname'];
                $message['avatar'] = file_full_url($contact['avatar']);
            }
        }
        return $message;
    }

    /**
     * 创建客户信息.
     * @throws \JsonException
     */
    private function createLotteryContact(array $params, array $lottery): array
    {
        $corp = $this->corpService->getCorpById((int)$lottery['corpId'], ['id', 'name', 'wx_corpid', 'contact_secret']);
        $contactEmployee = $this->getContactEmployee($params['union_id'], (int)$lottery['corpId']);
        $data = [
            'lottery_id' => (int)$params['id'],
            'union_id' => $params['union_id'],
            'contact_id' => $contactEmployee['contact_id'],
            'nickname' => $params['nickname'],
            'avatar' => $params['avatar'] ? $params['avatar'] : '',
            'employee_ids' => $contactEmployee['employee_ids'],
            'city' => empty($params['city']) ? '' : $params['city'],
            'source' => $params['source'],
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // 数据操作
        Db::beginTransaction();
        try {
            // 创建客户
            $id = $this->lotteryContactService->createLotteryContact($data);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '客户创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'客户创建失败'
        }
        if ($contactEmployee['contact_id'] > 1) {
            $this->send($corp, $contactEmployee, $lottery, $params);
        }
        // 客户标签
        if ($contactEmployee['contact_id'] > 0 && !empty($lottery['contactTags'])) {
            $contactTags = json_decode($lottery['contactTags'], true, 512, JSON_THROW_ON_ERROR);
            foreach ($contactTags as $item) {
                if ((int)$item['action'] === 1) {
                    $data = ['contactId' => 0, 'employeeId' => 0, 'tagArr' => array_column($item['tags'], 'tagid'), 'employeeWxUserId' => '', 'contactWxExternalUserid' => ''];
                    // 客户id
                    $data['contactId'] = $contactEmployee['contact_id'];
                    // 员工id
                    $contactEmp = $this->workContactEmployeeService->getWorkContactEmployeeByCorpIdContactId($contactEmployee['contact_id'], (int)$lottery['corpId'], ['employee_id']);
                    $data['employeeId'] = $contactEmp['employeeId'];
                    // 客户
                    $contact = $this->workContactService->getWorkContactById($contactEmployee['contact_id'], ['wx_external_userid']);
                    $data['contactWxExternalUserid'] = $contact['wxExternalUserid'];
                    // 员工
                    $employee = $this->workEmployeeService->getWorkEmployeeById($contactEmp['employeeId'], ['wx_user_id']);
                    $data['employeeWxUserId'] = $employee['wxUserId'];
                    $this->autoTag($data);
                    $this->lotteryContactService->updateLotteryContactById($id, ['contact_tags' => json_encode($item['tags'], JSON_THROW_ON_ERROR)]);
                    break;
                }
            }
        }

        return [];
    }

    /**
     * 查询企业客户-员工.
     * @param $unionId
     * @param $corpId
     *
     * @return array
     */
    private function getContactEmployee($unionId, $corpId): array
    {
        $contact = $this->workContactService->getWorkContactByCorpIdUnionId((int)$corpId, $unionId, ['id']);
        $contactEmployee = empty($contact) ? '' : $this->workContactEmployeeService->getWorkContactEmployeesByContactId((int)$contact['id'], ['employee_id']);
        $employee = empty($contactEmployee) ? '' : array_column($contactEmployee, 'employeeId');
        $employeeIds = empty($employee) ? '' : implode(',', $employee);
        return [
            'contact_id' => empty($contact) ? 0 : $contact['id'],
            'employee_ids' => $employeeIds,
        ];
    }

    /**
     * MoChat提醒.
     * @throws \JsonException
     */
    private function send(array $corp, array $contactEmployee, array $lottery, array $params)
    {
        $employee = $this->workEmployeeService->getWorkEmployeesById(explode(',', $contactEmployee['employee_ids']), ['wx_user_id']);
        $employee = empty($employee) ? '' : array_column($employee, 'wxUserId');
        $touser = empty($employee) ? '' : implode('|', $employee);
        if (!empty($touser)) {
            return;
        }
        $content = "【抽奖活动】\n客户昵称：{$params['nickname']}\n活动名称：{$lottery['name']}\n客户行为：客户进入了抽奖活动页面\n<a href='#'>点击查看客户详情 </a>";
        $messageRemind = make(MessageRemind::class);
        $messageRemind->sendToEmployee((int)$corp['id'], $touser, 'text', $content);
    }
}
