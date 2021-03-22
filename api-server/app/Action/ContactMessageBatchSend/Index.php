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

use App\Logic\ContactMessageBatchSend\IndexLogic;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Action\AbstractAction;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 客户消息群发 - 消息列表
 * @Controller()
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject()
     * @var IndexLogic
     */
    private $indexLogin;

    /**
     * @RequestMapping(path="/contactMessageBatchSend/index", methods="GET")
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $params = [
            'page'    => $this->request->input('page', 1),
            'perPage' => $this->request->input('perPage', '10'),
        ];
        return $this->indexLogin->handle($params, user());
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'page'    => 'number|min:1',
            'perPage' => 'number',
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