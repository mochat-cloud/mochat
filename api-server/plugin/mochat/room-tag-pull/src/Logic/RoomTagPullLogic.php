<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomTagPull\Logic;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Plugin\RoomTagPull\Contract\RoomTagPullContactContract;
use MoChat\Plugin\RoomTagPull\Contract\RoomTagPullContract;

class RoomTagPullLogic
{
    use AppTrait;

    /**
     * @Inject
     * @var RoomTagPullContract
     */
    protected $roomTagPullService;

    /**
     * @Inject
     * @var RoomTagPullContactContract
     */
    protected $roomTagPullContactService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function handle()
    {
        try {
            $corps = $this->corpService->getCorps(['id']);
            foreach ($corps as $item) {
                $corp = $this->corpService->getCorpById($item['id']);
                $this->groupMsgTask($corp);
                $this->groupMsgSendResult($corp);
            }
            $this->logger->info('标签建群获取企业群发成员执行结果完成' . date('Y-m-d H:i:s', time()));
        } catch (\Exception $e) {
            $this->logger->error(sprintf('%s [%s] %s', '标签建群获取企业群发成员执行结果失败', date('Y-m-d H:i:s'), $e->getMessage()));
        }
    }

    /**
     * 处理企业群发成员执行结果.
     * @throws \JsonException
     */
    private function groupMsgSendResult(array $corp): void
    {
        $app = $this->wxApp($corp['id'], 'contact')->external_contact_message;
        $roomTagPull = $this->roomTagPullService->getRoomTagPullByCorpId($corp['id'], ['id', 'wx_tid']);
        foreach ($roomTagPull as $wxTid) {
            if (empty($wxTid['wxTid'])) {
                continue;
            }
            ## 过滤完成邀请
            if (empty($this->roomTagPullContactService->getRoomTagPullContactByRoomTagPullIdSendStatus($wxTid['id'], 0, ['id']))) {
                continue;
            }
            foreach (json_decode($wxTid['wxTid'], true, 512, JSON_THROW_ON_ERROR) as $tid) {
                $res = $app->httpPostJson('cgi-bin/externalcontact/get_groupmsg_send_result', [
                    'msgid' => $tid['tid'],
                    'userId' => $tid['wxUserId'],
                    'limit' => 500,
                    'cursor' => '',
                ]);
                $result = $res['data'];
                if ($result['errcode'] !== 0 || empty($result['send_list'])) {
                    continue;
                }
                foreach ($result['send_list'] as $send) {
                    $this->roomTagPullContactService->updateRoomTagPullContactByRoomTagPullIdUseridExternalUserid($wxTid['id'], $send['userid'], $send['external_userid'], (int) $send['status']);
                }
            }
        }
    }

    /**
     * 处理企业群发成员发送结果.
     * @throws \JsonException
     */
    private function groupMsgTask(array $corp): void
    {
        $app = $this->wxApp($corp['id'], 'contact')->external_contact_message;
        $roomTagPull = $this->roomTagPullService->getRoomTagPullByCorpId($corp['id'], ['id', 'wx_tid']);
        foreach ($roomTagPull as $v) {
            if (empty($v['wxTid'])) {
                continue;
            }
            $wxTid = json_decode($v['wxTid'], true, 512, JSON_THROW_ON_ERROR);
            foreach ($wxTid as $k => $tid) {
                ## 过滤完成发送
                if ($tid['status'] === 1) {
                    continue;
                }
                $res = $app->httpPostJson('cgi-bin/externalcontact/get_groupmsg_task', [
                    'msgid' => $tid['tid'],
                    'limit' => 500,
                    'cursor' => '',
                ]);
                $result = $res['data'];
                if ($result['errcode'] !== 0 || empty($result['task_list'])) {
                    continue;
                }
                foreach ($result['task_list'] as $task) {
                    $wxTid[$k]['status'] = $task['status'];
                }
            }
            $this->roomTagPullService->updateRoomTagPullById($v['id'], ['wx_tid' => json_encode($wxTid, JSON_THROW_ON_ERROR)]);
        }
    }
}
