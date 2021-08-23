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
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Medium\Constants\Type as MediumType;
use MoChat\App\Utils\Media;
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
use MoChat\Plugin\Greeting\Logic\ContactCallBackLogic as GreetingLogic;
use MoChat\Plugin\RoomAutoPull\Logic\ContactCallBackLogic as AutoPullLogic;
use MoChat\Plugin\RoomSop\Service\RoomSopService;
use MoChat\Plugin\WorkFission\Logic\ContactCallBackLogic as FissionLogic;
use Psr\EventDispatcher\EventDispatcherInterface;
use MoChat\App\WorkContact\Event\AddContactEvent;

/**
 * 添加客户 - 回调.
 */
class StoreCallback
{
    use AppTrait;

    /**
     * @Inject
     * @var RoomSopService
     */
    protected $roomSopService;

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
     * @Inject
     * @var WorkContactContract
     */
    private $workContactService;

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
     * @Inject
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @Inject()
     * @var Media
     */
    protected $media;

    /**
     * @param array $wxData 微信回调数据
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
                    $sendWelcomeData['image']['media_id'] = $this->media->uploadImage($wxResponse['ToUserName'], $content['medium']['mediumContent']['imagePath']);
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
                    $mediaId = $this->media->uploadImage($wxResponse['ToUserName'], $content['medium']['mediumContent']['imagePath']);
                    $sendWelcomeData['miniprogram'] = [
                        'title'        => $content['medium']['mediumContent']['title'],
                        'pic_media_id' => $mediaId,
                        'appid'        => $content['medium']['mediumContent']['appid'],
                        'page'         => $content['medium']['mediumContent']['page'],
                    ];
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

        $workContactEmployeeService = make(WorkContactEmployeeContract::class);
        ## 查询当前公司是否存在此客户
        $sysContact = $this->workContactService->getWorkContactByCorpIdWxExternalUserId($corpId, $wxResponse['ExternalUserID'], ['id']);
        ## 开启事物
        Db::beginTransaction();
        ## 记录客户被操作事件轨迹日志
        $contactEmployeeTrackData = [];

        try {
            if (! empty($sysContact)) {
                $isNewContact = 0;
                $contactId = (int) $sysContact['id'];
            } else {
                ## 组织客户表入表数据
                $isNewContact = 1;
                $createContractData = [
                    'corp_id'            => $corpId,
                    'wx_external_userid' => $wxResponse['ExternalUserID'],
                    'name'               => $wxContact['name'],
                    'avatar'             => ! empty($wxContact['avatar']) ? $wxContact['avatar'] : '',
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
                $contactId = $this->workContactService->createWorkContact($createContractData);
            }
            
            // 触发添加客户事件
            $state = isset($wxResponse['State']) ? $wxResponse['State'] : '';
            $welcomeCode = isset($wxResponse['WelcomeCode']) ? $wxResponse['WelcomeCode'] : '';
            $this->triggerAddContactEvent((int) $contactId, (int)$employeeId, (string)$state, $isNewContact, $welcomeCode);
            
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

    private function triggerAddContactEvent(int $contactId, int $employeeId, string $state, int $isNewContact, string $welcomeCode)
    {
        $contact = $this->workContactService->getWorkContactById($contactId);
        if (!empty($contact)) {
            $contact['employeeId'] = $employeeId;
            $contact['state'] = $state;
            $contact['isNew'] = $isNewContact;
            $contact['welcomeCode'] = $welcomeCode;
            $this->eventDispatcher->dispatch(new AddContactEvent($contact));
        }
    }
}
