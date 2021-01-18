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

use App\Logic\ChannelCode\ChannelCodeLogic;
use App\Logic\ChannelCode\UpdateLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 编辑渠道码.
 *
 * Class Store
 * @Controller
 */
class Update extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var UpdateLogic
     */
    protected $updateLogic;

    /**
     * @Inject
     * @var ChannelCodeLogic
     */
    protected $channelCodeLogic;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/channelCode/update", methods="PUT")
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return array
     */
    public function handle()
    {
        $corpId = user()['corpIds'];
        if (count($corpId) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请先选择企业');
        }

        //接收参数
        $params['channelCodeId']    = $this->request->input('channelCodeId');
        $params['baseInfo']         = $this->request->input('baseInfo');
        $params['drainageEmployee'] = $this->request->input('drainageEmployee');
        $params['welcomeMessage']   = $this->request->input('welcomeMessage');

        //验证参数
        $this->validated($params);

        return $this->updateLogic->handle($params);
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
