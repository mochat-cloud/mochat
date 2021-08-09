<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomMessageBatchSend\QueueService;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\Plugin\RoomMessageBatchSend\Constants\Status;
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendContract;
use MoChat\Plugin\RoomMessageBatchSend\Contract\RoomMessageBatchSendEmployeeContract;
use Psr\Container\ContainerInterface;

class StoreApply
{
    use AppTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @AsyncQueueMessage(pool="contact")
     * @param int $batchId 群发ID
     */
    public function handle(int $batchId): void
    {
        Db::beginTransaction();
        try {
            $batch = $this->container->get(RoomMessageBatchSendContract::class)->getRoomMessageBatchSendLockById($batchId);
            if (empty($batch) || $batch['sendStatus'] === Status::SEND_OK) {
                Db::commit();
                return;
            }
            $roomMessageBatchSendEmployee = $this->container->get(RoomMessageBatchSendEmployeeContract::class);
            $app                          = $this->wxApp($batch['corpId'], 'contact')->external_contact_message;
            $batchEmployees               = $roomMessageBatchSendEmployee->getRoomMessageBatchSendEmployeesByBatchId($batchId, [], ['id', 'employee_id', 'wx_user_id', 'content']);
            foreach ($batchEmployees as $employee) {
                $content              = $employee['content'];
                $content['chat_type'] = 'group';
                $content['sender']    = $employee['wxUserId'];
                ## 推送消息
                $batchResult = $app->submit($content);
                ## 更新状态
                $roomMessageBatchSendEmployee->updateRoomMessageBatchSendEmployeeById($employee['id'], [
                    'errCode' => $batchResult['errcode'],
                    'errMsg'  => $batchResult['errmsg'],
                    'msgId'   => $batchResult['msgid'] ?? '',
                ]);
            }
            ## 更新群发状态
            $this->container->get(RoomMessageBatchSendContract::class)->updateRoomMessageBatchSendById($batchId, [
                'sendStatus' => Status::SEND_OK,
                'sendTime'   => date('Y-m-d H:i:s'),
            ]);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->container->get(StdoutLoggerInterface::class)->error(sprintf('%s [%s] %s', '客户群群发消息创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->container->get(StdoutLoggerInterface::class)->error($e->getTraceAsString());
        }
    }
}
