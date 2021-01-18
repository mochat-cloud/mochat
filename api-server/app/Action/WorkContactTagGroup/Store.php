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
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 创建客户标签分组.
 *
 * Class Store
 * @Controller
 */
class Store extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkContactTagGroupServiceInterface
     */
    private $contactTagGroupService;

    /**
     * @RequestMapping(path="/workContactTagGroup/store", methods="POST")
     */
    public function handle()
    {
        //接收参数
        $params['groupName'] = $this->request->input('groupName');

        //企业id
        $corpId = user()['corpIds'];
        if (count($corpId) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请先选择企业');
        }

        $data = [
            'corp_id'    => $corpId[0],
            'group_name' => $params['groupName'],
        ];
        //验证参数
        $this->validated($params);

        //查询是否已存在相同分组名称
        $info = $this->contactTagGroupService
            ->getWorkContactTagGroupsByName($params['groupName']);

        if (! empty($info)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '已存在相同分组名');
        }

        $res = $this->contactTagGroupService->createWorkContactTagGroup($data);

        if (! is_int($res)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '客户标签分组创建失败');
        }
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'groupName' => 'required',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
            'groupName.required' => '分组名称必传',
        ];
    }
}
