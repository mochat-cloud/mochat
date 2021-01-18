<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkContactTag;

use App\Contract\WorkContactTagServiceInterface;
use App\Middleware\PermissionMiddleware;
use App\QueueService\WorkContactTag\DeleteApply;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 删除标签.
 *
 * Class Store
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
     * @var WorkContactTagServiceInterface
     */
    private $contactTagService;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/workContactTag/destroy", methods="DELETE")
     */
    public function handle()
    {
        //接收参数
        $params['tagId'] = $this->request->input('tagId');
        //验证参数
        $this->validated($params);

        //字符串转数组
        $tagIds = explode(',', $params['tagId']);

        //查询该标签对应的wx_contact_tag_id
        $tagInfo = $this->contactTagService->getWorkContactTagsById($tagIds, ['wx_contact_tag_id', 'contact_tag_group_id']);
        if (empty($tagInfo)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '查询不到该标签信息');
        }

        //软删除
        $res = $this->contactTagService
            ->deleteWorkContactTags($tagIds);

        if (! is_int($res)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '标签删除失败');
        }

        $tagId              = [];
        $contactTagGroupIds = [];
        foreach ($tagInfo as &$raw) {
            if ($raw['contactTagGroupId'] != 0) {
                $tagId[]              = $raw['wxContactTagId'];
                $contactTagGroupIds[] = $raw['contactTagGroupId'];
            }
        }
        unset($raw);

        // -----  以下为同步到企业微信 ----

        if (! empty($tagId)) {
            //删除微信标签
            $this->service = make(DeleteApply::class);
            $deleteParams  = [
                'tag_id'   => $tagId,
                'group_id' => [],
            ];

            $this->service->handle($deleteParams, $contactTagGroupIds, $tagIds, user()['corpIds'][0]);
        }
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'tagId' => 'required',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
            'tagId.required' => '标签id必传',
        ];
    }
}
