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
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendContract;
use MoChat\Plugin\ContactMessageBatchSend\Queue\ContactMessageBatchSendQueue;

class StoreLogic
{
    /**
     * @Inject
     * @var ContactMessageBatchSendContract
     */
    private $contactMessageBatchSend;

    /**
     * @Inject
     * @var ContactMessageBatchSendQueue
     */
    private $contactMessageBatchSendQueue;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    private $workRoomService;

    /**
     * @Inject
     * @var WorkContactTagContract
     */
    private $workContactTagService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @param array $params 请求参数
     * @param array $user 当前用户信息
     */
    public function handle(array $params, array $user): bool
    {
        $corpId = $user['corpIds'][0];
        $filterParams = empty($params['filterParams']) ? [] : (array) $params['filterParams'];

        $filterParamsDetail = [
            'gender' => $filterParams['gender'] ?? '',
            'addTimeStart' => $filterParams['addTimeStart'] ?? '',
            'addTimeEnd' => $filterParams['addTimeEnd'] ?? '',
            'rooms' => [],
            'tags' => [],
            'excludeContacts' => [],
        ];

        if (isset($filterParams['gender'])) {
            $filterParamsDetail['gender'] = $filterParams['gender'];
        }

        if (! empty($filterParams['addTimeStart'])) {
            $filterParamsDetail['addTimeStart'] = $filterParams['addTimeStart'];
        }

        if (isset($filterParams['addTimeEnd'])) {
            $filterParamsDetail['addTimeEnd'] = $filterParams['addTimeEnd'];
        }

        if (! empty($filterParams['rooms'])) {
            $filterParamsDetail['rooms'] = $this->workRoomService->getWorkRoomsById($filterParams['rooms'], ['id', 'name']);
        }

        if (! empty($filterParams['tags'])) {
            $filterParamsDetail['tags'] = $this->workContactTagService->getWorkContactTagsById($filterParams['tags'], ['id', 'name']);
        }

        ## 入库
        Db::beginTransaction();
        try {
            $batchContent = $params['content'];
            $employeeIds = ! empty($params['employeeIds']) ? json_encode($params['employeeIds'], JSON_UNESCAPED_UNICODE) : '[]';
            $batchId = $this->contactMessageBatchSend->createContactMessageBatchSend([
                'corp_id' => $corpId,
                'user_id' => $user['id'],
                'user_name' => $user['name'] ?: $user['phone'],
                'employee_ids' => $employeeIds,
                'filter_params' => json_encode($filterParams, JSON_UNESCAPED_UNICODE),
                'filter_params_detail' => json_encode($filterParamsDetail, JSON_UNESCAPED_UNICODE),
                'content' => json_encode($batchContent, JSON_UNESCAPED_UNICODE),
                'send_way' => $params['sendWay'],
                'definite_time' => $params['definiteTime'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $delay = 0;
            if ((int) $params['sendWay'] === 2) {
                $delay = strtotime($params['definiteTime']) - time();
                $delay = $delay < 0 ? 0 : $delay;
            }

            $this->contactMessageBatchSendQueue->push(['batchId' => $batchId], $delay);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '客户消息创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '客户消息创建失败');
        }
        return true;
    }
}
