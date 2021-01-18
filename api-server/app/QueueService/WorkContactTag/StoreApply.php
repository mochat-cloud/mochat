<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\QueueService\WorkContactTag;

use App\Contract\CorpServiceInterface;
use App\Contract\WorkContactTagGroupServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\Logic\WeWork\AppTrait;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\WeWork\WeWork;

/**
 * 企业微信 添加客户标签
 * Class StoreApply.
 */
class StoreApply
{
    use AppTrait;

    /**
     * @var WeWork
     */
    protected $client;

    /**
     * @var WorkContactTagServiceInterface
     */
    private $contactTagService;

    /**
     * @var WorkContactTagGroupServiceInterface
     */
    private $contactTagGroupService;

    /**
     * @param $addParams
     * @param $groupId
     * @param $corpId
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle($addParams, $groupId, $corpId): void
    {
        $this->contactTagService      = make(WorkContactTagServiceInterface::class);
        $this->contactTagGroupService = make(WorkContactTagGroupServiceInterface::class);

        ## 获取企业微信授信信息
        $corp     = make(CorpServiceInterface::class)->getCorpById($corpId, ['id', 'wx_corpid']);
        $ecClient = $this->wxApp($corp['wxCorpid'], 'contact')->external_contact;

        //添加标签
        $res = $ecClient->addCorpTag($addParams);

        if ($res['errcode'] != 0) {
            throw new CommonException($res['errcode'], $res['errmsg']);
        }
        //更新标签wx_contact_tag_id
        foreach ($res['tag_group']['tag'] as $raw) {
            $tagData['wx_contact_tag_id'] = $raw['id'];

            $updateRes = $this->contactTagService->updateWorkContactTagByName($raw['name'], $tagData);
            if (! is_int($updateRes)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '微信标签id修改失败');
            }
        }

        if (isset($addParams['group_name'])) {
            //更新标签分组wx_group_id
            $group['wx_group_id'] = $res['tag_group']['group_id'];
            $updateGroup          = $this->contactTagGroupService
                ->updateWorkContactTagGroupById((int) $groupId, $group);
            if (! is_int($updateGroup)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '微信标签分组id修改失败');
            }
        }
    }
}
