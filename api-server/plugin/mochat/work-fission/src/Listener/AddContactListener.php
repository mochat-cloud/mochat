<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\WorkFission\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\WorkAgent\QueueService\MessageRemind;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Event\AddContactEvent;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContactContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionPushContract;

/**
 * 添加企业客户事件.
 *
 * @Listener
 */
class AddContactListener implements ListenerInterface
{
    use AppTrait;

    /**
     * @Inject
     * @var WorkFissionContract
     */
    protected $workFissionService;

    /**
     * @Inject
     * @var WorkFissionContactContract
     */
    protected $workFissionContactService;

    /**
     * @Inject
     * @var WorkFissionPushContract
     */
    protected $workFissionPushService;

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
     * @var MessageRemind
     */
    protected $messageRemind;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function listen(): array
    {
        return [
            AddContactEvent::class,
        ];
    }

    /**
     * @param AddContactEvent $event
     */
    public function process(object $event)
    {
        $contact = $event->message;
        $this->handleFission($contact);
    }

    /**
     * 任务宝-裂变用户数据处理.
     * @param $wxData
     * @param $wxContact
     * @param mixed $contact
     */
    private function handleFission($contact): void
    {
        if (! isset($contact['state']) || empty($contact['state']) || ! str_contains($contact['state'], 'fission')) {
            return;
        }
        ## 开启事物
        Db::beginTransaction();
        try {
            ## 客户头像上传到阿里云
            $pathFileName = empty($contact['avatar']) ? '' : $contact['avatar'];
            $stateArr = explode('-', $contact['state']);
            if (empty($stateArr[1])) {
                return;
            }
            $parent = $this->workFissionContactService->getWorkFissionContactById((int) $stateArr[1], ['id', 'fission_id', 'level', 'invite_count', 'contact_superior_user_parent']);
            $this->logger->error('parent');
            if (empty($parent)) {
                return;
            }
            ## 任务宝-裂变等级
            $parentLevel = $parent['level'];
            if ($parent['level'] === 0) {//师傅无裂变等级
                if ($parent['contactSuperiorUserParent'] === 0) {
                    $parentLevel = 1;
                } elseif ($parent['contactSuperiorUserParent'] > 0) {//有师傅，二级裂变
                    $parentLevel = 2;
                    $shizu = $this->workFissionContactService->getWorkFissionContactById((int) $parent['contactSuperiorUserParent'], ['id', 'fission_id', 'level']);
                    if ($shizu['contactSuperiorUserParent'] > 0) {//师傅有师傅，三级裂变
                        $parentLevel = 3;
                        $shizuPlus = $this->workFissionContactService->getWorkFissionContactById((int) $shizu['id'], ['id', 'fission_id', 'level']);
                        if ($shizuPlus['contactSuperiorUserParent'] > 0) {//师祖有师傅，不记录裂变等级
                            $parentLevel = 0;
                        }
                    }
                }
            }
            $new = $contact['isNew'];
            $contactInfo = $this->workFissionContactService->getWorkContactByWxExternalUserIdParent((int) $stateArr[1], $contact['wxExternalUserid']);
            $employee = $this->workEmployeeService->getWorkEmployeeById($contact['employeeId'], ['wx_user_id']);
            if (empty($contactInfo)) {
                $level = $parentLevel > 2 ? 0 : $parentLevel + 1;
                if ($parentLevel == 0) {
                    $level = 0;
                }
                ## 任务宝-裂变用户信息
                $fissionContact = [
                    'fission_id' => $parent['fissionId'],
                    'union_id' => isset($contact['unionid']) ? $contact['unionid'] : '',
                    'nickname' => isset($contact['name']) ? $contact['name'] : '',
                    'avatar' => $pathFileName,
                    'contact_superior_user_parent' => (int) $stateArr[1],
                    'level' => $level,
                    'employee' => $employee['wxUserId'],
                    'external_user_id' => $contact['wxExternalUserid'],
                    'is_new' => $new,
                    'loss' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                $this->workFissionContactService->createWorkFissionContact($fissionContact);
            } else {
                $this->workContactService->updateWorkContactById((int) $contactInfo['id'], ['loss' => 0]);
            }

            ## 任务宝-裂变用户上级信息
            $inviteCount = $parent['inviteCount']++;
            $fission = $this->workFissionService->getWorkFissionById((int) $parent['fissionId'], ['service_employees', 'tasks', 'end_time', 'new_friend']);
            if (! ($fission['newFriend'] === 1 && $new === 0)) {
                $inviteCount = $parent['inviteCount'];
            }//必须新好友才能助力,客户已非新好友
            $this->workFissionContactService->updateWorkFissionContactById((int) $parent['id'], ['level' => $parentLevel, 'invite_count' => $inviteCount, 'employee' => $employee['wxUserId']]);

            ## 裂变成功推送消息
            $totalCount = 0;
            foreach (json_decode($fission['tasks'], true, 512, JSON_THROW_ON_ERROR) as $key => $val) {
                $totalCount += $val['count'];
            }
            if ($totalCount === $inviteCount) {
                $this->workFissionContactService->updateWorkFissionContactById((int) $parent['id'], ['status' => 1]);
            }
            if ($totalCount === $inviteCount && strtotime($fission['endTime']) > time()) {//邀请人数已满，活动有效期内
                $push = $this->workFissionPushService->getWorkFissionPushByFissionId((int) $parent['fissionId'], ['push_employee', 'push_contact', 'msg_text', 'msg_complex', 'msg_complex_type']);
                if ($push['pushEmployee'] === 1) {
                    $this->sendEmployeeMsg($contact, array_column(json_decode($fission['serviceEmployees'], true, 512, JSON_THROW_ON_ERROR), 'wxUserId'), $fission);
                }
                if ($push['pushContact'] === 1) {
                    // TODO 发送给客户应该是用群发消息，不是用欢迎语
                    // $this->sendContactMsg($contact, $push);
                }
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            ## 记录错误日志
            $this->logger->error(sprintf('%s [%s] %s', '裂变用户数据处理失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
        }
    }

    /**
     * 发送客户.
     * @param $contact
     * @param $push
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendContactMsg($contact, $push)
    {
        $sendWelcomeData = [];
        if (! empty($push['msgText'])) {
            $sendWelcomeData['text']['content'] = str_replace('[用户昵称]', '%NICKNAME%', $push['msgText']);
        }
        if (! empty($push['msgComplexType']) && $push['msgComplexType'] === 'image') {
            $image = json_decode($push['msgComplex'], true, 512, JSON_THROW_ON_ERROR);
            $sendWelcomeData['image']['pic_url'] = $image['pic_url'];
        }
        if (! empty($push['msgComplexType']) && $push['msgComplexType'] === 'link') {
            $link = json_decode($push['msgComplex'], true, 512, JSON_THROW_ON_ERROR);
            $sendWelcomeData['link'] = [
                'title' => $link['title'],
                'picurl' => $link['pic_url'],
                'desc' => $link['desc'],
                'url' => $link['url'],
            ];
        }
        if (! empty($push['msgComplexType']) && $push['msgComplexType'] == 'applets') {
            $applets = json_decode($push['msgComplex'], true, 512, JSON_THROW_ON_ERROR);
            $sendWelcomeData['miniprogram'] = [
                'title' => $applets['title'],
                'pic_media_id' => $applets['pic_url'],
                'appid' => $applets['appid'],
                'page' => $applets['path'],
            ];
        }

        $messageService = $this->wxApp($contact['corpId'], 'contact')->external_contact_message;
        $sendWelcomeRes = $messageService->sendWelcome($contact['welcomeCode'], $sendWelcomeData);
        if ($sendWelcomeRes['errcode'] !== 0) {
            ## 记录错误日志
            $this->logger->error(sprintf('%s [%s] %s', '裂变成功推送消息失败', date('Y-m-d H:i:s'), json_encode($sendWelcomeRes, JSON_THROW_ON_ERROR)));
        }
    }

    /**
     * 发送员工提醒.
     * @param array $contact
     * @param array $employee
     * @param array $fission
     */
    private function sendEmployeeMsg($contact, $employee, $fission)
    {
        $to = implode('|', $employee);
        $content = sprintf('客户【%s】已完成裂变任务：%s', $contact['name'], $fission['activeName']);
        $this->messageRemind->sendToEmployee($contact['corpId'], $to, 'text', $content);
    }
}
