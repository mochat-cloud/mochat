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
use App\QueueService\WorkContactTag\UpdateApply;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 编辑客户标签.
 *
 * Class UpdateLogic
 */
class UpdateLogic
{
    /**
     * @var UpdateApply
     */
    protected $service;

    /**
     * @var StoreApply
     */
    protected $storeService;

    /**
     * @Inject
     * @var WorkContactTagServiceInterface
     */
    private $contactTagService;

    /**
     * @Inject
     * @var WorkContactTagGroupServiceInterface
     */
    private $contactTagGroupService;

    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(array $params)
    {
        //若修改了信息做校验
        if ($params['isUpdate'] == 1) {
            //查询要修改的分组下是否已存在相同标签名称
            $info = $this->contactTagService
                ->getWorkContactTagsByNamesGroupId([$params['tagName']], $params['groupId']);

            if (! empty($info)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '该分组下已存在相同标签名');
            }
        }

        //查询该标签对应的wx_contact_tag_id和编辑之前的分组id
        $tagInfo = $this->contactTagService->getWorkContactTagById((int) $params['tagId'], ['wx_contact_tag_id', 'contact_tag_group_id']);
        if (empty($tagInfo)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '查询不到该标签信息');
        }

//        if ($tagInfo['contactTagGroupId'] != 0) {
//            //查询该标签原分组的wx_group_id
//            $group = $this->contactTagGroupService->getWorkContactTagGroupById((int) $tagInfo['contactTagGroupId'], ['wx_group_id']);
//            if (empty($group)) {
//                throw new CommonException(ErrorCode::SERVER_ERROR, '查询不到该标签分组信息');
//            }
//        }

        //编辑标签
        $data = [
            'contact_tag_group_id' => $params['groupId'],
            'name'                 => $params['tagName'],
        ];
        $res = $this->contactTagService->updateWorkContactTagById((int) $params['tagId'], $data);
        if (! is_int($res)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '客户标签编辑失败');
        }

        // ------ 同步企业微信 -------
        $this->service = make(UpdateApply::class);
        //若不是将分组改为未分组 编辑企业微信
        if ($params['groupId'] != 0) {
            //若没有修改分组
            if ($params['groupId'] == $tagInfo['contactTagGroupId']) {
                //编辑企业微信标签名称
                $params = [
                    'id'     => $tagInfo['wxContactTagId'],
                    'name'   => $params['tagName'],
                    'corpId' => user()['corpIds'][0],
                ];
                $this->service->handle($params);
            } else {
                //若原分组不是未分组
                if (! empty($tagInfo['wxContactTagId'])) {
                    //删除企业微信中原分组下的该标签
                    $deleteParams = [
                        'tag_id'   => [$tagInfo['wxContactTagId']],
                        'group_id' => [],
                    ];

                    $this->service->delete($deleteParams, [$tagInfo['contactTagGroupId']], user()['corpIds'][0]);
                }
                //查询要修改的分组是否有 wx_group_id
                $groupInfo = $this->contactTagGroupService->getWorkContactTagGroupById((int) $params['groupId'], ['wx_group_id', 'group_name']);

                //如果有 将该标签新增到该分组下
                if (! empty($groupInfo['wxGroupId'])) {
                    $addParams = [
                        'group_id' => $groupInfo['wxGroupId'],
                        'tag'      => [
                            [
                                'name' => $params['tagName'],
                            ],
                        ],
                    ];
                } else {  //如果没有 新建分组并将标签新增到该分组下
                    $addParams = [
                        'group_name' => $groupInfo['groupName'],
                        'tag'        => [
                            [
                                'name' => $params['tagName'],
                            ],
                        ],
                    ];
                }

                //添加到企业微信 同时同步企业微信标签分组
                $this->storeService = make(StoreApply::class);
                $this->storeService->handle($addParams, $params['groupId'], user()['corpIds'][0]);
            }
        } else { //若是改为未分组 删除原分组下的该标签
            $deleteParams = [
                'tag_id'   => [$tagInfo['wxContactTagId']],
                'group_id' => [],
            ];

            $this->service->delete($deleteParams, [$tagInfo['contactTagGroupId']], user()['corpIds'][0]);
        }
    }
}
