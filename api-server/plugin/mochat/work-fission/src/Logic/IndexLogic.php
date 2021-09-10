<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\WorkFission\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContactContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContract;

class IndexLogic
{
    /**
     * @Inject
     * @var WorkFissionContract
     */
    protected $workFissionService;

    /**
     * @Inject
     * @var WorkFissionContactContract
     */
    protected $workFissionContactService;

    /**
     * @var int
     */
    protected $perPage;

    public function __construct(WorkFissionContract $workFissionService, WorkFissionContactContract $workFissionContactService)
    {
        $this->workFissionService = $workFissionService;
        $this->workFissionContactService = $workFissionContactService;
    }

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理请求参数
        $params = $this->handleParams($user, $params);

        ## 查询数据
        return $this->getFissionList($params);
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParams(array $user, array $params): array
    {
        $where = [];
        if (! empty($params['active_name'])) {
            $where[] = ['active_name', 'LIKE', '%' . $params['active_name'] . '%'];
        }
        $where['corp_id'] = $user['corpIds'][0];
        if ($user['isSuperAdmin'] === 0) {
            $where['create_user_id'] = $user['id'];
        }
        $options = [
            'perPage' => $params['perPage'],
            'page' => $params['page'],
            'orderByRaw' => 'id desc',
        ];

        return ['where' => $where, 'options' => $options];
    }

    /**
     * 获取任务宝列表.
     * @param array $params 参数
     * @return array 响应数组
     */
    private function getFissionList(array $params): array
    {
        $columns = ['id', 'active_name', 'end_time', 'service_employees', 'contact_tags', 'tasks', 'created_at'];
        $fissionList = $this->workFissionService->getWorkFissionList($params['where'], $columns, $params['options']);

        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($fissionList['data']) ? $data : $this->handleData($fissionList);
    }

    /**
     * 数据处理.
     * @param array $roleList 任务宝列表数据
     * @return array 响应数组
     */
    private function handleData(array $roleList): array
    {
        $list = [];
        foreach ($roleList['data'] as $key => $val) {
            $list[$key] = [
                'id' => $val['id'],
                'tasks' => $val['tasks'],
                'active_name' => $val['activeName'],
                'service_employees' => $val['serviceEmployees'],
                'contact_tags' => $val['contactTags'],
                'created_at' => $val['createdAt'],
                'status' => time() > strtotime($val['endTime']) ? '已结束' : '进行中',
                'finance_tag' => '1/2', //任务达成标签-待完善
                'employeeNum' => $this->workFissionContactService->countWorkFissionContactByFissionId((int) $val['id']),
            ];
        }
        $data['page']['total'] = $roleList['total'];
        $data['page']['totalPage'] = $roleList['last_page'];
        $data['list'] = $list;

        return $data;
    }
}
