<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkMessage\Listener;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\WorkMessage\Event\MessageArchiveRawEvent;
use MoChat\App\WorkMessage\Event\MessageNotifyRawEvent;
use MoChat\App\WorkMessage\Fetcher\MessageDataFetcher;

/**
 * 产生会话回调事件.
 *
 * @Listener
 */
class MessageArchiveRawListener implements ListenerInterface
{
    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('MessageArchive');
    }

    public function listen(): array
    {
        return [
            MessageArchiveRawEvent::class,
            MessageNotifyRawEvent::class,
        ];
    }

    /**
     * @param MessageArchiveRawEvent $event
     */
    public function process(object $event)
    {
        $message = $event->message;
        $corp = $this->corpService->getCorpsByWxCorpId($message['ToUserName'], ['id', 'name']);

        if (empty($corp)) {
            return;
        }

        $this->logger->debug(sprintf('企业[%s-%s]收到会话存档回调', $corp['id'], $corp['name']));

        $messageDataFetcher = MessageDataFetcher::get((int) $corp['id']);
        while ($data = $messageDataFetcher->pull(200)) {
            $messageDataFetcher->syncData($data);
        }
    }
}
