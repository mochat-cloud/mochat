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

use App\Logic\WorkContactTag\StoreLogic;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 创建客户标签.
 *
 * Class Store
 * @Controller
 */
class Store extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var StoreLogic
     */
    private $storeLogic;

    /**
     * @RequestMapping(path="/workContactTag/store", methods="POST")
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        //接收参数
        $params['groupId'] = $this->request->input('groupId');
        $params['tagName'] = $this->request->input('tagName');

        //验证参数
        $this->validated($params);

        return $this->storeLogic->handle($params);
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'groupId' => 'required',
            'tagName' => 'required',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
            'groupId.required' => '分组id必传',
            'tagName.required' => '标签名称必传',
        ];
    }
}
