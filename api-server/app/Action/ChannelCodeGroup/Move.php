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

use App\Contract\ChannelCodeServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 渠道码移动分组.
 *
 * Class Move
 * @Controller
 */
class Move extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ChannelCodeServiceInterface
     */
    private $channelCode;

    /**
     * @RequestMapping(path="/channelCodeGroup/move", methods="PUT")
     */
    public function handle()
    {
        //接收参数
        $params['channelCodeId'] = $this->request->input('channelCodeId');
        $params['groupId']       = $this->request->input('groupId');
        //验证参数
        $this->validated($params);

        $res = $this->channelCode
            ->updateChannelCodeById(
                (int) $params['channelCodeId'],
                [
                    'group_id'   => $params['groupId'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ]
            );

        if (! is_int($res)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '移动分组失败');
        }
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'groupId' => 'required',
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
