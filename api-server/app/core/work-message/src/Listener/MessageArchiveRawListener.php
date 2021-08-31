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
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Event\Annotation\Listener;
use MoChat\App\WorkMessage\Event\MessageArchiveRawEvent;
use Psr\Container\ContainerInterface;
use MoChat\App\WorkMessage\Fetcher\MessageDataFetcher;
use MoChat\App\Corp\Contract\CorpContract;

/**
 * 产生会话回调事件
 *
 * @Listener
 */
class MessageArchiveRawListener implements ListenerInterface
{
    /**
     * @Inject()
     * @var ContainerInterface
     */
    protected $container;

    public function listen(): array
    {
        return [
            MessageArchiveRawEvent::class
        ];
    }

    /**
     * @param MessageArchiveRawEvent $event
     */
    public function process(object $event)
    {
        $message = $event->message;
        $corpService = make(CorpContract::class);
        $corp = $corpService->getCorpsByWxCorpId($message['ToUserName'], ['id']);

        if (empty($corp)) {
            return;
        }

        $messageDataFetcher = MessageDataFetcher::get((int)$corp['id']);
        $data = $messageDataFetcher->pull(200);

        if (!empty($data)) {
            $messageDataFetcher->syncData($data);
        }
    }
}