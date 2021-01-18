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
 * 修改客户标签分组.
 *
 * Class Update
 * @Controller
 */
class Update extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkContactTagGroupServiceInterface
     */
    private $contactTagGroupService;

    /**
     * @RequestMapping(path="/workContactTagGroup/update", methods="PUT")
     */
    public function handle()
    {
        //接收参数
        $params['groupId']   = $this->request->input('groupId');
        $params['groupName'] = $this->request->input('groupName');
        $params['isUpdate']  = $this->request->input('isUpdate');

        //验证参数
        $this->validated($params);

        //若修改了信息做校验
        if ($params['isUpdate'] == 1) {
            //查询是否已存在相同分组名称
            $info = $this->contactTagGroupService
                ->getWorkContactTagGroupsByName($params['groupName']);

            if (! empty($info)) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '已存在相同分组名');
            }
        }

        $res = $this->contactTagGroupService
            ->updateWorkContactTagGroupById((int) $params['groupId'], ['group_name' => $params['groupName']]);
        if (! is_int($res)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '客户标签分组编辑失败');
        }
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'groupId'   => 'required|int',
            'groupName' => 'required',
            'isUpdate'  => 'required',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
            'groupId.required'   => '分组id必传',
            'groupName.required' => '分组名称必传',
            'isUpdate.required'  => '是否修改信息必传',
        ];
    }
}
