<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Logic;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use League\Flysystem\Filesystem;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Medium\Constants\Type as MediumType;
use MoChat\App\WorkContact\Constants\AddWay;
use MoChat\App\WorkContact\Constants\Event;
use MoChat\App\WorkContact\Contract\ContactEmployeeTrackContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Plugin\AutoTag\Logic\ContactCallBackLogic as AutoTagLogic;
use MoChat\Plugin\ChannelCode\Logic\ContactCallBackLogic as ChannelCodeLogic;
use MoChat\Plugin\ContactSop\QueueService\ContactSopPush;
use MoChat\Plugin\ContactSop\Service\ContactSopLogService;
use MoChat\Plugin\ContactSop\Service\ContactSopService;
use MoChat\Plugin\Greeting\Logic\ContactCallBackLogic as GreetingLogic;
use MoChat\Plugin\RoomAutoPull\Logic\ContactCallBackLogic as AutoPullLogic;
use MoChat\Plugin\RoomSop\Service\RoomSopService;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContactContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionPushContract;
use MoChat\Plugin\WorkFission\Logic\ContactCallBackLogic as FissionLogic;

/**
 * 添加客户 - 回调.
 */
class StoreCallback
{
    use AppTrait;

    /**
     * @Inject
     * @var ContactSopLogService
     */
    protected $contactSopLogService;

    /**
     * @Inject
     * @var ContactSopService
     */
    protected $contactSopService;

    /**
     * @Inject
     * @var RoomSopService
     */
    protected $roomSopService;

    /**
     * @Inject
     * @var ContactSopPush
     */
    protected $contactSopPush;

    /**
     * @var \EasyWeChat\Work\ExternalContact\Client
     */
    protected $contactService;

    /**
     * @Inject
     * @var FissionLogic
     */
    protected $fissionLogic;

    /**
     * @Inject
     * @var AutoTagLogic
     */
    protected $autoTagLogic;

    /**
     * 自动拉群业务.
     * @Inject
     * @var AutoPullLogic
     */
    private $autoPullLogic;

    /**
     * 渠道码业务.
     * @Inject
     * @var ChannelCodeLogic
     */
    private $channelCodeLogic;

    /**
     * 欢迎语业务.
     * @Inject
     * @var GreetingLogic
     */
    private $greetingLogic;

    /**
     * 标签.
     * @Inject
     * @var WorkContactTagContract
     */
    private $workContactTagService;

    /**
     * 通讯录 - 客户 - 轨迹互动.
     * @Inject
     * @var ContactEmployeeTrackContract
     */
    private $contactEmployeeTrackService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @param array $wxData 微信回调数据
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \League\Flysystem\FileExistsException
     */
    public function handle(array $wxData): void
    {
        $this->logger->info('触发添加好友回调了：' . json_encode($wxData, JSON_THROW_ON_ERROR));

        ## 校验 - 微信回调参数
        if (empty($wxData) || empty($wxData['ToUserName']) || empty($wxData['UserID']) || empty($wxData['ExternalUserID'])) {
            return;
        }
        ## 校验 - 系统数据
        $corp     = make(CorpContract::class)->getCorpsByWxCorpId($wxData['ToUserName'], ['id']);
        $employee = make(WorkEmployeeContract::class)->getWorkEmployeeByWxUserId($wxData['UserID'], ['id', 'name', 'wx_user_id']);
        if (empty($corp) || empty($employee)) {
            return;
        }

        ## 拉取微信客户详情
        $this->contactService = $this->wxApp($wxData['ToUserName'], 'contact')->external_contact;
        $wxContact            = $this->contactService->get($wxData['ExternalUserID']);
        if ($wxContact['errcode'] !== 0) {
            $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '请求企业微信客户群详情错误', date('Y-m-d H:i:s'), $wxData['ExternalUserID'], json_encode($wxContact)));
        }

        ## 个人sop 好友回调倒计时提示恢复客户指定消息
        $list = $this->contactSopService->getContactSopByCorpIdState([$corp['id']]);
