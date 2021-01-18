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
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 客户标签详情.
 *
 * Class Detail
 * @Controller
 */
class Detail extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkContactTagServiceInterface
     */
    private $contactTagService;

    /**
     * @RequestMapping(path="/workContactTag/detail", methods="GET")
     */
    public function handle()
    {
        //接收参数
        $params['tagId'] = $this->request->input('tagId');

        //验证参数
        $this->validated($params);

        $columns = [
            'id',
            'name',
            'contact_tag_group_id',
        ];
        $res = $this->contactTagService->getWorkContactTagById((int) $params['tagId'], $columns);

        if (empty($res)) {
            return [];
        }

        return [
            'tagId'   => $res['id'],
            'tagName' => $res['name'],
            'groupId' => $res['contactTagGroupId'],
        ];
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
