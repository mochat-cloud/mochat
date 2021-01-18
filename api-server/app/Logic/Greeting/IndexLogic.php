<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\Greeting;

use App\Constants\BusinessLog\Event;
use App\Constants\Greeting\RangeType;
use App\Constants\Medium\Type as mediumType;
use App\Contract\GreetingServiceInterface;
use App\Contract\MediumServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Logic\Common\Traits\BusinessLogTrait;
use Hyperf\Di\Annotation\Inject;

/**
 * 欢迎语-列表.
 *
 * Class IndexLogic
 */
class IndexLogic
{
    use BusinessLogTrait;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var GreetingServiceInterface
     */
    private $greetingService;

    /**
     * @Inject
     * @var MediumServiceInterface
     */
    private $mediumService;

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
        return $this->getGreetings($params);
    }

    /**
     * @param array $user 当前登录用户
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function handleParams(array $user, array $params): array
    {
        ## 数据权限
        $user['dataPermission'] == 0 || $where[] = ['id', 'IN', $this->getBusinessIds($user['deptEmployeeIds'], [Event::GREETING_CREATE])];
        ## 列表查询条件
        $where['corp_id'] = $user['corpIds'][0];
        ## 分页信息
        $options = [
            'page'       => $params['page'],
            'perPage'    => $params['perPage'],
            'orderByRaw' => 'updated_at desc',
        ];

        return $data = [
            'where'   => $where,
            'options' => $options,
        ];
    }

    /**
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function getGreetings(array $params): array
    {
        ## 分页查询数据表
        $greetings = $this->greetingService->getGreetingList($params['where'], ['*'], $params['options']);
        ## 组织响应数据
        $data = [
            'page' => [
                'perPage'   => $params['options']['perPage'],
                'total'     => 0,
                'totalPage' => 0,
            ],
            'hadGeneral'   => 0,
            'hadEmployees' => [],
            'list'         => [],
        ];

        return empty($greetings['data']) ? $data : $this->handleData($data, $greetings, (int) $params['where']['corp_id']);
    }

    /**
     * @param array $data 请求参数
     * @param array $greetings 欢迎语列表
     * @param int $corpId 公司ID
     * @return array 响应数组
     */
    private function handleData(array $data, array $greetings, int $corpId): array
    {
        ## 分页数据
        $data['page']['total']     = $greetings['total'];
        $data['page']['totalPage'] = $greetings['last_page'];
        ## 处理已存在数据
        $hadGreetings = $this->hadGreetings($corpId);
        ## 查询是否存在通用欢迎语
        $data['hadGeneral'] = $hadGreetings['hadGeneral'];
        ## 查询已存在特定欢迎语的成员
        $data['hadEmployees'] = $hadGreetings['hadEmployees'];
        ## 欢迎语素材
        $mediumIdArr = array_unique(array_filter(array_column($greetings['data'], 'mediumId')));
        $mediumList  = empty($mediumIdArr) ? [] : $this->getMediumList($mediumIdArr);

        $data['list'] = array_map(function ($greeting) use ($mediumList) {
            $medium = isset($mediumList[$greeting['mediumId']]) ? $mediumList[$greeting['mediumId']] : [];
            $mediumContent = empty($medium) ? [] : $this->mediumService->addFullPath(json_decode($medium['content'], true), $medium['type']);
            ## 欢迎语类型
            $typeTextArr = array_map(function ($type) {
                return mediumType::getMessage((int) $type);
            }, array_filter(explode('-', $greeting['type'])));
            return [
                'greetingId'    => $greeting['id'],
                'typeText'      => implode('+', $typeTextArr),
                'rangeType'     => $greeting['rangeType'],
                'rangeTypeText' => RangeType::getMessage((int) $greeting['rangeType']),
                'employees'     => $this->getEmployees((int) $greeting['rangeType'], json_decode($greeting['employees'], true)),
                'words'         => $greeting['words'],
                'mediumId'      => $greeting['mediumId'],
                'mediumContent' => $mediumContent,
                'createdAt'     => $greeting['createdAt'],
            ];
        }, $greetings['data']);

        return $data;
    }

    /**
     * @param int $corpId 公司ID
     */
    private function hadGreetings(int $corpId): array
    {
        $data = [
            'hadGeneral'   => 0,
            'hadEmployees' => [],
        ];
        $greetings = $this->greetingService->getGreetingsByCorpId($corpId, ['id', 'range_type', 'employees']);
        if (empty($greetings)) {
            return [];
        }
        in_array(RangeType::ALL, array_column($greetings, 'rangeType')) && $data['hadGeneral'] = 1;

        foreach ($greetings as $greet) {
            $employees                                 = json_decode($greet['employees']);
            empty($employees) || $data['hadEmployees'] = array_merge($data['hadEmployees'], $employees);
        }
        $data['hadEmployees'] = array_values(array_unique($data['hadEmployees']));
        return $data;
    }

    /**
     * @param array $mediumIdArr 素材库ID数组
     * @return array 相应数组
     */
    private function getMediumList(array $mediumIdArr): array
    {
        $mediumList = $this->mediumService->getMediaById($mediumIdArr, ['id', 'type', 'content']);
        return empty($mediumList) ? [] : array_column($mediumList, null, 'id');
    }

    /**
     * @param int $rangeType 欢迎语适用类型枚举
     * @param array $employeeIds 适用成员ID数组
     * @return array 响应数组
     */
    private function getEmployees(int $rangeType, array $employeeIds): array
    {
        if ($rangeType == RangeType::ALL) {
            $data = [RangeType::getMessage((int) $rangeType)];
        } else {
            $data                 = empty($employeeIds) ? [] : $this->workEmployeeService->getWorkEmployeesById($employeeIds, ['name']);
            empty($data) || $data = array_column($data, 'name');
        }
        return $data;
    }
}
