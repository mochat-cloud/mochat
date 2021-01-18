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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 企业微信 添加客户标签回调
 * Class StoreCallBackApply.
 */
class StoreCallBackLogic
{
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
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * 企业id.
     * @var int
     */
    private $corpId;

    public function handle(array $params, array $corp): void
    {
        $this->corpId = $corp['id'];

        //查询表中所有分组
        $allGroup = $this->contactTagGroupService
            ->getWorkContactTagGroupsByCorpId([$this->corpId]);
        if (! empty($allGroup)) {
            $allGroup = array_column($allGroup, null, 'wxGroupId');
        }

        //添加标签组和标签
        $this->addTag($params, $allGroup);
    }

    /**
     * 添加标签组和标签.
     * @param $params
     * @param $allGroup
     */
    private function addTag($params, $allGroup)
    {
        $createTag = [];

        foreach ($params['tag_group'] as &$val) {
            //若表中没值 需新增分组 和对应标签
            if (! isset($allGroup[$val['group_id']])) {
                $data = [
                    'wx_group_id' => $val['group_id'],
                    'corp_id'     => $this->corpId,
                    'group_name'  => $val['group_name'],
                    'order'       => $val['order'],
                ];
                $contactGroupId = $this->contactTagGroupService->createWorkContactTagGroup($data);
                if (! is_int($contactGroupId)) {
                    $this->logger->error('新建标签组回调失败', $data);
                    throw new CommonException(ErrorCode::SERVER_ERROR, '标签分组创建失败');
                }

                //标签
                foreach ($val['tag'] as &$v) {
                    $createTag[] = [
                        'wx_contact_tag_id'    => $v['id'],
                        'corp_id'              => $this->corpId,
                        'name'                 => $v['name'],
                        'order'                => $v['order'],
                        'contact_tag_group_id' => $contactGroupId,
                    ];
                }
                unset($v);
            }
        }
        unset($val);

        //添加标签
        if (! empty($createTag)) {
            $createTagRes = $this->contactTagService->createWorkContactTags($createTag);
            if ($createTagRes != true) {
                $this->logger->error('新建标签组回调失败', $createTag);
                throw new CommonException(ErrorCode::SERVER_ERROR, '新增标签失败');
            }
        }
    }
}
