<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\SensitiveWordsMonitor;

use App\Logic\SensitiveWordsMonitor\ShowLogic;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 敏感词监控 - 详情.
 *
 * Class Show.
 * @Controller
 */
class Show extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ShowLogic
     */
    protected $showLogic;

    /**
     * @RequestMapping(path="/sensitiveWordsMonitor/show", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 获取当前登录用户
        $user = user();
        ## 接收参数
        $monitorId = $this->request->input('sensitiveWordsMonitorId');

        return $this->showLogic->handle((int) $monitorId);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'sensitiveWordsMonitorId' => 'required | integer | min:0| bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'sensitiveWordsMonitorId.required' => '敏感词监控ID 必填',
            'sensitiveWordsMonitorId.integer'  => '敏感词监控ID 必需为整数',
            'sensitiveWordsMonitorId.min'      => '敏感词监控ID 不可小于1',
        ];
    }
}
