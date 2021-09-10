<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkMessage\Fetcher;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\SyncData\Contract\DataFetcherInterface;
use MoChat\App\WorkMessage\Contract\WorkMessageConfigContract;
use MoChat\App\WorkMessage\Contract\WorkMessageContract;
use MoChat\App\WorkMessage\Queue\MessageReceivedRawQueue;
use MoChat\App\WorkMessage\Utils\MessageArchiveFactory;
use MoChat\WeWorkFinanceSDK\WxFinanceSDK;

class MessageDataFetcher implements DataFetcherInterface
{
    /**
     * 每次获取消息数量.
     */
    public const MESSAGE_LIMIT = 100;

    /**
     * @var int
     */
    protected $corpId;

    /**
     * @var WxFinanceSDK
     */
    protected $sdk;

    /**
     * @Inject
     * @var WorkMessageConfigContract
     */
    protected $messageConfigService;

    /**
     * @var WorkMessageContract
     */
    protected $workMessageService;

    /**
     * @Inject
     * @var MessageReceivedRawQueue
     */
    protected $messageReceivedRaw;

    /**
     * @Inject
     * @var MessageArchiveFactory
     */
    protected $messageArchiveFactory;

    /**
     * @var MessageDataFetcher[]
     */
    protected static $dataFetchers = [];

    public function __construct($corpId = null)
    {
        if (! empty($corpId)) {
            $this->init((int) $corpId);
        }
    }

    public static function get(int $id): MessageDataFetcher
    {
        if (isset(static::$dataFetchers[$id]) && static::$dataFetchers[$id] instanceof MessageDataFetcher) {
            return static::$dataFetchers[$id];
        }

        return static::$dataFetchers[$id] = new MessageDataFetcher($id);
    }

    public function getDataSource(): array
    {
        $dataSources = $this->messageConfigService->getWorkMessageConfigsByDoneStatus(['corp_id']);
        if (empty($dataSources)) {
            return [];
        }

        return array_column($dataSources, 'corpId');
    }

    public function pull(int $limit = self::MESSAGE_LIMIT): array
    {
        $messages = $this->sdk->getDecryptChatData($this->getLastSeq(), $limit);

        if (empty($messages)) {
            return $messages;
        }

        $maxSeq = $this->getMaxSeq($messages);
        $this->setLastSeq($maxSeq);
        return $messages;
    }

    public function syncData(array $data)
    {
        if (! empty($data)) {
            $this->messageReceivedRaw->handle($this->corpId, $data);
        }
    }

    private function init(int $corpId)
    {
        $this->corpId = $corpId;
        $this->sdk = $this->messageArchiveFactory->get($corpId);
        $this->workMessageService = make(WorkMessageContract::class, [$corpId]);
    }

    private function setLastSeq(int $seq)
    {
        $this->workMessageService->updateWorkMessageLastSeqByCorpId($this->corpId, $seq);
        return $this;
    }

    private function getLastSeq()
    {
        return $this->workMessageService->getWorkMessageLastSeqByCorpId($this->corpId);
    }

    private function getMaxSeq(array $data): int
    {
        return (int) max(array_column($data, 'seq'));
    }
}