//        $this->logger->error('个人sop：' . json_encode($list, JSON_THROW_ON_ERROR));
        $rightList = [];
        foreach ($list as $item) {
            if (in_array($employee['id'], json_decode($item['employeeIds']))) {
                $rightList[] = $item;
            }
        }

        foreach ($rightList as $item) {
            ## lal修改
            $taskList = json_decode($item['setting'], true, 512, JSON_THROW_ON_ERROR);
            foreach ($taskList as $task) {
                $delay = 0;
                if ((int) $task['time']['type'] === 0) {
                    //1时 30分
                    $delay += (int) $task['time']['data']['first'] * 3600;
                    $delay += (int) $task['time']['data']['last'] * 60;
                } else {
                    //1天 11:30
                    $delay += (int) $task['time']['data']['first'] * 86400;
                    $tempTime = explode(':', $task['time']['data']['last']);
                    $delay += (int) $tempTime[0] * 3600 + (int) $tempTime[1] * 60;
                    $delay += (int) $task['time']['data']['last'] * 60;
                }
                ## xrz版本
//            $taskList = json_decode($item['setting']);
//            foreach ($taskList as $task) {
//                $delay = 0;
//                if ($task->time->type == 0) {
//                    //1时 30分
//                    $delay += (int)$task->time->data->first * 3600;
//                    $delay += (int)$task->time->data->last * 60;
//                } else {
//                    //1天 11:30
//                    $delay += (int)$task->time->data->first * 86400;
//                    $tempTime = explode(':', $task->time->data->last);
//                    $delay += (int)$tempTime[0] * 3600 + (int)$tempTime[1] * 60;
//                    $delay += (int)$task->time->data->last * 60;
//                }

                //添加触发记录
                $sopLogId = $this->contactSopLogService->createContactSopLog([
                    'corp_id'        => $corp['id'],
                    'contact_sop_id' => $item['id'],
                    'employee'       => $employee['wxUserId'],
                    'contact'        => $wxContact['external_contact']['external_userid'],
                    'task'           => json_encode($task, JSON_THROW_ON_ERROR),
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);

                //启动倒计时提醒
                $this->contactSopPush->push([
                    'corpId'          => $corp['id'],
                    'contactSopId'    => $item['id'],
                    'contactSopLogId' => $sopLogId,
                    'employeeWxId'    => $employee['wxUserId'],
                    'contactName'     => $wxContact['external_contact']['name'],
                    'sopName'         => $item['name'],
                ], $delay);
            }
        }

        ## 跟进员工筛选
        $followUser     = [];
        $followUserTags = [];
        foreach ($wxContact['follow_user'] as $follow) {
            if ($follow['userid'] == $wxData['UserID']) {
                $followUser                                     = $follow;
                ! isset($followUser['tags']) || $followUserTags = $followUser['tags'];
            }
        }
        ## 【联系我二维码】
        if (isset($wxData['State']) && ! empty($wxData['State'])) {
            $stateArr = explode('-', $wxData['State']);
            switch ($stateArr[0]) {
                case 'workRoomAutoPullId': ## 自动拉群
                    $drainageInfo                                           = $this->autoPullLogic->getWorkRoomAutoPull((int) $stateArr[1]);
                    isset($followUser['add_way']) && $followUser['add_way'] = AddWay::AUTO_GROUP;
                    break;
                case 'channelCode': ## 渠道码
                    $drainageInfo                                           = $this->channelCodeLogic->getChannelCode((int) $stateArr[1]);
                    isset($followUser['add_way']) && $followUser['add_way'] = AddWay::CHANNEL_CODE;
                    break;
                case 'fission':## 企微任务宝 裂变
                    $drainageInfo                                           = $this->fissionLogic->getFission((int) $stateArr[1]);
                    isset($followUser['add_way']) && $followUser['add_way'] = AddWay::FISSION_DRAINAGE;
                    break;
                default:
                    $params       = ['contactWxExternalUserid' => $wxData['ExternalUserID'], 'wxUserId' => $wxData['UserID'], 'corpId' => (int) $corp['id']];
                    $drainageInfo = $this->autoTagLogic->getAutoTag($params);
            }
        } else {
            ## 欢迎语
            $drainageInfo = $this->greetingLogic->getGreeting($wxData['UserID'], $wxData['ExternalUserID'], (int) $corp['id']);
        }
        ## 请求微信 - 客户打标签
        $drainageTags = $drainageInfo['tags'];
        $wxTags       = empty($followUserTags) ? $drainageTags : array_diff($drainageTags, array_column($followUserTags, 'tag_id'));
        $tagRes       = $this->applyWxAddContactTags($wxData, $wxTags);
        ## 请求微信 - 发送欢迎语
        $this->applyWxSendContactMessage($wxData, $wxContact, $drainageInfo['content']);
        ## 客户信息添加
        $this->storeContact(
            $wxData,
            $wxContact['external_contact'],
            $followUser,
            (int) $corp['id'],
            $employee,
            $followUserTags,
            $wxTags,
            $tagRes
        );
        ## 任务宝-裂变用户处理
        $this->handleFission($wxData, $wxContact);
    }

    /**
     * @param array $wxResponse 微信回调数据
     * @param array $wxTags 请求微信添加的客户标签数组
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @return array 响应数组
     */
    private function applyWxAddContactTags(array $wxResponse, array $wxTags): array
    {
        $data = [
            'errcode' => 0,
        ];
        if (empty($wxTags)) {
            return $data;
        }
        $tagData = [
            'userid'          => $wxResponse['UserID'],
            'external_userid' => $wxResponse['ExternalUserID'],
            'add_tag'         => $wxTags,
        ];
        $wxTagRes = $this->contactService->markTags($tagData);
        if ($wxTagRes['errcode'] != 0) {
            ## 记录错误日志
            $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '请求企业微信客户打标签错误', date('Y-m-d H:i:s'), json_encode($tagData), json_encode($wxTagRes)));
            $data['errcode'] = $wxTagRes['errcode'];
        }
        return $data;
    }

    private function applyWxSendContactMessage(array $wxResponse, array $wxContact, array $content)
    {
        if (empty($content)) {
            return true;
        }
        ## 微信消息体
        $sendWelcomeData = [];
        ## 微信消息体 - 文本
        empty($content['text']) || $sendWelcomeData['text']['content'] = str_replace('##客户名称##', $wxContact['external_contact']['name'], $content['text']);
        ## 微信消息体 - 媒体文件
        if (! empty($content['medium'])) {
            ## 组织推送消息数据
            switch ($content['medium']['mediumType']) {
                case MediumType::PICTURE:
                    ## 上传临时素材
                    $wxMediaRes = $this->uploadMedia($wxResponse['ToUserName'], $content['medium']['mediumContent']['imagePath']);
                    if ($wxMediaRes['code'] == 0) {
                        $sendWelcomeData['image']['media_id'] = $wxMediaRes['mediaId'];
                    }
                    break;
                case MediumType::PICTURE_TEXT:
                    $sendWelcomeData['link'] = [
                        'title'  => $content['medium']['mediumContent']['title'],
                        'picurl' => file_full_url($content['medium']['mediumContent']['imagePath']),
                        'desc'   => $content['medium']['mediumContent']['description'],
                        'url'    => $content['medium']['mediumContent']['imageLink'],
                    ];
                    break;
                case MediumType::MINI_PROGRAM:
                    ## 上传临时素材
                    $wxMediaRes = $this->uploadMedia($wxResponse['ToUserName'], $content['medium']['mediumContent']['imagePath']);
                    if ($wxMediaRes['code'] == 0) {
                        $sendWelcomeData['miniprogram'] = [
                            'title'        => $content['medium']['mediumContent']['title'],
                            'pic_media_id' => $wxMediaRes['mediaId'],
                            'appid'        => $content['medium']['mediumContent']['appid'],
                            'page'         => $content['medium']['mediumContent']['page'],
                        ];
                    }
                    break;
            }
        }

        $messageService = $this->wxApp($wxResponse['ToUserName'], 'contact')->external_contact_message;
        $sendWelcomeRes = $messageService->sendWelcome($wxResponse['WelcomeCode'], $sendWelcomeData);
        if ($sendWelcomeRes['errcode'] != 0) {
            ## 记录错误日志
            $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '请求微信上推送新增客户信息失败', date('Y-m-d H:i:s'), json_encode(['WelcomeCode' => $wxResponse['WelcomeCode'], 'sendWelcomeData' => $sendWelcomeData]), $sendWelcomeRes['errmsg']));
        }
    }

    /**
     * @param array $wxResponse 创建客户微信回调数据
     * @param array $wxContact 微信客户详情
     * @param array $followUser 微信客户跟进人信息
     * @param int $corpId 企业授权ID
     * @param array $employee 跟进员工
     * @param array $followUserTags 微信客户跟进人给客户打的标签集合
     * @param array $newAddTags 新增客户标签
     * @param array $tagRes 请求微信给客户打标签的响应结果
     */
    private function storeContact(array $wxResponse, array $wxContact, array $followUser, int $corpId, array $employee, array $followUserTags, array $newAddTags, array $tagRes)
    {
        $employeeId = (int) $employee['id'];

        $workContactService         = make(WorkContactContract::class);
        $workContactEmployeeService = make(WorkContactEmployeeContract::class);
        ## 查询当前公司是否存在此客户
        $sysContact = $workContactService->getWorkContactByCorpIdWxExternalUserId($corpId, $wxResponse['ExternalUserID'], ['id']);
        ## 开启事物
        Db::beginTransaction();
        ## 记录客户被操作事件轨迹日志
        $contactEmployeeTrackData = [];

        try {
            if (! empty($sysContact)) {
                $contactId = (int) $sysContact['id'];
            } else {
                ## 客户头像上传到阿里云
//                $pathFileName = '';
//                if (isset($wxContact['avatar']) && ! empty($wxContact['avatar'])) {
//                    $filesystem   = make(Filesystem::class);
//                    $pathFileName = 'contact/avatar/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg';
//                    $stream       = file_get_contents($wxContact['avatar'], true);
//                    ## 文件上传
//                    $filesystem->write($pathFileName, $stream);
//                }

                ## 组织客户表入表数据
                $createContractData = [
                    'corp_id'            => $corpId,
                    'wx_external_userid' => $wxResponse['ExternalUserID'],
                    'name'               => $wxContact['name'],
                    'avatar'             => ! empty($wxContact['avatar']) ? ! $wxContact['avatar'] : '',
                    'type'               => $wxContact['type'],
                    'gender'             => $wxContact['gender'],
                    'unionid'            => isset($wxContact['unionid']) ? $wxContact['unionid'] : '',
                    'position'           => isset($wxContact['position']) ? $wxContact['position'] : '',
                    'corp_name'          => isset($wxContact['corp_name']) ? $wxContact['corp_name'] : '',
                    'corp_full_name'     => isset($wxContact['corp_full_name']) ? $wxContact['corp_full_name'] : '',
                    'external_profile'   => isset($wxContact['external_profile']) ? json_encode($wxContact['external_profile']) : json_encode([]),
                    'business_no'        => isset($wxContact['business_no']) ? $wxContact['business_no'] : '',
                    'created_at'         => date('Y-m-d H:i:s'),
                    'updated_at'         => date('Y-m-d H:i:s'),
                ];
                $contactId = $workContactService->createWorkContact($createContractData);
            }
            ## 查询当前用户与客户是否存在关联信息
            $workContactEmployee = $workContactEmployeeService->findWorkContactEmployeeByOtherIds($employeeId, $contactId, ['id']);
            if (empty($workContactEmployee)) {
                ## 组织客户与企业用户关联表信息
                $createContractEmployeeData = [
                    'employee_id'      => $employeeId,
                    'contact_id'       => $contactId,
                    'remark'           => isset($followUser['remark']) ? $followUser['remark'] : '',
                    'description'      => isset($followUser['description']) ? $followUser['description'] : '',
                    'remark_corp_name' => isset($followUser['remark_corp_name']) ? $followUser['remark_corp_name'] : '',
                    'remark_mobiles'   => isset($followUser['remark_mobiles']) ? json_encode($followUser['remark_mobiles']) : json_encode([]),
                    'add_way'          => isset($followUser['add_way']) ? $followUser['add_way'] : 0,
                    'oper_userid'      => isset($followUser['oper_userid']) ? $followUser['oper_userid'] : '',
                    'state'            => isset($followUser['state']) ? $followUser['state'] : '',
                    'corp_id'          => $corpId,
                    'status'           => 1, // 正常
                    'create_time'      => isset($followUser['createtime']) ? date('Y-m-d H:i:', $followUser['createtime']) : '',
                    'created_at'       => date('Y-m-d H:i:s'),
                    'updated_at'       => date('Y-m-d H:i:s'),
                ];
                $workContactEmployeeService->createWorkContactEmployee($createContractEmployeeData);
                ## 创建客户事件
                $contactEmployeeTrackData[] = [
                    'employee_id' => $employeeId,
                    'contact_id'  => $contactId,
                    'event'       => Event::CREATE,
                    'content'     => sprintf('客户通过%s添加企业成员【%s】', AddWay::getMessage((int) $followUser['add_way']), $employee['name']),
                    'corp_id'     => $corpId,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ];
            }
            ## 客户标签
            $followUserTags = (empty($newAddTags) || $tagRes['errcode'] != 0) ? $followUserTags : array_merge($followUserTags, array_map(function ($newTag) {
                return [
                    'tag_id' => $newTag,
                    'type'   => 1, // 标签类型:1-企业设置
                ];
            }, $newAddTags));
            if (! empty($followUserTags)) {
                ## 获取客户标签信息
                $tagList = $this->workContactTagService->getWorkContactTagsByCorpIdWxTagId([$corpId], array_column($followUserTags, 'tag_id'), ['id', 'wx_contact_tag_id', 'name']);
                ## 查询当前客户已存在的标签
                $exitTagList                    = make(WorkContactTagPivotContract::class)->getWorkContactTagPivotsByOtherId((int) $contactId, (int) $employeeId, ['contact_tag_id']);
                empty($exitTagList) || $tagList = array_filter($tagList, function ($tag) use ($exitTagList) {
                    if (in_array($tag['id'], array_column($exitTagList, 'contactTagId'))) {
                        return false;
                    }
                    return true;
                });
                if (! empty($tagList)) {
                    $followUserTags = array_column($followUserTags, null, 'tag_id');
                    $addContactTag  = array_map(function ($tag) use ($contactId, $employeeId, $followUserTags) {
                        return [
                            'contact_id'     => $contactId,
                            'employee_id'    => $employeeId,
                            'contact_tag_id' => $tag['id'],
                            'type'           => $followUserTags[$tag['wxContactTagId']]['type'],
                            'created_at'     => date('Y-m-d H:i:s'),
                        ];
                    }, $tagList);
                    make(WorkContactTagPivotContract::class)->createWorkContactTagPivots($addContactTag);
                    $newAddTagNames = array_map(function ($tag) use ($newAddTags) {
                        if (in_array($tag['wxContactTagId'], $newAddTags)) {
                            return '【' . $tag['name'] . '】';
                        }
                    }, $tagList);
                    ## 给客户打标签事件
                    $contactEmployeeTrackData[] = [
                        'employee_id' => $employeeId,
                        'contact_id'  => $contactId,
                        'event'       => Event::TAG,
                        'content'     => sprintf('系统对该客户打标签%s', rtrim(implode('、', $newAddTagNames), '、')),
                        'corp_id'     => $corpId,
                        'created_at'  => date('Y-m-d H:i:s'),
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ];
                }
            }
            ## 记录日志
            empty($contactEmployeeTrackData) || $this->contactEmployeeTrackService->createContactEmployeeTracks($contactEmployeeTrackData);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            ## 记录错误日志
            $this->logger->error(sprintf('%s [%s] %s', '创建客户信息失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
        }
    }

    /**
     * @param string $toUserName 企业微信ID
     * @param string $mediaPath 上传图片阿里云地址
     * @return array 响应数组
     */
    private function uploadMedia(string $toUserName, string $mediaPath): array
    {
        try {
            $data = [
                'code'    => 0,
                'mediaId' => '',
            ];
            $mediaService = $this->wxApp($toUserName, 'contact')->media;
            $imageContent = file_get_contents(file_full_url($mediaPath));
            $localPath    = '/tmp/' . time() . '.jpg';
            file_put_contents($localPath, $imageContent, FILE_USE_INCLUDE_PATH);
            $wxMediaRes = $mediaService->uploadImage($localPath);
            if ($wxMediaRes['errcode'] != 0) {
                ## 记录错误日志
                $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '请求微信上传临时素材失败', date('Y-m-d H:i:s'), json_encode($localPath), json_encode($wxMediaRes)));
                $data['code'] = $wxMediaRes['errcode'];
            } else {
                file_exists($localPath) && unlink($localPath);
                $data['mediaId'] = $wxMediaRes['media_id'];
            }
            return $data;
        } catch (\Throwable $e) {
            ## 记录错误日志
            $this->logger->error(sprintf('%s [%s] %s', '上传媒体文件失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
        } finally {
            file_exists($localPath) && unlink($localPath);
        }
    }

    /**
     * 任务宝-裂变用户数据处理.
     * @param $wxData
     * @param $wxContact
     */
    private function handleFission($wxData, $wxContact): ?bool
    {
        $workContactService        = make(WorkContactContract::class);
        $workFissionContactService = make(WorkFissionContactContract::class);
        $workFissionService        = make(WorkFissionContract::class);
        $workFissionPushService    = make(WorkFissionPushContract::class);

        ## 开启事物
        Db::beginTransaction();
        try {
            if (isset($wxData['State']) && ! empty($wxData['State']) && str_contains($wxData['State'], 'fission')) {
                ## 客户头像上传到阿里云
                $pathFileName = empty($wxContact['external_contact']['avatar']) ? '' : $wxContact['external_contact']['avatar'];
//                if (isset($wxContact['external_contact']['avatar']) && ! empty($wxContact['external_contact']['avatar'])) {
//                    $filesystem   = make(Filesystem::class);
//                    $pathFileName = 'contact/avatar/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg';
//                    $stream       = file_get_contents($wxContact['external_contact']['avatar'], true);
//                    ## 文件上传
//                    $filesystem->write($pathFileName, $stream);
//                }
                $stateArr = explode('-', $wxData['State']);
                if (! empty($stateArr[1])) {
                    $parent = $workFissionContactService->getWorkFissionContactById((int) $stateArr[1], ['id', 'fission_id', 'level', 'invite_count', 'contact_superior_user_parent']);
                    $this->logger->error('parent');
                    if (! empty($parent)) {
                        ## 任务宝-裂变等级
                        $parent_level = $parent['level'];
                        if ($parent['level'] === 0) {//师傅无裂变等级
                            if ($parent['contactSuperiorUserParent'] === 0) {
                                $parent_level = 1;
                            }
                            if ($parent['contactSuperiorUserParent'] > 0) {//有师傅，二级裂变
                                $parent_level = 2;
                                $shizu        = $workFissionContactService->getWorkFissionContactById((int) $parent['contactSuperiorUserParent'], ['id', 'fission_id', 'level']);
                                if ($shizu['contactSuperiorUserParent'] > 0) {//师傅有师傅，三级裂变
                                    $parent_level = 3;
                                    $shizu_plus   = $workFissionContactService->getWorkFissionContactById((int) $shizu['id'], ['id', 'fission_id', 'level']);
                                    if ($shizu_plus['contactSuperiorUserParent'] > 0) {//师祖有师傅，不记录裂变等级
                                        $parent_level = 0;
                                    }
                                }
                            }
                        }
                        ## 查询当前公司是否存在此客户
                        $corp        = make(CorpContract::class)->getCorpsByWxCorpId($wxData['ToUserName'], ['id']);
                        $sysContact  = $workContactService->getWorkContactByCorpIdWxExternalUserId($corp['id'], $wxData['ExternalUserID'], ['id']);
                        $new         = empty($sysContact) ? 1 : 0;
                        $contactInfo = $workFissionContactService->getWorkContactByWxExternalUserIdParent((int) $stateArr[1], $wxData['ExternalUserID']);
                        if (empty($contactInfo)) {
                            $level = $parent_level > 2 ? 0 : $parent_level + 1;
                            if ($parent_level == 0) {
                                $level = 0;
                            }
                            ## 任务宝-裂变用户信息
                            $fissionContact = [
                                'fission_id'                   => $parent['fissionId'],
                                'union_id'                     => isset($wxContact['external_contact']['unionid']) ? $wxContact['external_contact']['unionid'] : '',
                                'nickname'                     => isset($wxContact['external_contact']['name']) ? $wxContact['external_contact']['name'] : '',
                                'avatar'                       => $pathFileName,
                                'contact_superior_user_parent' => (int) $stateArr[1],
                                'level'                        => $level,
                                'employee'                     => $wxData['UserID'],
                                'external_user_id'             => $wxData['ExternalUserID'],
                                'is_new'                       => $new,
                                'loss'                         => 0,
                                'created_at'                   => date('Y-m-d H:i:s'),
                                'updated_at'                   => date('Y-m-d H:i:s'),
                            ];
                            $workFissionContactService->createWorkFissionContact($fissionContact);
                        } else {
                            $workContactService->updateWorkContactById((int) $contactInfo['id'], ['loss' => 0]);
                        }

                        ## 任务宝-裂变用户上级信息
                        $invite_count = $parent['inviteCount']++;
                        $fission      = $workFissionService->getWorkFissionById((int) $parent['fissionId'], ['service_employees', 'tasks', 'end_time', 'new_friend']);
                        if (! ($fission['newFriend'] === 1 && $new === 0)) {
                            $invite_count = $parent['inviteCount'];
                        }//必须新好友才能助力,客户已非新好友
                        $workFissionContactService->updateWorkFissionContactById((int) $parent['id'], ['level' => $parent_level, 'invite_count' => $invite_count, 'employee' => $wxData['UserID']]);

                        ## 裂变成功推送消息
                        $total_count = 0;
                        foreach (json_decode($fission['tasks'], true, 512, JSON_THROW_ON_ERROR) as $key => $val) {
                            $total_count += $val['count'];
                        }
                        if ($total_count === $invite_count) {
                            $workFissionContactService->updateWorkFissionContactById((int) $parent['id'], ['status' => 1]);
                        }
                        if ($total_count === $invite_count && strtotime($fission['endTime']) > time()) {//邀请人数已满，活动有效期内
                            $push = $workFissionPushService->getWorkFissionPushByFissionId((int) $parent['fissionId'], ['push_employee', 'push_contact', 'msg_text', 'msg_complex', 'msg_complex_type']);
                            if ($push['pushEmployee'] === 1) {
                                $this->sendEmployeeMsg($wxData, array_column(json_decode($fission['serviceEmployees'], true, 512, JSON_THROW_ON_ERROR), 'wxUserId'), $push);
                            }
                            if ($push['pushContact'] === 1) {
                                $this->sendContactMsg($wxData, $push);
                            }
                        }
                    }
                }
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            ## 记录错误日志
            $this->logger->error(sprintf('%s [%s] %s', '裂变用户数据处理失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
        }
        return true;
    }

    /**
     * 发送客户.
     * @param $wxResponse
     * @param $push
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function sendContactMsg($wxResponse, $push)
    {
        $sendWelcomeData = [];
        if (! empty($push['msgText'])) {
            $sendWelcomeData['text']['content'] = str_replace('[用户昵称]', '%NICKNAME%', $push['msgText']);
        }
        if (! empty($push['msgComplexType']) && $push['msgComplexType'] === 'image') {
            $image                               = json_decode($push['msgComplex'], true, 512, JSON_THROW_ON_ERROR);
            $sendWelcomeData['image']['pic_url'] = $image['pic_url'];
        }
        if (! empty($push['msgComplexType']) && $push['msgComplexType'] === 'link') {
            $link                    = json_decode($push['msgComplex'], true, 512, JSON_THROW_ON_ERROR);
            $sendWelcomeData['link'] = [
                'title'  => $link['title'],
                'picurl' => $link['pic_url'],
                'desc'   => $link['desc'],
                'url'    => $link['url'],
            ];
        }
        if (! empty($push['msgComplexType']) && $push['msgComplexType'] == 'applets') {
            $applets                        = json_decode($push['msgComplex'], true, 512, JSON_THROW_ON_ERROR);
            $sendWelcomeData['miniprogram'] = [
                'title'        => $applets['title'],
                'pic_media_id' => $applets['pic_url'],
                'appid'        => $applets['appid'],
                'page'         => $applets['path'],
            ];
        }

        $messageService = $this->wxApp($wxResponse['ToUserName'], 'contact')->external_contact_message;
        $sendWelcomeRes = $messageService->sendWelcome($wxResponse['WelcomeCode'], $sendWelcomeData);
        if ($sendWelcomeRes['errcode'] !== 0) {
            ## 记录错误日志
            $this->logger->error(sprintf('%s [%s] %s', '裂变成功推送消息失败', date('Y-m-d H:i:s'), json_encode($sendWelcomeRes, JSON_THROW_ON_ERROR)));
        }
    }

    /**
     * 发送员工.
     * @param $wxResponse
     * @param $employee
     * @param $push
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     */
    private function sendEmployeeMsg($wxResponse, $employee, $push)
    {
        $message = $this->wxApp($wxResponse['ToUserName'], 'contact')->message;
        $content = [];
        if (! empty($pish['msgText'])) {
            $content = [
                'touser'  => implode('|', $employee),
                'msgtype' => 'text',
                'agentid' => (int) 2000003,
                'text'    => [
                    'content' => $push['text'],
                ],
            ];
        }
        if (! empty($push['msgComplex']) && $push['msgComplexType'] == 'image') {
            $image      = json_decode($push['msgComplex'], true);
            $wxMediaRes = $this->uploadMedia($wxResponse['ToUserName'], $image['image']);
            if ($wxMediaRes['code'] !== 0) {
                $this->logger->error(sprintf('%s [%s] %s', '图片上传临时素材失败', date('Y-m-d H:i:s'), json_encode($wxMediaRes)));
            }
            $content = [
                'touser'  => implode('|', $employee),
                'msgtype' => 'image',
                'agentid' => (int) 2000003,
                'image'   => [
                    'media_id' => $wxMediaRes['mediaId'],
                ],
            ];
        }

        if (! empty($push['msgComplex']) && $push['msgComplexType'] == 'link') {
            $link    = json_decode($push['msgComplex'], true);
            $content = [
                'touser'  => implode('|', $employee),
                'msgtype' => 'news',
                'agentid' => (int) 2000003,
                'news'    => [
                    'articles' => [
                        'title'       => $link['title'],
                        'description' => $link['desc'],
                        'url'         => $link['url'],
                        'picurl'      => $link['picUrl'],
                    ],
                ],
            ];
        }

        if (! empty($push['msgComplex']) && $push['msgComplexType'] == 'applets') {
            $applets = json_decode($push['msgComplex'], true);
            $content = [
                'touser'             => implode('|', $employee),
                'msgtype'            => 'miniprogram_notice',
                'agentid'            => (int) 2000003,
                'miniprogram_notice' => [
                    'appid' => $applets['appid'],
                    'page'  => $applets['path'],
                    'title' => $applets['title'],
                ],
            ];
        }
        if (! empty($content)) {
            $ere = $message->message(json_encode($content));
            $this->logger->error(sprintf('%s [%s] %s', '员工失败', date('Y-m-d H:i:s'), json_encode($ere, JSON_THROW_ON_ERROR)));
        }
    }
}
