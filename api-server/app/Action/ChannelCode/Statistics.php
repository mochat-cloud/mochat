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

use App\Logic\ChannelCode\StatisticsLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 渠道码 - 统计折线图.
 *
 * Class Statistics
 * @Controller
 */
class Statistics extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var StatisticsLogic
     */
    protected $statisticsLogic;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/channelCode/statistics", methods="GET")
     */
    public function handle()
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $params = [
            'channelCodeId' => $this->request->input('channelCodeId'),
            'type'          => $this->request->input('type'),
            'startTime'     => $this->request->input('startTime'),
            'endTime'       => $this->request->input('endTime'),
        ];

        return $this->statisticsLogic->handle($params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'channelCodeId' => 'required | integer | min:0, | bail',
            'type'          => 'required | integer | in:1,2,3, | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'channelCodeId.required' => '渠道码ID 必填',
            'channelCodeId.integer'  => '渠道码ID 必需为整数',
            'channelCodeId.min'      => '渠道码ID 值不可小于1',
            'type.required'          => '统计类型 必填',
            'type.integer'           => '统计类型 必需为整数',
            'type.in'                => '统计类型 值必须在列表内：[1,2,3]',
        ];
    }
}
