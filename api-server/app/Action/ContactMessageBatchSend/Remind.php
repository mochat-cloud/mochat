<?php

declare(strict_types=1);

/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochatcloud/mochat/blob/master/LICENSE
 */

namespace App\Action\ContactMessageBatchSend;


use App\Logic\ContactMessageBatchSend\RemindLogic;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 客户消息群发 - 提醒群主发送
 * @Controller()
 */
class Remind extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject()
     * @var RemindLogic
     */
    private $remindLogic;

    /**
     * @RequestMapping(path="/contactMessageBatchSend/remind", methods="POST")
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $batchEmployIds = $this->request->input('batchEmployIds', '');
        $params         = [
            'batchId'        => $this->request->input('batchId'),
            'batchEmployIds' => array_filter(explode(',', $batchEmployIds)),
        ];
        $this->remindLogic->handle($params, intval(user()['id']));
        return [];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'batchId' => 'required|numeric',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [];
    }

}