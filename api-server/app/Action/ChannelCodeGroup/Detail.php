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
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 渠道码分组详情.
 *
 * Class Detail
 * @Controller
 */
class Detail extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ChannelCodeGroupServiceInterface
     */
    private $channelCodeGroup;

    /**
     * @RequestMapping(path="/channelCodeGroup/detail", methods="GET")
     */
    public function handle()
    {
        //接收参数
        $params['groupId'] = $this->request->input('groupId');
        //验证参数
        $this->validated($params);

        $res = $this->channelCodeGroup
            ->getChannelCodeGroupById((int) $params['groupId'], ['id', 'name']);

        if (empty($res)) {
            return [];
        }

        $res['groupId'] = $res['id'];

        return $res;
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
