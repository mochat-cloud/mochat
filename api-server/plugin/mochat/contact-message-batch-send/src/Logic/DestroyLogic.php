<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactMessageBatchSend\Logic;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendContract;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendEmployeeContract;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendResultContract;

class DestroyLogic
{
    /**
     * @Inject
     * @var ContactMessageBatchSendContract
     */
    private $contactMessageBatchSend;

    /**
     * @Inject
     * @var ContactMessageBatchSendEmployeeContract
     */
    private $contactMessageBatchSendEmployee;

    /**
     * @Inject
     * @var ContactMessageBatchSendResultContract
     */
    private $contactMessageBatchSendResult;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @param array $params 请求参数
     * @param int $userId 当前登录用户信息
     * @return bool 响应数组
     */
    public function handle(array $params, int $userId): bool
    {
        $batch = $this->contactMessageBatchSend->getContactMessageBatchSendById((int) $params['batchId']);
        if (! $batch) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未找到记录');
        }
        if ($batch['userId'] != $userId) {
            throw new CommonException(ErrorCode::ACCESS_DENIED, '无操作权限');
        }
        Db::beginTransaction();
        try {
            $this->contactMessageBatchSend->deleteContactMessageBatchSend($batch['id']);
            $this->contactMessageBatchSendEmployee->deleteContactMessageBatchSendEmployeesByBatchId($batch['id']);
            $this->contactMessageBatchSendResult->deleteContactMessageBatchSendResultsByBatchId($batch['id']);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '客户消息删除失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '客户消息删除失败');
        }
        return true;
    }
}
