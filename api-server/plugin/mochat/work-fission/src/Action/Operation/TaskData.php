<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\WorkFission\Action\Operation;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContactContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionWelcomeContract;

/**
 * 任务宝H5 - 获取任务数据.
 *
 * Class Store.
 * @Controller
 */
class TaskData extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @var \Laminas\Stdlib\RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var WorkFissionContactContract
     */
    protected $workFissionContactService;

    /**
     * @Inject
     * @var WorkFissionContract
     */
    protected $workFissionService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * 欢迎语.
     * @var WorkFissionWelcomeContract
     */
    protected $workFissionWelcomeService;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * Statistics constructor.
     */
    public function __construct(RequestInterface $request, WorkFissionContactContract $workFissionContactService, WorkFissionContract $workFissionService, WorkEmployeeContract $workEmployeeService, WorkFissionWelcomeContract $fissionWelcomeService)
    {
        $this->request = $request;
        $this->workFissionContactService = $workFissionContactService;
        $this->workFissionService = $workFissionService;
        $this->workEmployeeService = $workEmployeeService;
        $this->workFissionWelcomeService = $fissionWelcomeService;
    }

    /**
     * @RequestMapping(path="/operation/workFission/taskData", methods="get")
     * @throws \JsonException
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
        return ['union_id' => 'required',
            'fission_id' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'union_id.required' => '客户微信id 必填',
            'fission_id' => '活动id 必填',
        ];
    }

    /**
     * @param $params
     * @throws \JsonException
     */
    private function getTaskList($params): array
    {
        $user = $this->workFissionContactService->getWorkFissionContactByUnionId($params['union_id']);
        ## 裂变
        $contact = $this->workFissionContactService->getWorkFissionContactById((int) $user['id'], ['level', 'invite_count', 'receive_level']);
        $level = $contact['level'] ?? 0;
        ## 活动
        $fission = $this->workFissionService->getWorkFissionById((int) $params['fission_id'], ['receive_prize', 'receive_prize_employees', 'receive_links', 'receive_qrcode', 'tasks', 'end_time', 'delete_invalid']);
        ## 邀请数量
        $inviteCount = 0;
        if ($fission['deleteInvalid'] == 0) {
            $inviteCount = $this->workFissionContactService->countWorkFissionContactByParentLoss((int) $user['id'], (int) $params['fission_id']);
        }
        ## 删除好友失效
        if ($fission['deleteInvalid'] == 1) {
            $inviteCount = $this->workFissionContactService->countWorkFissionContactByParentLoss((int) $user['id'], (int) $params['fission_id'], '0');
        }
        $task = [];
        foreach (json_decode($fission['tasks'], true, 512, JSON_THROW_ON_ERROR) as $key => $val) {
            $task[$key]['count'] = $val['count'];
            $task[$key]['status'] = 0;
            if ($inviteCount >= $val['count']) {
                $task[$key]['status'] = 1;
            }
            $task[$key]['receive_status'] = 0;
            if ($contact['receiveLevel'] > 0 && $contact['receiveLevel'] >= $key + 1) {
                $task[$key]['receive_status'] = 1;
            }
            $task[$key]['gift_type'] = $fission['receivePrize'];
            if ($fission['receivePrize'] == 0) {
                $task[$key]['gift_url'] = json_decode($fission['receiveQrcode'], true, 512, JSON_THROW_ON_ERROR)['url'];
            }
            if ($fission['receivePrize'] == 1) {
                $task[$key]['gift_url'] = json_decode($fission['receiveLinks'], true, 512, JSON_THROW_ON_ERROR)[$key];
            }
        }
        $total = 0;
        $differCount = 0;
        foreach (json_decode($fission['tasks'], true) as $key => $val) {
            if ($val['count'] > $inviteCount) {
                $differCount = $total - $inviteCount;
                break;
            }
        }

        return [
            'invite_count' => $inviteCount,
            'differ_count' => $differCount,
            'end_time' => strtotime($fission['endTime']),
            'task' => $task,
        ];
    }
}
