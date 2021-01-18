<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkRoom;

use App\Constants\WorkContactRoom\Status as WorkContactRoomStatus;
use App\Contract\WorkContactRoomServiceInterface;
use App\Contract\WorkRoomServiceInterface;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 客户群管理-统计分页数据.
 *
 * Class StatisticsIndex.
 * @Controller
 */
class StatisticsIndex extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkRoomServiceInterface
     */
    protected $workRoomService;

    /**
     * @Inject
     * @var WorkContactRoomServiceInterface
     */
    protected $workContactRoomService;

    /**
     * @RequestMapping(path="/workRoom/statisticsIndex", methods="get")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function index(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $params = [
            'workRoomId' => $this->request->input('workRoomId'),
            'type'       => $this->request->input('type'),
            'startTime'  => $this->request->input('startTime'),
            'endTime'    => $this->request->input('endTime'),
            'page'       => $this->request->input('page', 1),
            'perPage'    => $this->request->input('perPage', 10),
        ];

        ## 参数校验-当type = 1(按天统计)时间必传
        if ($params['type'] == 1 && (empty($params['startTime']) || empty($params['endTime']))) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '按天统计开始和结束时间必传');
        }

        ## 检索该群聊所有成员信息
        $contactRoomList = $this->workContactRoomService->getWorkContactRoomsByRoomId((int) $params['workRoomId'], ['id', 'status', 'join_time', 'out_time']);

        if (empty($contactRoomList)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '该客户群数据错误');
        }
        ## 根据统计类型组织列表结构
        $list = $this->formData($params);
        ## 组织返回结构
        $data = [
            'page' => [
                'perPage'   => $params['perPage'],
                'total'     => 0,
                'totalPage' => 0,
            ],
            'list' => [],
        ];

        ## 归纳数据
        foreach ($contactRoomList as $v) {
            if (in_array($params['type'], [1, 2])) { ## 按天统计||按自然周统计
                $inKey  = date('Y-m-d', strtotime($v['joinTime']));
                $outKey = empty($v['outTime']) ? 'outTime' : date('Y-m-d', strtotime($v['outTime']));
            } else { ## 按自然年统计
                $inKey  = date('Y-m', strtotime($v['joinTime']));
                $outKey = empty($v['outTime']) ? 'outTime' : date('Y-m', strtotime($v['outTime']));
            }
            ## 入群
            isset($list[$inKey]) && ++$list[$inKey]['addNum'];
            ## 退群
            isset($list[$outKey]) && ++$list[$outKey]['outNum'];

            foreach ($list as $key => &$value) {
                $inKey > $key || ++$value['total'];
                if ($v['status'] == WorkContactRoomStatus::QUIT && $outKey != 'outTime' && $outKey <= $key) {
                    ++$value['outTotal'];
                }
            }
        }
        $list = array_values($list);

        $data['page']['total']     = count($list);
        $data['page']['totalPage'] = ceil(count($list) / $params['perPage']);
        $startKey                  = ($params['page'] - 1) * $params['perPage'];
        $data['list']              = array_slice($list, $startKey, $params['perPage']);

        return $data;
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'workRoomId' => 'required | integer | min:0, | bail',
            'type'       => 'required | integer | in:1,2,3, | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'workRoomId.required' => '客户群ID 必填',
            'workRoomId.integer'  => '客户群ID 必需为整数',
            'workRoomId.min'      => '客户群ID 值不可小于1',
            'type.required'       => '统计类型 必填',
            'type.integer'        => '统计类型 必需为整数',
            'type.in'             => '统计类型 值必须在列表内：[1,2,3]',
        ];
    }

    /**
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function formData($params): array
    {
        $data = [];
        if ($params['type'] == 1) { ## 按天统计
            $stime = strtotime($params['startTime']);
            $etime = strtotime($params['endTime']);
            while ($stime <= $etime) {
                $data[] = [
                    'time'     => date('Y-m-d', $stime),
                    'addNum'   => 0,
                    'outNum'   => 0,
                    'total'    => 0,
                    'outTotal' => 0,
                ];
                $stime = $stime + 86400;
            }
        } elseif ($params['type'] == 2) { ## 按自然周统计
            $beforeWeekDay = date('Y-m-d', strtotime('-1 week'));
            for ($i = 1; $i <= 7; ++$i) {
                $data[] = [
                    'time'     => date('Y-m-d', strtotime('+' . $i . ' days', strtotime($beforeWeekDay))),
                    'addNum'   => 0,
                    'outNum'   => 0,
                    'total'    => 0,
                    'outTotal' => 0,
                ];
            }
        } else { ## 按自然年统计
            $beforeYearMonth = date('Y-m', strtotime('-1 year'));
            for ($i = 1; $i <= 12; ++$i) {
                $data[] = [
                    'time'     => date('Y-m', strtotime('+' . $i . ' months', strtotime($beforeYearMonth))),
                    'addNum'   => 0,
                    'outNum'   => 0,
                    'total'    => 0,
                    'outTotal' => 0,
                ];
            }
        }

        return array_column($data, null, 'time');
    }
}
