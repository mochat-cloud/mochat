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
use MoChat\App\WorkRoom\Event\CreateRoomEvent;
use MoChat\App\WorkRoom\Event\CreateRoomMemberEvent;
use MoChat\App\WorkRoom\Event\DeleteRoomMemberEvent;
use MoChat\App\WorkRoom\Event\DismissRoomEvent;
use MoChat\App\WorkRoom\Event\UpdateRoomEvent;
use MoChat\App\WorkRoom\Event\UpdateRoomMemberEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * 客户群管理-微信客户群数据同步.
 *
 * Class WxSyncLogic
 */
class WxSyncLogic
{
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
     * @Inject
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

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
        //事件分发
        $this->triggerRoomEvent(
            (int) $corpId,
            $roomCreateData,
            $roomUpdateData,
            $deleteRoomIdArr,
            $contactRoomCreateData,
            $contactRoomUpdateData,
            $deleteContactRoomIdArr
        );
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
            }
            ## 客户成员-更新数据
            empty($contactRoomUpdateData) || $this->workContactRoomService->batchUpdateByIds($contactRoomUpdateData);
            ## 客户成员-删除数据
            empty($deleteContactRoomIdArr) || $this->workContactRoomService->updateWorkContactRoomByIds($deleteContactRoomIdArr, [
                'status' => WorkContactRoomStatus::QUIT,
                'out_time' => date('Y-m-d H:i:s'),
            ]);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '微信请求或回调更新客户群信息失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
        }
    }

    /**
     * 事件分发
     * @param int $corpId
     * @param array $roomCreateData
     * @param array $roomUpdateData
     * @param array $deleteRoomIdArr
     * @param array $contactRoomCreateData
     * @param array $contactRoomUpdateData
     * @param array $deleteContactRoomIdArr
     */
    private function triggerRoomEvent(
        int $corpId,
        array $roomCreateData,
        array $roomUpdateData,
        array $deleteRoomIdArr,
        array $contactRoomCreateData,
        array $contactRoomUpdateData,
        array $deleteContactRoomIdArr
    ) {
        if (! empty($roomCreateData)) {
            go(function () use ($corpId, $roomCreateData) {
                $this->eventDispatcher->dispatch(new CreateRoomEvent([
                    'corpId' => $corpId,
                    'rooms' => $roomCreateData,
                ]));
            });
        }

        if (! empty($roomUpdateData)) {
            go(function () use ($corpId, $roomUpdateData) {
                $this->eventDispatcher->dispatch(new UpdateRoomEvent([
                    'corpId' => $corpId,
                    'rooms' => $roomUpdateData,
                ]));
            });
        }

        if (! empty($deleteRoomIdArr)) {
            go(function () use ($corpId, $deleteRoomIdArr) {
                $this->eventDispatcher->dispatch(new DismissRoomEvent([
                    'corpId' => $corpId,
                    'rooms' => $deleteRoomIdArr,
                ]));
            });
        }

        if (! empty($contactRoomCreateData)) {
            go(function () use ($corpId, $contactRoomCreateData) {
                $this->eventDispatcher->dispatch(new CreateRoomMemberEvent([
                    'corpId' => $corpId,
                    'rooms' => $contactRoomCreateData,
                ]));
            });
        }

        if (! empty($contactRoomUpdateData)) {
            go(function () use ($corpId, $contactRoomUpdateData) {
                $this->eventDispatcher->dispatch(new UpdateRoomMemberEvent([
                    'corpId' => $corpId,
                    'rooms' => $contactRoomUpdateData,
                ]));
            });
        }

        if (! empty($deleteContactRoomIdArr)) {
            go(function () use ($corpId, $deleteContactRoomIdArr) {
                $this->eventDispatcher->dispatch(new DeleteRoomMemberEvent([
                    'corpId' => $corpId,
                    'rooms' => $deleteContactRoomIdArr,
                ]));
            });
        }
    }
}
