<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\ChannelCode;

use App\Logic\ChannelCode\ContactLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 渠道码客户.
 * Class Contact.
 * @Controller
 */
class Contact extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ContactLogic
     */
    protected $contactLogic;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/channelCode/contact", methods="GET")
     */
    public function handle()
    {
        //接收参数
        $params['channelCodeId'] = $this->request->input('channelCodeId');
        $params['page']          = $this->request->input('page');
        $params['perPage']       = $this->request->input('perPage');

        //验证参数
        $this->validated($params);

        return $this->contactLogic->handle($params);
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'channelCodeId' => 'required',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
            'channelCodeId.required' => '渠道码id必传',
        ];
    }
}
