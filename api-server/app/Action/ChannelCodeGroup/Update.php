<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\ChannelCodeGroup;

use App\Contract\ChannelCodeGroupServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 修改渠道码分组.
 *
 * Class Update
 * @Controller
 */
class Update extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ChannelCodeGroupServiceInterface
     */
    private $channelCodeGroup;

    /**
     * @RequestMapping(path="/channelCodeGroup/update", methods="PUT")
     */
    public function handle()
    {
        //接收参数
        $params['groupId']  = $this->request->input('groupId');
        $params['name']     = $this->request->input('name');
        $params['isUpdate'] = $this->request->input('isUpdate');

        //验证参数
        $this->validated($params);

        //查询是否已存在相同分组名称
        $info = $this->channelCodeGroup
            ->getChannelCodeGroupsByName($params['name']);
        if (! empty($info)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '已存在相同分组名');
        }

        $res = $this->channelCodeGroup
            ->updateChannelCodeGroupById((int) $params['groupId'], ['name' => $params['name']]);
        if (! is_int($res)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '分组编辑失败');
        }
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'groupId' => 'required|int',
            'name'    => 'required',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
            'groupId.required' => '分组id必传',
            'name.required'    => '分组名称必传',
        ];
    }
}
