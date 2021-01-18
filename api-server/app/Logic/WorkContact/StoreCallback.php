<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkContact;

use App\Constants\Medium\Type as MediumType;
use App\Constants\WorkContact\AddWay;
use App\Constants\WorkContact\Event;
use App\Contract\ContactEmployeeTrackServiceInterface;
use App\Contract\CorpServiceInterface;
use App\Contract\WorkContactEmployeeServiceInterface;
use App\Contract\WorkContactServiceInterface;
use App\Contract\WorkContactTagPivotServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Logic\ChannelCode\ContactCallBackLogic as ChannelCodeLogic;
use App\Logic\Greeting\ContactCallBackLogic as GreetingLogic;
use App\Logic\WeWork\AppTrait;
use App\Logic\WorkRoomAutoPull\ContactCallBackLogic as AutoPullLogic;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use League\Flysystem\Filesystem;

/**
 * 添加客户 - 回调.
 */
class StoreCallback
{
    use AppTrait;

    /**
     * @var \EasyWeChat\Work\ExternalContact\Client
     */
    protected $contactClient;

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
     * @var WorkContactTagServiceInterface
     */
    private $workContactTagService;

    /**
     * 通讯录 - 客户 - 轨迹互动.
     * @Inject
     * @var ContactEmployeeTrackServiceInterface
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
     */
    public function handle(array $wxData): void
    {
        ## 校验 - 微信回调参数
        if (empty($wxData) || empty($wxData['ToUserName']) || empty($wxData['UserID']) || empty($wxData['ExternalUserID'])) {
            return;
        }
        ## 校验 - 系统数据
        $corp     = make(CorpServiceInterface::class)->getCorpsByWxCorpId($wxData['ToUserName'], ['id']);
        $employee = make(WorkEmployeeServiceInterface::class)->getWorkEmployeeByWxUserId($wxData['UserID'], ['id', 'name']);
        if (empty($corp) || empty($employee)) {
            return;
        }
        ## 拉取微信客户详情
        $this->contactClient = $this->wxApp($wxData['ToUserName'], 'contact')->external_contact;
        $wxContact           = $this->contactClient->get($wxData['ExternalUserID']);
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
                default:
                    $drainageInfo = [
                        'tags'    => [],
                        'content' => [],
                    ];
            }
        } else {
            ## 欢迎语
            $drainageInfo = $this->greetingLogic->getGreeting($wxData['UserID']);
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
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
        $wxTagRes = $this->contactClient->markTags($tagData);
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
        $messageClient  = $this->wxApp($wxResponse['ToUserName'], 'contact')->external_contact_message;
        $sendWelcomeRes = $messageClient->sendWelcome($wxResponse['WelcomeCode'], $sendWelcomeData);
        if ($sendWelcomeRes['errcode'] != 0) {
            ## 记录错误日志
            $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '请求微信上推送新增客户信息失败', date('Y-m-d H:i:s'), json_encode(['WelcomeCode' => $wxResponse['WelcomeCode'], 'sendWelcomeData' => $sendWelcomeData]), json_encode($sendWelcomeRes)));
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

        $workContactService         = make(WorkContactServiceInterface::class);
        $workContactEmployeeService = make(WorkContactEmployeeServiceInterface::class);
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
                $pathFileName = '';
                if (isset($wxContact['avatar']) && ! empty($wxContact['avatar'])) {
                    $filesystem   = make(Filesystem::class);
                    $pathFileName = 'Contact/Avatar/' . time() . '.png';
                    $stream       = file_get_contents($wxContact['avatar'], true);
                    ## 文件上传
                    $filesystem->write($pathFileName, $stream);
                }

                ## 组织客户表入表数据
                $createContractData = [
                    'corp_id'            => $corpId,
                    'wx_external_userid' => $wxResponse['ExternalUserID'],
                    'name'               => $wxContact['name'],
                    'avatar'             => $pathFileName,
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
                $exitTagList                    = make(WorkContactTagPivotServiceInterface::class)->getWorkContactTagPivotsByOtherId((int) $contactId, (int) $employeeId, ['contact_tag_id']);
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
                    make(WorkContactTagPivotServiceInterface::class)->createWorkContactTagPivots($addContactTag);
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
        $data = [
            'code'    => 0,
            'mediaId' => '',
        ];
        $mediaClient  = $this->wxApp($toUserName, 'contact')->media;
        $imageContent = file_get_contents(file_full_url($mediaPath));
        $localPath    = '/tmp/' . time() . '.jpg';
        file_put_contents($localPath, $imageContent, FILE_USE_INCLUDE_PATH);
        $wxMediaRes = $mediaClient->uploadImage($localPath);
        if ($wxMediaRes['errcode'] != 0) {
            ## 记录错误日志
            $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '请求微信上传临时素材失败', date('Y-m-d H:i:s'), json_encode($localPath), json_encode($wxMediaRes)));
            $data['code'] = $wxMediaRes['errcode'];
        } else {
            file_exists($localPath) && unlink($localPath);
            $data['mediaId'] = $wxMediaRes['media_id'];
        }
        return $data;
    }
}
