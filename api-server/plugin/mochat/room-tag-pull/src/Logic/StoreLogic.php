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
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Utils\File;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\RoomTagPull\Action\Dashboard\Traits\UpdateTrait;
use MoChat\Plugin\RoomTagPull\Contract\RoomTagPullContactContract;
use MoChat\Plugin\RoomTagPull\Contract\RoomTagPullContract;
use Psr\Container\ContainerInterface;

/**
 * 标签建群-增加.
 *
 * Class StoreLogic
 */
class StoreLogic
{
    use UpdateTrait;
    use AppTrait;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @Inject
     * @var RoomTagPullContract
     */
    protected $roomTagPullService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

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
     * @var WorkContactRoomContract
     */
    protected $workContactRoomService;

    /**
     * @Inject
     * @var RoomTagPullContactContract
     */
    protected $roomTagPullContactService;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理参数
        $params = $this->handleParam($user, $params);
        ## 创建标签
        return $this->createRoomTagPull($user, $params);
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParam(array $user, array $params): array
    {
        $rooms = $this->handleRooms($user, $params['rooms']);
        $sendRes = $this->sendMsg($user, $rooms, $params['guide'], $params['employees'], $params['choose_contact'], (int) $params['filter_contact']);
        ## 基本信息
        return [
            'name' => $params['name'],
            'employees' => implode(',', $params['employees']),
            'choose_contact' => json_encode($params['choose_contact'], JSON_THROW_ON_ERROR),
            'guide' => $params['guide'],
            'rooms' => json_encode($rooms),
            'filter_contact' => (int) $params['filter_contact'],
            'contact_num' => $this->filterContact((int) $params['filter_contact'], $user, $rooms, $params['employees'], $params['choose_contact']),
            'wx_tid' => json_encode($sendRes),
            'tenant_id' => isset($params['tenant_id']) ? $params['tenant_id'] : 0,
            'corp_id' => $user['corpIds'][0],
            'create_user_id' => $user['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * 群二维码上传微信素材库.
     */
    private function handleRooms(array $user, array $rooms): array
    {
        foreach ($rooms as $key => $room) {
            $rooms[$key]['image'] = File::uploadBase64Image($room['image'], 'image/roomTagPull/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
            $localFile = File::download(file_full_url($rooms[$key]['image']), $rooms[$key]['image']);
            $res = $this->wxApp($user['corpIds'][0], 'contact')->media->uploadImg($localFile);
            if ($res['errcode'] === 0) {
                $rooms[$key]['wx_image'] = $res['url'];
            } else {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '上传图片失败');
            }
        }
        return $rooms;
    }

    /**
     * 发送消息.
     */
    private function sendMsg(array $user, array $rooms, string $text, array $employees, array $chooseContact, int $filter_contact): array
    {
        ##邀请客户
        $sendRes = [];
        ##EasyWeChat添加企业群发消息模板.
        $wx = $this->wxApp($user['corpIds'][0], 'contact')->external_contact_message;
        ## 员工
        foreach ($employees as $emp) {
            $employee = $this->workEmployeeService->getWorkEmployeeById((int) $emp, ['wx_user_id']);
            ## 员工客户
            $contact = $this->workContactService->getWorkContactsByEmployeeIdSearch($user['corpIds'][0], (int) $emp, $chooseContact);
            if (empty($contact)) {
                continue;
            }
            $start = 0;
            for ($i = 0; $i <= 100; ++$i) {
                if (! isset($rooms[$i]) || ! isset($rooms[$i]['num'])) {
                    continue;
                }

                $sendContact = array_slice($contact, $start, $rooms[$i]['num']);
                if (empty($sendContact)) {
                    break;
                }

                $roomContact = $this->workContactRoomService->getWorkContactRoomsByRoomIdContact((int) $rooms[$i]['id'], ['contact_id']);
                foreach ($sendContact as $k => $v) {
                    if (in_array($v['id'], array_column($roomContact, 'contactId'), true)) {
                        $sendContact[$k]['is_join_room'] = 1;
                        ## 已在群聊中的客户将不会收到邀请
                        if ($filter_contact === 1) {
                            unset($sendContact[$k]);
                        }
                    }
                }
                $easyWeChatParams['text']['content'] = $text;
                $easyWeChatParams['image'] = ['pic_url' => $rooms[$i]['wx_image']];
                $easyWeChatParams['external_userid'] = array_unique(array_column($contact, 'wxExternalUserid'));
                $easyWeChatParams['sender'] = $employee['wxUserId'];
                $res = $wx->submit($easyWeChatParams);
                if ($res['errcode'] !== 0) {
                    throw new CommonException(ErrorCode::INVALID_PARAMS, '发送失败');
                }
                $sendRes[] = [
                    'wxUserId' => $employee['wxUserId'],
                    'tid' => $res['msgid'],
                    'status' => 0,
                ];
                $start += $rooms[$i]['num'];
            }
        }

        return $sendRes;
    }

    /**
     * 创建活动.
     * @param array $params 参数
     * @return array 响应数值
     */
    private function createRoomTagPull(array $user, array $params): array
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建活动
            $id = $this->roomTagPullService->createRoomTagPull($params);
            ## 记录客户数据
            $rooms = json_decode($params['rooms'], true, 512, JSON_THROW_ON_ERROR);
            foreach (explode(',', $params['employees']) as $emp) {
                ## 员工客户
                $contact = $this->workContactService->getWorkContactsBySearch($user['corpIds'][0], [(int) $emp], json_decode($params['choose_contact'], true, 512, JSON_THROW_ON_ERROR));
                $start = 0;
                if (empty($contact)) {
                    continue;
                }
                for ($i = 0; $i <= 100; ++$i) {
                    if (! isset($rooms[$i]) || ! isset($rooms[$i]['num'])) {
                        continue;
                    }

                    $sendContact = array_slice($contact, $start, $rooms[$i]['num']);
                    if (empty($sendContact)) {
                        break;
                    }

                    $roomContact = $this->workContactRoomService->getWorkContactRoomsByRoomIdContact((int) $rooms[$i]['id'], ['contact_id']);
                    foreach ($sendContact as $k => $v) {
                        $sendContact[$k]['room_tag_pull_id'] = $id;
                        $sendContact[$k]['room_id'] = $rooms[$i]['id'];
                        $sendContact[$k]['is_join_room'] = 0;
                        if (in_array($v['contactId'], array_column($roomContact, 'contactId'), true)) {
                            $sendContact[$k]['is_join_room'] = 1;
                            if ($params['filter_contact'] === 1) {
                                unset($sendContact[$k]);
                            }
                        }
                    }
                    $this->roomTagPullContactService->createRoomTagPullContacts($sendContact);
                    $start += $rooms[$i]['num'];
                }
            }

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '标签建群失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
        return [$id];
    }
}
