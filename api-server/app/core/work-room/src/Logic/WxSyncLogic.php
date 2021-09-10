<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkRoom\Logic;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkContact\Constants\Room\Status as WorkContactRoomStatus;
use MoChat\App\WorkContact\Constants\Room\Type as WorkContactRoomType;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Plugin\AutoTag\Action\Dashboard\Traits\AutoContactTag;
use MoChat\Plugin\AutoTag\Contract\AutoTagContract;
use MoChat\Plugin\AutoTag\Contract\AutoTagRecordContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContactContract;
use MoChat\Plugin\RoomFission\Contract\RoomFissionContract;

/**
 * 客户群管理-微信客户群数据同步.
 *
 * Class WxSyncLogic
 */
class WxSyncLogic
{
    use AutoContactTag;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * @Inject
     * @var WorkContactRoomContract
     */
    protected $workContactRoomService;

    /**
     * @Inject
     * @var RoomFissionContract
     */
    protected $roomFissionService;

    /**
     * @Inject
     * @var RoomFissionContactContract
     */
    protected $roomFissionContactService;

    /**
     * @Inject
     * @var AutoTagContract
     */
    protected $autoTagService;

    /**
     * @Inject
     * @var AutoTagRecordContract
     */
    protected $autoTagRecordService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkContactEmployeeContract
     */
    protected $workContactEmployeeService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @param array $wxRoomList 微信客户群聊列表信息
     * @param int $corpId 企业授信ID
     * @param int $isSingle 是否是单条更新（0-是1-否）
     */
    public function handle(array $wxRoomList, int $corpId = 0, int $isSingle = 0)
    {
        $this->logger->error('客户群回调开始' . date('Y-m-d H:i:s'));
        ## 获取系统中当前企业所有客户群聊列表
        $roomList = $this->getRoomList($wxRoomList, $corpId, $isSingle);
        ## 客户群-新增数据
        $roomCreateData = [];
        ## 客户群-更新数据
        $roomUpdateData = [];
        ## 客户群-删除数据
        $deleteRoomIdArr = [];
        ## 客户成员-新增数据
        $contactRoomCreateData = [];
        ## 客户成员-更新数据
        $contactRoomUpdateData = [];
        ## 客户成员-删除数据
        $deleteContactRoomIdArr = [];
        foreach ($wxRoomList as $wxRoom) {
            if (isset($roomList['roomList'][$wxRoom['chat_id']])) {
                $currentRoom = $roomList['roomList'][$wxRoom['chat_id']];
                unset($roomList['roomList'][$wxRoom['chat_id']]);
                ## 群
                $roomUpdateData[] = [
                    'id' => $currentRoom['id'],
                    'status' => $wxRoom['status'],
                    'name' => ! empty($wxRoom['name']) ? $wxRoom['name'] : '群聊',
                    'owner_id' => isset($roomList['employeeList'][$wxRoom['owner']]) ? $roomList['employeeList'][$wxRoom['owner']] : 0,
                    'notice' => isset($wxRoom['notice']) ? $wxRoom['notice'] : '',
                    'create_time' => date('Y-m-d H:i:s', $wxRoom['create_time']),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                ## 群成员
                foreach ($wxRoom['member_list'] as $item) {
                    if ($item['type'] == WorkContactRoomType::EMPLOYEE) {
                        $contactId = 0;
                        $employeeId = isset($roomList['employeeList'][$item['userid']]) ? $roomList['employeeList'][$item['userid']] : 0;
                    } else {
                        $contactId = isset($roomList['contactList'][$item['userid']]) ? $roomList['contactList'][$item['userid']] : 0;
                        $employeeId = 0;
                    }
                    if (isset($currentRoom['list'][$item['userid']])) {
                        $contactRoomUpdateData[] = [
                            'id' => $currentRoom['list'][$item['userid']]['id'],
                            'contact_id' => $contactId,
                            'employee_id' => $employeeId,
                            'unionid' => isset($item['unionid']) ? $item['unionid'] : '',
                            'join_scene' => $item['join_scene'],
                            'type' => $item['type'],
                            'status' => WorkContactRoomStatus::NORMAL,
                            'join_time' => date('Y-m-d H:i:s', $item['join_time']),
                            'out_time' => '',
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                        unset($currentRoom['list'][$item['userid']]);
                    } else {
                        $contactRoomCreateData[] = [
                            'wx_user_id' => $item['userid'],
                            'contact_id' => $contactId,
                            'employee_id' => $employeeId,
                            'unionid' => isset($item['unionid']) ? $item['unionid'] : '',
                            'room_id' => $currentRoom['id'],
                            'join_scene' => $item['join_scene'],
                            'type' => $item['type'],
                            'status' => WorkContactRoomStatus::NORMAL,
                            'join_time' => date('Y-m-d H:i:s', $item['join_time']),
                            'out_time' => '',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                }
                $deleteContactRoomIdArr = array_merge($deleteContactRoomIdArr, array_column($currentRoom['list'], 'id'));
            } else {
                $roomCreateData[] = [
                    'corp_id' => $corpId,
                    'wx_chat_id' => $wxRoom['chat_id'],
                    'name' => ! empty($wxRoom['name']) ? $wxRoom['name'] : '群聊',
                    'owner_id' => isset($roomList['employeeList'][$wxRoom['owner']]) ? $roomList['employeeList'][$wxRoom['owner']] : 0,
                    'notice' => isset($wxRoom['notice']) ? $wxRoom['notice'] : '',
                    'status' => $wxRoom['status'],
                    'create_time' => date('Y-m-d H:i:s', $wxRoom['create_time']),
                    'room_max' => 200,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
                foreach ($wxRoom['member_list'] as $value) {
                    if ($value['type'] == WorkContactRoomType::EMPLOYEE) {
                        $contactId = 0;
                        $employeeId = isset($roomList['employeeList'][$value['userid']]) ? $roomList['employeeList'][$value['userid']] : 0;
                    } else {
                        $contactId = isset($roomList['contactList'][$value['userid']]) ? $roomList['contactList'][$value['userid']] : 0;
                        $employeeId = 0;
                    }
                    $contactRoomCreateData[] = [
                        'wx_user_id' => $value['userid'],
                        'contact_id' => $contactId,
                        'employee_id' => $employeeId,
                        'unionid' => isset($value['unionid']) ? $value['unionid'] : '',
                        'room_id' => $wxRoom['chat_id'],
                        'join_scene' => $value['join_scene'],
                        'type' => $value['type'],
                        'status' => WorkContactRoomStatus::NORMAL,
                        'join_time' => date('Y-m-d H:i:s', $value['join_time']),
                        'out_time' => '',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                }
            }
        }
        $deleteRoomIdArr = array_merge($deleteRoomIdArr, array_column($roomList['roomList'], 'id'));
        ## 数据入库
        $this->dataIntoDb($roomCreateData, $roomUpdateData, $deleteRoomIdArr, $contactRoomCreateData, $contactRoomUpdateData, $deleteContactRoomIdArr);
    }

    /**
     * @param array $wxRoomList 微信客户群聊列表信息
     * @param int $corpId 企业授信ID
     * @param int $isSingle 是否是单条更新（0-是1-否）
     * @return array 响应数组
     */
    private function getRoomList(array $wxRoomList, int $corpId = 0, int $isSingle = 0): array
    {
        ## 客户群聊列表
        if ($isSingle == 0) {
            $roomList = $this->workRoomService->getWorkRoomsByCorpId($corpId, ['id', 'wx_chat_id', 'owner_id']);
        } else {
            $roomList = $this->workRoomService->getWorkRoomsByWxChatId(array_column($wxRoomList, 'chat_id'), ['id', 'wx_chat_id', 'owner_id']);
        }
        empty($roomList) || $roomList = array_column($roomList, null, 'id');
        ## 客户成员群聊列表
        $contactRoomList = $this->workContactRoomService->getWorkContactRoomsByRoomIds(array_keys($roomList), ['id', 'wx_user_id', 'room_id', 'status']);

        if (! empty($contactRoomList)) {
            foreach ($contactRoomList as $contactRoom) {
                if ($contactRoom['status'] == WorkContactRoomStatus::QUIT) {
                    continue;
                }
                isset($roomList[$contactRoom['roomId']]['list']) || $roomList[$contactRoom['roomId']]['list'] = [];
                $roomList[$contactRoom['roomId']]['list'][$contactRoom['wxUserId']] = $contactRoom;
            }
        }
        empty($roomList) || $roomList = array_column(array_values($roomList), null, 'wxChatId');
        unset($contactRoomList);
        ## 企业通讯录列表
        $employeeList = make(WorkEmployeeContract::class)->getWorkEmployeesByCorpId($corpId, ['id', 'wx_user_id']);
        empty($employeeList) || $employeeList = array_column($employeeList, 'id', 'wxUserId');
        ## 企业客户列表
        $contactList = make(WorkContactContract::class)->getWorkContactsByCorpId($corpId, ['id', 'wx_external_userid']);
        empty($contactList) || $contactList = array_column($contactList, 'id', 'wxExternalUserid');

        return compact('roomList', 'employeeList', 'contactList');
    }

    /**
     * @param array $roomCreateData 客户群-新增数据
     * @param array $roomUpdateData 客户群-更新数据
     * @param array $deleteRoomIdArr 客户群-删除数据
     * @param array $contactRoomCreateData 客户成员-新增数据
     * @param array $contactRoomUpdateData 客户成员-更新数据
     * @param array $deleteContactRoomIdArr 客户成员-删除数据
     */
    private function dataIntoDb(array $roomCreateData, array $roomUpdateData, array $deleteRoomIdArr, array $contactRoomCreateData, array $contactRoomUpdateData, array $deleteContactRoomIdArr)
    {
//        var_dump($roomCreateData, '111111111111');
//        var_dump($roomUpdateData, '222222222222');
//        var_dump($deleteRoomIdArr, '3333333333333');
//        var_dump($contactRoomCreateData, '44444444444444');
//        var_dump($contactRoomUpdateData, '55555555555');
//        var_dump($deleteContactRoomIdArr, '66666666666666');

        //开启事务
        Db::beginTransaction();

        try {
            ## 新建群聊ID数组
            $newRoomList = [];
            ## 客户群-新增数据
            empty($roomCreateData) || $newRoomList = array_map(function ($newRoom) {
                return [
                    'wxChatId' => $newRoom['wx_chat_id'],
                    'roomId' => $this->workRoomService->createWorkRoom($newRoom),
                ];
            }, $roomCreateData);
            empty($newRoomList) || $newRoomList = array_column($newRoomList, 'roomId', 'wxChatId');
            ## 客户群-更新数据
            empty($roomUpdateData) || $this->workRoomService->batchUpdateByIds($roomUpdateData);
            ## 客户群-删除数据
            empty($deleteRoomIdArr) || $this->workRoomService->deleteWorkRooms($deleteRoomIdArr);
            ## 客户成员-新增数据
            if (! empty($contactRoomCreateData)) {
                array_walk($contactRoomCreateData, function (&$data) use ($newRoomList) {
                    is_numeric($data['room_id']) || $data['room_id'] = isset($newRoomList[$data['room_id']]) ? $newRoomList[$data['room_id']] : 0;
                });
                $this->workContactRoomService->createWorkContactRooms($contactRoomCreateData);
                ## 群裂变数据处理
                $this->createRoomFission($contactRoomCreateData);
                ## 自动打标签处理
                $this->autoTagRoom($contactRoomCreateData);
            }
            ## 客户成员-更新数据
            empty($contactRoomUpdateData) || $this->workContactRoomService->batchUpdateByIds($contactRoomUpdateData);
            ## 客户成员-删除数据
            empty($deleteContactRoomIdArr) || $this->workContactRoomService->updateWorkContactRoomByIds($deleteContactRoomIdArr, [
                'status' => WorkContactRoomStatus::QUIT,
                'out_time' => date('Y-m-d H:i:s'),
            ]);
            ## 群裂变数据处理
            empty($deleteContactRoomIdArr) || $this->deleteRoomFission($deleteContactRoomIdArr);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '微信请求或回调更新客户群信息失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
        }
    }

    /**
     * 客户入群处理群裂变数据.
     * @param $contactRoomCreateData
     */
    private function createRoomFission($contactRoomCreateData): void
    {
        foreach ($contactRoomCreateData as $item) {
            if (! empty($item['unionid'])) {
                $contact_record = $this->roomFissionContactService->getRoomFissionContactByRoomIdUnionIdFissionID((int) $item['room_id'], $item['unionid'], 0, ['id', 'fission_id', 'is_new', 'parent_union_id']);
                if (! empty($contact_record)) {
                    $fission = $this->roomFissionService->getRoomFissionById($contact_record['fissionId'], ['target_count', 'new_friend']);
                    if (! empty($fission)) {
                        ## 入群
                        $this->roomFissionContactService->updateRoomFissionContactById($contact_record['id'], ['join_status' => 1]);
                        ## 有师傅 并且（无用户限制活新用户有效）
                        if (! empty($contact_record['parentUnionId']) && ($fission['newFriend'] === 0 || ($fission['newFriend'] === 1 && $contact_record['isNew'] === 1))) {
                            $parent = $this->roomFissionContactService->getRoomFissionContactByRoomIdUnionIdFissionID((int) $item['room_id'], $contact_record['parentUnionId'], $contact_record['fissionId'], ['id', 'invite_count']);
                            $num = $parent['inviteCount'] + 1;
                            $status = $fission['targetCount'] > $num ? 0 : 1;
                            $this->roomFissionContactService->updateRoomFissionContactById($parent['id'], ['invite_count' => $num, 'status' => $status]);
                        }
                    }
                }
            }
        }
    }

    /**
     * 客户退群处理群裂变数据.
     * @param $deleteContactRoomIdArr
     */
    private function deleteRoomFission($deleteContactRoomIdArr): void
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            foreach ($deleteContactRoomIdArr as $item) {
                $contact_room = $this->workContactRoomService->getWorkContactRoomById($item, ['unionid', 'room_id']);
                if (! empty($contact_room['unionid'])) {
                    $contact_record = $this->roomFissionContactService->getRoomFissionContactByRoomIdUnionIdFissionID($contact_room['roomId'], $contact_room['unionid'], 0, ['id', 'fission_id', 'is_new', 'parent_union_id']);
                    if (! empty($contact_record)) {
                        $fission = $this->roomFissionService->getRoomFissionById($contact_record['fissionId'], ['target_count', 'new_friend', 'delete_invalid']);
                        if (! empty($fission)) {
                            ## 流失
                            $this->roomFissionContactService->updateRoomFissionContactById($contact_record['id'], ['loss' => 1]);
                            ## 有师傅并且流失无效
                            if (! empty($contact_record['parentUnionId']) && $fission['deleteInvalid'] === 1) {
                                $parent = $this->roomFissionContactService->getRoomFissionContactByRoomIdUnionIdFissionID($contact_room['roomId'], $contact_record['parentUnionId'], $contact_record['fissionId'], ['id', 'invite_count', 'status', 'receive_status']);
                                $num = $parent['inviteCount'] - 1;
                                $status = $parent['status'];
                                ## 已完成未领取
                                if ($parent['status'] === 1 && $parent['receiveStatus'] === 0) {
                                    $status = 0;
                                }
                                $this->roomFissionContactService->updateRoomFissionContactById($parent['id'], ['invite_count' => $num, 'status' => $status]);
                            }
                        }
                    }
                }
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '客户记录失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
        }
    }

    /**
     * 自动打标签-客户入群行为打标签.
     * @param $contactRoomCreateData
     * @throws \JsonException
     */
    private function autoTagRoom($contactRoomCreateData): array
    {
        $this->logger->error('客户入群行为打标签' . date('Y-m-d H:i:s'));
        $auto_tag = $this->autoTagService->getAutoTagByTypeOnOff(2, 1, ['id', 'tag_rule', 'corp_id']);
        if (empty($auto_tag)) {
            return [];
        }
        foreach ($auto_tag as $auto) {
            ## 2 客户入群群聊id
            foreach ($contactRoomCreateData as $item) {
                // 客户0跳出循环
                $contact_id = (int) $item['contact_id'];
                if ($contact_id === 0) {
                    continue;
                }
                foreach (json_decode($auto['tagRule'], true, 512, JSON_THROW_ON_ERROR) as $key => $tagRule) {
                    $room_ids = array_column($tagRule['rooms'], 'id');
                    $tags = $tagRule['tags'];
                    // 空标签跳出循环
                    if (empty($tags)) {
                        continue;
                    }
                    if (in_array((int) $item['room_id'], $room_ids, true)) {
                        $data = ['contactId' => 0, 'employeeId' => 0, 'tagArr' => array_column($tags, 'tagid'), 'employeeWxUserId' => '', 'contactWxExternalUserid' => ''];
                        ## 客户id
                        $data['contactId'] = $contact_id;
                        ## 员工id
                        $contact_employee = $this->workContactEmployeeService->getWorkContactEmployeeByCorpIdContactId($contact_id, $auto['corpId'], ['employee_id']);
                        $data['employeeId'] = $contact_employee['employeeId'];
                        ## 客户
                        $contact = $this->workContactService->getWorkContactById($contact_id, ['wx_external_userid']);
                        $data['contactWxExternalUserid'] = $contact['wxExternalUserid'];
                        ## 员工
                        $employee = $this->workEmployeeService->getWorkEmployeeById($contact_employee['employeeId'], ['wx_user_id']);
                        $data['employeeWxUserId'] = $employee['wxUserId'];
                        $data['corpId'] = $auto['corpId'];
                        $this->autoTag($data);
                        ## 数据库操作
                        $record = $this->autoTagRecordService->getAutoTagRecordByCorpIdWxExternalUseridAutoTagId($auto['corpId'], $data['contactWxExternalUserid'], $auto['id'], $key + 1, ['id', 'trigger_count']);
                        $trigger_count = empty($record) ? 1 : $record['triggerCount'] + 1;
                        $createMonitors = [
                            'auto_tag_id' => $auto['id'],
                            'contact_id' => $contact_id,
                            'tag_rule_id' => $key + 1,
                            'wx_external_userid' => $data['contactWxExternalUserid'],
                            'employee_id' => $data['employeeId'],
                            'tags' => json_encode(array_column($tags, 'tagname'), JSON_THROW_ON_ERROR),
                            'corp_id' => $auto['corpId'],
                            'trigger_count' => $trigger_count,
                            'status' => 1,
                            'created_at' => date('Y-m-d H:i:s'),
                        ];
                        if (empty($record)) {
                            $this->autoTagRecordService->createAutoTagRecord($createMonitors);
                        } else {
                            $this->autoTagRecordService->updateAutoTagRecordById($record['id'], $createMonitors);
                        }
                    }
                }
            }
        }
        return [];
    }
}
