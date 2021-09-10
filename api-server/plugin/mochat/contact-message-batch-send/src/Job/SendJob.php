<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactMessageBatchSend\Job;

use Hyperf\AsyncQueue\Job;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use MoChat\App\Corp\Utils\WeWorkFactory;
use MoChat\App\Utils\Media;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Plugin\ContactMessageBatchSend\Constants\Status;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendContract;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendEmployeeContract;
use MoChat\Plugin\ContactMessageBatchSend\Contract\ContactMessageBatchSendResultContract;

class SendJob extends Job
{
    public $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function handle()
    {
        $batchId = (int) $this->params['batchId'];
        $contactMessageBatchSendService = make(ContactMessageBatchSendContract::class);
        $contactMessageBatchSendEmployeeService = make(ContactMessageBatchSendEmployeeContract::class);
        $contactMessageBatchSendResultService = make(ContactMessageBatchSendResultContract::class);
        $weWorkFactory = make(WeWorkFactory::class);
        $logger = make(StdoutLoggerInterface::class);

        Db::beginTransaction();
        try {
            $batch = $contactMessageBatchSendService->getContactMessageBatchSendLockById($batchId);
            if (empty($batch) || $batch['sendStatus'] !== Status::NOT_SEND) {
                Db::commit();
                return;
            }

            $this->createSendTaskByParams($batch);

            $weWorkContactApp = $weWorkFactory->getContactApp((int) $batch['corpId']);
            $batchEmployees = $contactMessageBatchSendEmployeeService->getContactMessageBatchSendEmployeesByBatchId($batchId, [], ['id', 'employee_id', 'wx_user_id']);
            $content = $this->formatContent((int) $batch['corpId'], $batch['content']);

            foreach ($batchEmployees as $employee) {
                $content['sender'] = $employee['wxUserId'];
                $externalUserId = $contactMessageBatchSendResultService->getExternalUserIdsByBatchEmployeeId($batchId, $employee['employeeId']);

                // 无客户
                if (empty($externalUserId)) {
                    continue;
                }

                // 最多可传入1万个客户
                $chunkExternalUserId = array_chunk($externalUserId, 10000);
                foreach ($chunkExternalUserId as $newExternalUserId) {
                    $content['external_userid'] = $newExternalUserId;
                    $batchResult = $weWorkContactApp->external_contact_message->submit($content);
                    // 更新状态 TODO fail_list 失败结果处理
                    $contactMessageBatchSendEmployeeService->updateContactMessageBatchSendEmployeeById($employee['id'], [
                        'errCode' => $batchResult['errcode'],
                        'errMsg' => $batchResult['errmsg'],
                        'msgId' => $batchResult['msgid'] ?? '',
                        'receiveStatus' => $batchResult['errcode'] === 0 ? 1 : 2, // 1-已接收 2-接收失败
                    ]);
                }
            }
            // 更新群发状态
            $contactMessageBatchSendService->updateContactMessageBatchSendById($batchId, [
                'sendStatus' => Status::SEND_OK,
                'sendTime' => date('Y-m-d H:i:s'),
            ]);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $logger->error(sprintf('%s [%s] %s', '客户群发消息创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $logger->error($e->getTraceAsString());
        }
    }

    /**
     * 根据参数创建发送任务
     */
    private function createSendTaskByParams(array $sendData)
    {
        $workEmployeeService = make(WorkEmployeeContract::class);
        $workContactEmployeeService = make(WorkContactEmployeeContract::class);
        $contactMessageBatchSendService = make(ContactMessageBatchSendContract::class);
        $contactMessageBatchSendEmployeeService = make(ContactMessageBatchSendEmployeeContract::class);
        $contactMessageBatchSendResultService = make(ContactMessageBatchSendResultContract::class);

        $filterParams = $sendData['filterParams'];
        $corpId = (int) $sendData['corpId'];
        $employeeIds = (array) $sendData['employeeIds'];
        $batchId = (int) $sendData['id'];

        // 获取用户成员
        $employees = $workEmployeeService->getWorkEmployeesByIdCorpIdStatus($corpId, $employeeIds, 1, ['id', 'wx_user_id']);

        $employeeTotal = count($employees);
        $contactTotal = 0;
        foreach ($employees as $employee) {
            // 获取成员客户
            $contacts = $workContactEmployeeService->getWorkContactsByEmployeeIdFilterParams($employee['id'], $filterParams);
            $employeeContactTotal = count($contacts);
            $contactTotal += $employeeContactTotal;

            // 客户
            foreach ($contacts as $contact) {
                $contactMessageBatchSendResultService->createContactMessageBatchSendResult([
                    'batch_id' => $batchId,
                    'employee_id' => $employee['id'],
                    'contact_id' => $contact['id'],
                    'external_user_id' => $contact['wxExternalUserid'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            // 成员
            $contactMessageBatchSendEmployeeService->createContactMessageBatchSendEmployee([
                'batch_id' => $batchId,
                'employee_id' => $employee['id'],
                'wx_user_id' => $employee['wxUserId'],
                'send_contact_total' => $employeeContactTotal,
                'created_at' => date('Y-m-d H:i:s'),
                'last_sync_time' => date('Y-m-d H:i:s'),
            ]);
        }

        // 更新发送数量
        $contactMessageBatchSendService->updateContactMessageBatchSendById($batchId, [
            'sendEmployeeTotal' => $employeeTotal,
            'notSendTotal' => $employeeTotal,
            'sendContactTotal' => $contactTotal,
            'notReceivedTotal' => $contactTotal,
        ]);
    }

    private function formatContent(int $corpId, array $content)
    {
        if (count($content) == 0) {
            return $content;
        }

        $newContent = [
            'chat_type' => 'single',
        ];

        $media = make(Media::class);

        foreach ($content as $item) {
            if (empty($item['msgType'])) {
                continue;
            }

            switch ($item['msgType']) {
                case 'text':
                    $newContent['text']['content'] = $item['content'];
                    break;
                case 'image':
                    $mediaId = $media->uploadImage($corpId, $item['pic_url']);
                    $newContent['attachments'][] = [
                        'msgtype' => $item['msgType'],
                        $item['msgType'] => [
                            'media_id' => $mediaId,
                        ],
                    ];
                    break;
                case 'link':
                    if (! empty($item['pic_url'])) {
                        $item['picurl'] = $item['pic_url'];
                        unset($item['pic_url']);
                    }
                    unset($item['msgType']);
                    $newContent['attachments'][] = [
                        'msgtype' => $item['msgType'],
                        $item['msgType'] => $item,
                    ];
                    break;
                case 'miniprogram':
                    $item['pic_media_id'] = $media->uploadImage($corpId, $item['pic_url']);
                    unset($item['msgType'], $item['pic_url']);

                    $newContent['attachments'][] = [
                        'msgtype' => $item['msgType'],
                        $item['msgType'] => $item,
                    ];
                    break;
            }
        }
        return $newContent;
    }
}
