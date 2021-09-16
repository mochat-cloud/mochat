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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use MoChat\App\Medium\Contract\MediumContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Event\ContactWelcomeEvent;
use MoChat\Plugin\Greeting\Constants\RangeType;
use MoChat\Plugin\Greeting\Contract\GreetingContract;

/**
 * 添加企业客户事件.
 * 通用欢迎语执行优先级低一些，保障最后执行
 *
 * @Listener(priority=1)
 */
class SendWelcomeListener implements ListenerInterface
{
    /**
     * @Inject
     * @var WorkContactContract
     */
    private $workContactService;

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

    /**
     * @Inject()
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function listen(): array
    {
        return [
            ContactWelcomeEvent::class,
        ];
    }

    /**
     * @param ContactWelcomeEvent $event
     */
    public function process(object $event)
    {
        $contact = $event->message;

        // 判断是否需要发送欢迎语
        if (! $this->isNeedSendWelcome($contact)) {
            return;
        }

        // 获取欢迎语
        $welcomeContent = $this->getWelcome($contact);
        if (empty($welcomeContent)) {
            $this->logger->debug(sprintf('[基础]客户欢迎语未发送，获取欢迎语为空，客户id: %s', (string) $contact['id']));
            return;
        }

        $this->logger->debug(sprintf('[基础]客户欢迎语匹配成功，即将发送，客户id: %s', (string) $contact['id']));

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
        // 已经发送过不再发送
        // 其他欢迎语可能会未设置或是不生效，也需要由通用欢迎语发送
        if ($this->workContactService->getWelcomeStatus((int) $contact['id']) === 1) {
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
     * @return array
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
