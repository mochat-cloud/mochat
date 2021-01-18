<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkContactTag;

use App\Contract\WorkContactTagGroupServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\QueueService\WorkContactTag\StoreApply;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 创建客户标签.
 *
 * Class StoreLogic
 */
class StoreLogic
{
    /**
     * @var StoreApply
     */
    protected $service;

    /**
     * @Inject
     * @var WorkContactTagGroupServiceInterface
     */
    private $contactTagGroupService;

    /**
     * @Inject
     * @var WorkContactTagServiceInterface
     */
    private $contactTagService;

    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(array $params)
    {
        $this->service = make(StoreApply::class);

        $corpId = user()['corpIds'];
        if (count($corpId) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请先选择企业');
        }

        $data = [];
        $tag  = [];
        foreach ($params['tagName'] as $val) {
            $data[] = [
                'corp_id'              => user()['corpIds'][0],
                'contact_tag_group_id' => $params['groupId'],
                'name'                 => $val,
            ];

            $tag[] = [
                'name' => $val,
            ];
        }
        //查询该分组下是否已存在相同标签名称
        $info = $this->contactTagService
            ->getWorkContactTagsByNamesGroupId($params['tagName'], $params['groupId']);

        if (! empty($info)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '该分组下已存在相同标签名');
        }

        $res = $this->contactTagService->createWorkContactTags($data);

        if ($res != true) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '客户标签创建失败');
        }

        //若不是未分组
        if ($params['groupId'] != 0) {
            //根据分组id查询微信分组id、分组名
            $groupInfo = $this->contactTagGroupService->getWorkContactTagGroupById((int) $params['groupId'], ['wx_group_id', 'group_name']);
            if (! empty($groupInfo['wxGroupId'])) {
                $addParams = [
                    'group_id' => $groupInfo['wxGroupId'],
                    'tag'      => $tag,
                ];
            } else {
                $addParams = [
                    'group_name' => $groupInfo['groupName'],
                    'tag'        => $tag,
                ];
            }

            //添加到企业微信 同时同步企业微信标签分组
            $this->service->handle($addParams, $params['groupId'], user()['corpIds'][0]);
        }
    }
}
