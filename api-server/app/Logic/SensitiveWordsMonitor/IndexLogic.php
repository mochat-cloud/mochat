<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\SensitiveWordsMonitor;

use App\Constants\SensitiveWordsMonitor\ReceiverType;
use App\Constants\SensitiveWordsMonitor\Source;
use App\Contract\CorpServiceInterface;
use App\Contract\SensitiveWordMonitorServiceInterface;
use App\Contract\SensitiveWordServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 敏感词监控-列表.
 *
 * Class IndexLogic
 */
class IndexLogic
{
    /**
     * @Inject
     * @var SensitiveWordMonitorServiceInterface
     */
    protected $monitorService;

    /**
     * @Inject
     * @var SensitiveWordServiceInterface
     */
    protected $sensitiveWordService;

    /**
     * @Inject
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @param array $params 请求参数
     * @param array $user 当前登录用户信息
     * @return array 响应数组
     */
    public function handle(array $params, array $user): array
    {
        ## 处理请求参数
        $params = $this->handleParams($user, $params);
        ## 查询数据
        return $this->getPageList($user, $params);
    }

    /**
     * @param array $user 当前登录用户
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function handleParams(array $user, array $params): array
    {
        ## 查询条件
        $where = [];
        ## 公司信息
        $where['corp_id'] = isset($user['corpIds']) ? (int) $user['corpIds'][0] : 0;
        ## 敏感词分组
        $params['intelligentGroupId'] == 'no' || $where['sensitive_word_id'] = $this->getSensitiveWordList((int) $params['intelligentGroupId']);
        ## 员工信息
        if (! empty($params['employeeId'])) {
            $where['trigger_id'] = array_filter(explode(',', $params['employeeId']));
            $where['source']     = Source::EMPLOYEE;
        }
        ## 群聊
        if (! empty($params['workRoomId'])) {
            $where['receiver_id']   = $params['workRoomId'];
            $where['receiver_type'] = ReceiverType::ROOM;
        }
        ## 触发时间-开始
        empty($params['triggerStart']) || $where[] = ['trigger_time', '>=', $params['triggerStart']];
        ## 触发时间-结束
        empty($params['triggerEnd']) || $where[] = ['trigger_time', '<=', $params['triggerEnd']];

        ## 分页排序
        $options = [
            'page'       => $params['page'],
            'perPage'    => $params['perPage'],
            'orderByRaw' => 'id desc',
        ];
        return [
            'where'   => $where,
            'options' => $options,
        ];
    }

    /**
     * @param int $groupId 敏感词分组ID
     * @return array 响应数组
     */
    private function getSensitiveWordList(int $groupId): array
    {
        $list = $this->sensitiveWordService->getSensitiveWordsByGroupId($groupId, ['id']);
        return empty($list) ? [] : array_column($list, 'id');
    }

    /**
     * @param array $user 当前登录用户
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function getPageList(array $user, array $params): array
    {
        $monitors = $this->monitorService->getSensitiveWordMonitorList($params['where'], ['*'], $params['options']);
        ## 组织响应数据
        $data = [
            'page' => [
                'perPage'   => $params['options']['perPage'],
                'total'     => 0,
                'totalPage' => 0,
            ],
            'list' => [],
        ];
        ## 处理分页数据
        $data['page']['total']     = $monitors['total'];
        $data['page']['totalPage'] = $monitors['last_page'];
        ## 列表数据
        $data['list'] = empty($monitors['data']) ? [] : $this->handleMonitors($monitors['data'], $user);
        return $data;
    }

    /**
     * @param array $monitors 敏感词监控数据列表
     * @param array $user 当前登录用户
     * @return array 响应数据
     */
    private function handleMonitors(array $monitors, array $user): array
    {
        return array_map(function ($monitor) {
            return [
                'sensitiveWordMonitorId' => $monitor['id'],
                'sensitiveWordName'      => $monitor['sensitiveWordName'],
                'source'                 => $monitor['source'],
                'sourceText'             => Source::getMessage((int) $monitor['source']),
                'triggerName'            => $monitor['triggerName'],
                'triggerScenario'        => $monitor['receiverName'],
                'triggerTime'            => $monitor['triggerTime'],
            ];
        }, $monitors);
    }
}
