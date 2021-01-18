<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkContactTagGroup;

use App\Contract\WorkContactTagGroupServiceInterface;
use App\Contract\WorkContactTagPivotServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\QueueService\WorkContactTagGroup\DeleteApply;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 删除客户标签分组.
 *
 * Class Destroy
 * @Controller
 */
class Destroy extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @var DeleteApply
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
     * @Inject
     * @var WorkContactTagPivotServiceInterface
     */
    private $contactTagPivotService;

    /**
     * @RequestMapping(path="/workContactTagGroup/destroy", methods="DELETE")
     */
    public function handle()
    {
        //接收参数
        $params['groupId'] = $this->request->input('groupId');
        //验证参数
        $this->validated($params);

        //查询该分组的wx_group_id
        $groupInfo = $this->contactTagGroupService->getWorkContactTagGroupById((int) $params['groupId'], ['wx_group_id']);

        //开启事务
        Db::beginTransaction();

        try {
            //软删除分组
            $res = $this->contactTagGroupService
                ->deleteWorkContactTagGroup((int) $params['groupId']);

            if (! is_int($res)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '客户标签分组删除失败');
            }

            //查询该分组下的标签
            $tags = $this->contactTagService->getWorkContactTagsByGroupIds([$params['groupId']], ['id']);

            if (! empty($tags)) {
                $tagIds = array_column($tags, 'id');
                //删除标签
                $tagDelete = $this->contactTagService
                    ->deleteWorkContactTags($tagIds);

                if (! is_int($tagDelete)) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '标签删除失败');
                }

                //删除客户标签
                $contactTag = $this->contactTagPivotService->deleteWorkContactTagPivotsByTagId($tagIds);
                if (! is_int($contactTag)) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '客户标签删除失败');
                }
            }

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            throw new CommonException(ErrorCode::SERVER_ERROR, '删除失败');
        }

        // ---- 以下为同步到企业微信 ----

        $this->service = make(DeleteApply::class);

        if (! empty($groupInfo['wxGroupId'])) {
            $deleteParams = [
                'tag_id'   => [],
                'group_id' => [$groupInfo['wxGroupId']],
            ];

            //删除企业微信分组
            $this->service->handle($deleteParams, user()['corpIds'][0]);
        }
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'groupId' => 'required|int',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
            'groupId.required' => '分组id必传',
        ];
    }
}
