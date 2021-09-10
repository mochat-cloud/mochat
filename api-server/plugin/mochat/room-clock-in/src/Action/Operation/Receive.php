<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomClockIn\Action\Operation;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomClockIn\Contract\ClockInContactContract;

/**
 * H5 - 领取任务奖励.
 *
 * Class Receive.
 * @Controller
 */
class Receive extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var ClockInContactContract
     */
    protected $clockInContactService;

    public function __construct(RequestInterface $request, ClockInContactContract $clockInContactService)
    {
        $this->request = $request;
        $this->clockInContactService = $clockInContactService;
    }

    /**
     * @RequestMapping(path="/operation/roomClockIn/receive", methods="put")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $params = $this->request->all();
        $this->validated($params);

        return $this->getTaskList($params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'id' => 'required | integer | min:0 | bail',
            'union_id' => 'required',
            'level' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'id.required' => '活动ID 必填',
            'id.integer' => '活动ID 必需为整数',
            'id.min  ' => '活动ID 不可小于1',
            'union_id.required' => 'union_id 必填',
            'level.required' => 'level 必填',
        ];
    }

    /**
     * @param $params
     * @return array|array[]
     */
    private function getTaskList($params): array
    {
        $contact = $this->clockInContactService->getClockInContactByClockInIdUnionId((int) $params['id'], $params['union_id'], ['id', 'total_day', 'series_day']);
        $this->clockInContactService->updateClockInContactById((int) $contact['id'], ['receive_level' => $params['level']]);
        return [];
    }
}
