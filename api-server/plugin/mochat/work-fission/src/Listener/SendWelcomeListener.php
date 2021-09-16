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
use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use MoChat\App\Medium\Constants\Type as MediumType;
use MoChat\App\Utils\Url;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Event\ContactWelcomeEvent;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContactContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionWelcomeContract;

/**
 * 发送欢迎语监听.
 *
 * @Listener(priority=10)
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
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * 任务宝-裂变.
     * @Inject
     * @var WorkFissionContract
     */
    protected $workFissionService;

    /**
     * 任务宝-裂变客户参与.
     * @Inject
     * @var WorkFissionContactContract
     */
    protected $workFissionContactService;

    /**
     * 任务宝-裂变欢迎语.
     * @Inject
     * @var WorkFissionWelcomeContract
     */
    protected $workFissionWelcomeService;

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
            $this->logger->debug(sprintf('[任务宝]客户欢迎语未发送，获取欢迎语为空，客户id: %s', (string) $contact['id']));
            return;
        }

        $this->logger->debug(sprintf('[任务宝]客户欢迎语匹配成功，即将发送，客户id: %s', (string) $contact['id']));

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
        if (! isset($contact['state']) || empty($contact['state'])) {
            return false;
        }

        if (! isset($contact['welcomeCode']) || empty($contact['welcomeCode'])) {
            return false;
        }

        $stateArr = explode('-', $contact['state']);
        if ($stateArr[0] !== $this->getStateName()) {
            return false;
        }

        return true;
    }

    /**
     * 获取来源名称.
     *
     * @return string
     */
    private function getStateName()
    {
        return 'fission';
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
        $stateArr = explode('-', $contact['state']);
        $id = (int) $stateArr[1];

        $data = [];
        $fissionContact = $this->workFissionContactService->getWorkFissionContactById((int) $id, ['fission_id', 'invite_count']);
        if (empty($fissionContact)) {
            return $data;
        }
        $fission = $this->workFissionService->getWorkFissionById((int) $fissionContact['fissionId'], ['id', 'contact_tags', 'tasks', 'end_time']);
        if (empty($fission)) {
            return $data;
        }

        // 欢迎语
        $welcome = $this->workFissionWelcomeService->getWorkFissionWelcomeByFissionId((int) $contact['fissionId']);
        // 欢迎语-文本
        empty($welcome['msgText']) || $data['text'] = $welcome['msgText'];

        $data['medium']['mediumType'] = MediumType::PICTURE_TEXT;
        $data['medium']['mediumContent']['title'] = $welcome['linkTitle'];
        $data['medium']['mediumContent']['description'] = $welcome['linkDesc'];
        $data['medium']['mediumContent']['imagePath'] = $welcome['linkCoverUrl'];
        $data['medium']['mediumContent']['imageLink'] = Url::getAuthRedirectUrl(7, (int) $contact['fissionId']);

        return $data;
    }
}
