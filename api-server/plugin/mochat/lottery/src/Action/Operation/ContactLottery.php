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
 * 抽奖活动H5- 客户抽奖.
 *
 * Class ContactData.
 * @Controller
 */
class ContactLottery extends AbstractAction
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
     * @RequestMapping(path="/operation/lottery/contactLottery", methods="put")
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $params = $this->request->all();
        ## 查询数据
        return $this->handleDate($params);
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
        ];
    }

    /**
     * @param $params
     * @throws \JsonException
     */
    private function handleDate($params): array
    {
        ## 抽奖限制 中奖限制
        $lottery = $this->lotteryService->getLotteryById((int) $params['id'], ['name', 'time_type', 'start_time', 'end_time', 'corp_id', 'contact_tags']);
        $prize = $this->lotteryPrizeService->getLotteryPrizeByLotteryId((int) $params['id'], ['prize_set', 'is_show', 'draw_set', 'exchange_set', 'win_set']);
        $contact = $this->lotteryContactService->getLotteryContactByLotteryIdUnionId((int) $params['id'], $params['union_id'], ['id', 'contact_id', 'grade', 'contact_tags', 'draw_num', 'win_num', 'employee_ids']);
        $contactCount = $this->lotteryContactRecordService->countLotteryContactRecordByLotteryIdContactId((int) $params['id'], (int) $contact['id']);

        ## 时间限制
        if ($lottery['timeType'] === 2 && time() < strtotime($lottery['startTime'])) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '抽奖还没有开始，开始时间为：' . $lottery['startTime']);
        }
        if ($lottery['timeType'] === 2 && time() > strtotime($lottery['endTime'])) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '抽奖已经结束，结束时间为：' . $lottery['endTime']);
        }

        ## 总抽奖机会限制次数
        $drawSet = json_decode($prize['drawSet'], true, 512, JSON_THROW_ON_ERROR); //抽奖限制
        if ($drawSet['type_total_num'] === 2 && $drawSet['max_total_num'] <= $contactCount) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '您的抽奖机会已用完');
        }

        ## 每日抽奖机会限制次数
        if ($drawSet['type_every_day_num'] === 2 && $drawSet['max_every_day_num'] <= $this->lotteryContactRecordService->countLotteryContactRecordTodayByLotteryIdContactId((int) $params['id'], (int) $contact['id'])) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '今日抽奖机会已用完哦~');
        }

        ## 中奖限制
        $winSet = json_decode($prize['winSet'], true, 512, JSON_THROW_ON_ERROR);
        if ($winSet['type_total_num'] === 2 && (int) $winSet['max_total_num'] <= $this->lotteryContactRecordService->countLotteryContactRecordWinByLotteryIdContactId((int) $params['id'], (int) $contact['id'])) {
            $this->handleDraw('谢谢参与', $params, $contact, $prize, $lottery);
            return ['prize_name' => '谢谢参与', 'receive_qr' => '', 'receive_code' => ''];
        }
        if ($winSet['type_every_day_num'] === 2 && (int) $winSet['max_every_day_num'] <= $this->lotteryContactRecordService->countLotteryContactRecordWinTodayByLotteryIdContactId((int) $params['id'], (int) $contact['id'])) {
            $this->handleDraw('谢谢参与', $params, $contact, $prize, $lottery);
            return ['prize_name' => '谢谢参与', 'receive_qr' => '', 'receive_code' => ''];
        }
        ## 抽奖
        $draw = $this->draw(json_decode($prize['prizeSet'], true, 512, JSON_THROW_ON_ERROR));
        return $this->handleDraw($draw['yes'], $params, $contact, $prize, $lottery);
    }

    /**
     * @throws \JsonException
     */
    private function handleDraw(string $winName, array $params, array $contact, array $prize, array $lottery): array
    {
        ## 抽奖信息
        $data = [
            'lottery_id' => $params['id'],
            'contact_id' => $contact['id'],
            'prize_id' => 1,
            'prize_name' => $winName,
            'receive_qr' => '',
            'receive_type' => 0,
            'receive_code' => '',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        ## 客户信息
        $contactData = [
            'draw_num' => $contact['drawNum'] + 1,
            'contact_tags' => empty($contact['contactTags']) ? [] : json_decode($contact['contactTags'], true, 512, JSON_THROW_ON_ERROR),
            'grade' => $contact['grade'],
        ];
        ## 标签
        $tags = json_decode($lottery['contactTags'], true, 512, JSON_THROW_ON_ERROR);
        if (! empty($tags) && $contact['drawNum'] === 0) {
            foreach ($tags as $key => $val) {
                if ($val['action'] === 1) {
                    array_merge($contactData['contact_tags'] = $val['tags']);
                    if ($val['type'] === 2) {
                        $contactData['grade'] = $val['grade'];
                    }
                }
            }
        }

        ## 奖项总数  中奖数量
        if ($winName !== '谢谢参与') {
            $prizeNum = 0;
            foreach (json_decode($prize['prizeSet'], true, 512, JSON_THROW_ON_ERROR) as $key => $val) {
                if ($val['name'] === $winName) {
                    $prizeNum = $val['num'];
                    $data['prize_id'] = $val['id'];
                    break;
                }
            }
            $totalNum = $this->lotteryContactRecordService->countLotteryContactRecordByLotteryIdPrizeName((int) $params['id'], $winName);
            if ($totalNum >= $prizeNum) {
                $data['prize_id'] = 1;
                $data['prize_name'] = '谢谢参与';
                $winName = '谢谢参与';
                $this->createLotteryContactRecord($data, $contact['id'], $contactData);
                throw new CommonException(ErrorCode::INVALID_PARAMS, '很抱歉，您所抽中的奖项已经中完！');
            }

            foreach (json_decode($prize['exchangeSet'], true, 512, JSON_THROW_ON_ERROR) as $key => $val) {
                if ($val['name'] === $winName) {
                    $data['receive_type'] = $val['type'];
                    $data['receive_qr'] = $val['employee_qr'];
                    if ($val['type'] === 2) {
                        $winCode = $this->lotteryContactRecordService->countLotteryContactRecordReceiveCodeByLotteryIdPrizeName((int) $params['id'], $winName, ['receive_code']);
                        $winCode = array_column($winCode, 'receiveCode');
                        $result = array_merge(array_diff($val['exchange_code'], $winCode));
                        $data['receive_code'] = sizeof($result) > 0 ? $result[0] : '';
                    }
                    break;
                }
            }
            if (! empty($tags) && $contact['winNum'] === 0) {
                foreach ($tags as $key => $val) {
                    if ($val['action'] === 2) {
                        array_merge($contactData['contact_tags'][] = $val['tags']);
                        if ($contact['contactId'] > 0 && ! empty($contact['employeeIds'])) {
                            $this->tag($params['id'], $contact['id'], 2);
                        }
                        if ($val['type'] === 2) {
                            $contactData['grade'] += $val['grade'];
                        }
                    }
                }
            }
            $contactData['win_num'] = $contact['winNum'] + 1;
            $this->send($contact, $lottery, $params, $winName);
        }
        $this->createLotteryContactRecord($data, $contact['id'], $contactData);
        return ['prize_name' => $data['prize_name'], 'receive_qr' => file_full_url($data['receive_qr']), 'receive_code' => $data['receive_code']];
    }

    /**
     * 抽奖.
     */
    private function draw(array $prize_arr): array
    {
        $arr = [];
        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['rate']; //将$prize_arr放入数组下标为$prize_arr的id元素，值为v元素的数组中
        }
        $rid = $this->get_rand($arr); //根据概率获取奖项id

        $res['yes'] = $prize_arr[$rid - 1]['name']; //获取中奖项

        unset($prize_arr[$rid - 1]); //将中奖项从数组中剔除，剩下未中奖项
        shuffle($prize_arr); //打乱数组顺序
        $pr = [];
        for ($i = 0, $iMax = count($prize_arr); $i < $iMax; ++$i) {
            $pr[] = $prize_arr[$i]['name'];
        }
        $res['no'] = $pr;
        return $res;
    }

    private function get_rand($proArr)
    {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr); //计算数组中元素的和
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) { //如果这个随机数小于等于数组中的一个元素，则返回数组的下标
                $result = $key;
                break;
            }
            $proSum -= $proCur;
        }
        unset($proArr);
        return $result;
    }

    /**
     * @throws \JsonException
     */
    private function createLotteryContactRecord(array $data, int $contactId, array $contactData): array
    {
        $contactData['contact_tags'] = json_encode($contactData['contact_tags'], JSON_THROW_ON_ERROR);
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建活动抽奖记录
            $this->lotteryContactRecordService->createLotteryContactRecord($data);
            $this->lotteryContactService->updateLotteryContactById($contactId, $contactData);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '抽奖失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
        return [];
    }

    /**
     * @throws \JsonException
     */
    private function tag(int $lottery_id, int $id, int $type): void
    {
        $lottery = $this->lotteryService->getLotteryById($lottery_id, ['contact_tags', 'corp_id']);
        $contactId = $this->lotteryContactService->getLotteryContactById($id, ['contact_id', 'contact_tags']);

        if ($contactId['contactId'] > 0 && ! empty($lottery['contactTags'])) {
            $record = $this->lotteryContactRecordService->getLotteryContactRecordByLotteryIdContactId($lottery_id, $id);
            if (! empty($record)) {
                return;
            }
            $contactTags = json_decode($lottery['contactTags'], true, 512, JSON_THROW_ON_ERROR);
            foreach ($contactTags as $item) {
                if ((int) $item['action'] === $type) {
                    $data = ['contactId' => 0, 'employeeId' => 0, 'tagArr' => array_column($item['tags'], 'tagid'), 'employeeWxUserId' => '', 'contactWxExternalUserid' => ''];
                    ## 客户id
                    $data['contactId'] = $contactId['contactId'];
                    ## 员工id
                    $contactEmployee = $this->workContactEmployeeService->getWorkContactEmployeeByCorpIdContactId($contactId['contactId'], (int) $lottery['corpId'], ['employee_id']);
                    $data['employeeId'] = $contactEmployee['employeeId'];
                    ## 客户
                    $contact = $this->workContactService->getWorkContactById($contactId['contactId'], ['wx_external_userid']);
                    $data['contactWxExternalUserid'] = $contact['wxExternalUserid'];
                    ## 员工
                    $employee = $this->workEmployeeService->getWorkEmployeeById($contactEmployee['employeeId'], ['wx_user_id']);
                    $data['employeeWxUserId'] = $employee['wxUserId'];
                    $data['corpId'] = $lottery['corpId'];
                    $this->autoTag($data);
                    $contactTag = empty($contactId['contactTags']) ? [] : json_decode($contactId['contactTags'], true, 512, JSON_THROW_ON_ERROR);
                    foreach ($item['tags'] as $item_tag) {
                        $contactTag[] = $item_tag;
                    }

                    $this->lotteryContactService->updateLotteryContactById($id, ['contact_tags' => json_encode($contactTag, JSON_THROW_ON_ERROR)]);
                    break;
                }
            }
        }
    }

    /**
     * MoChat提醒.
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    private function send(array $contact, array $lottery, array $params, string $winName)
    {
        $this->logger->error(sprintf('%s [%s] %s', '活动', date('Y-m-d H:i:s'), 'sds'));
        $employee = $this->workEmployeeService->getWorkEmployeesById(explode(',', $contact['employeeIds']), ['wx_user_id']);
        $employee = empty($employee) ? '' : array_column($employee, 'wxUserId');
        $touser = empty($employee) ? '' : implode('|', $employee);
        if (empty($touser)) {
            return;
        }
        $content = "【抽奖活动】\n客户昵称：{$params['nickname']}\n活动名称：{$lottery['name']}\n客户行为：客户抽中了奖品:{$winName}\n<a href='#'>点击查看客户详情 </a>";
        $messageRemind = make(MessageRemind::class);
        $messageRemind->sendToEmployee((int) $lottery['corpId'], $touser, 'text', $content);
    }
}
