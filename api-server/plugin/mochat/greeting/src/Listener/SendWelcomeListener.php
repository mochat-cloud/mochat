<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Greeting\Listener;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use MoChat\App\Medium\Contract\MediumContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Event\AddContactEvent;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Plugin\Greeting\Constants\RangeType;
use MoChat\Plugin\Greeting\Contract\GreetingContract;

/**
 * 添加企业客户事件.
 *
 * @Listener
 */
class SendWelcomeListener implements ListenerInterface
{
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
     * @var GreetingContract
     */
    private $greetingService;

    /**
     * @Inject
     * @var MediumContract
     */
    private $mediumService;

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

        // 判断是否需要发送欢迎语
        if (! $this->isNeedSendWelcome($contact)) {
            return;
        }

        // 获取欢迎语
        $welcomeContent = $this->getWelcome($contact['employeeId'], (int) $contact['corpId']);
        if (empty($welcomeContent)) {
            return;
        }

        // 发送欢迎语
        $this->workContactService->sendWelcome((int) $contact['corpId'], $contact, $contact['welcomeCode'], $welcomeContent);
    }

    /**
     * 判断是否需要发送欢迎语.
     *
     * @return bool
     */
    private function isNeedSendWelcome(array $contact)
    {
        if (isset($contact['state']) && ! empty($contact['state'])) {
            return false;
        }

        if (! isset($contact['welcomeCode']) || empty($contact['welcomeCode'])) {
            return false;
        }

        return true;
    }

    /**
     * 获取欢迎语.
     *
     * @param array $contact 客户
     *
     * @return array[]
     */
    private function getWelcome(array $contact): array
    {
        $data = [];
        $employeeId = $contact['employeeId'];
        $corpId = $contact['corpId'];
        if (empty($employeeId)) {
            return $data;
        }

        // 查询员工欢迎语
        $greetingList = $this->greetingService->getGreetingsByCorpId($corpId, ['id', 'medium_id', 'words', 'range_type', 'employees']);
        if (empty($greetingList)) {
            return $data;
        }
        $commonGreeting = [];
        foreach ($greetingList as $greeting) {
            // 检索通用欢迎语
            $greeting['rangeType'] == RangeType::ALL && $commonGreeting = [
                'text' => $greeting['words'],
                'mediumId' => $greeting['mediumId'],
            ];
            // 检索指定成员欢迎语
            $employees = empty($greeting['employees']) ? [] : json_decode($greeting['employees'], true);
            if (! in_array($employeeId, $employees)) {
                continue;
            }
            $data = [
                'text' => $greeting['words'],
                'mediumId' => $greeting['mediumId'],
            ];
        }
        if (empty($data) && ! empty($commonGreeting)) {
            $data = $commonGreeting;
        }
        if (isset($data['mediumId'])) {
            $data['medium'] = $this->getMedium((int) $data['mediumId']);
            unset($data['mediumId']);
        }
//        $params       = ['contactWxExternalUserid' => $externalUserID, 'wxUserId' => $wxUserId, 'corpId' => (int) $corpId];
//        $autoTag      = $this->autoTagLogic->getAutoTag($params);
//        $data['tags'] = $autoTag['tags'];
        return $data;
    }

    /**
     * @param int $mediumId 素材库ID
     * @return array 响应数组
     */
    private function getMedium(int $mediumId): array
    {
        $medium = $this->mediumService->getMediumById($mediumId, ['id', 'type', 'content']);
        return empty($medium) ? [] : [
            'mediumType' => $medium['type'],
            'mediumContent' => json_decode($medium['content'], true),
        ];
    }
}
