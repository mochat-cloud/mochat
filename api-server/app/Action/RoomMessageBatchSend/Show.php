<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochatcloud/mochat/blob/master/LICENSE
 */

namespace App\Action\RoomMessageBatchSend;

use App\Logic\RoomMessageBatchSend\ShowLogic;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Action\AbstractAction;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 客户群消息群发 - 查看详情
 * @Controller()
 */
class Show extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject()
     * @var ShowLogic
     */
    private $showLogic;

    /**
     * @RequestMapping(path="/roomMessageBatchSend/show", methods="GET")
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        $batchId = $this->request->input('batchId');
        ## 接收参数
        $params = [
            'batchId' => $batchId,
        ];
        return $this->showLogic->handle($params, intval(user()['id']));
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