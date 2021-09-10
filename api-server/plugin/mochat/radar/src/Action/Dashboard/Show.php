<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Radar\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\User\Contract\UserContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\Radar\Contract\RadarChannelContract;
use MoChat\Plugin\Radar\Contract\RadarChannelLinkContract;
use MoChat\Plugin\Radar\Contract\RadarContract;
use MoChat\Plugin\Radar\Contract\RadarRecordContract;

/**
 * 互动雷达- 详情.
 *
 * Class Show.
 * @Controller
 */
class Show extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var RadarContract
     */
    protected $radarService;

    /**
     * @Inject
     * @var UserContract
     */
    protected $userService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var RadarRecordContract
     */
    protected $radarRecordService;

    /**
     * @Inject
     * @var RadarChannelLinkContract
     */
    protected $radarChannelLinkService;

    /**
     * @Inject
     * @var RadarChannelContract
     */
    protected $radarChannelService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/dashboard/radar/show", methods="get")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $params = $this->request->all();
        $this->validated($params);
        ## 查询数据
        return $this->handleData($params);
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
        ];
    }

    /**
     * @throws \JsonException
     */
    private function handleData(array $params): array
    {
        ## 互动雷达信息
        $info = $this->radarService->getRadarById((int) $params['id'], ['id', 'title', 'link', 'link_title', 'link_description', 'link_cover', 'pdf_name', 'pdf', 'article_type', 'article', 'employee_card', 'action_notice', 'dynamic_notice', 'tag_status', 'contact_tags', 'contact_grade', 'create_user_id', 'created_at']);
        if (! empty($info['linkCover'])) {
            $info['linkCover'] = file_full_url($info['linkCover']);
        }
        if ($info['tagStatus'] === 1) {
            $info['contactTags'] = json_decode($info['contactTags'], true, 512, JSON_THROW_ON_ERROR);
        }
        ## 处理创建者信息
        $username = $this->userService->getUserById($info['createUserId']);
        $info['create_user'] = isset($username['name']) ? $username['name'] : '';
        $dataStatistics = $this->dataStatistics($params);
        return [
            'info' => $info,
            'statistics' => $dataStatistics['statistics'],
            'click_statistics' => $dataStatistics['click_statistics'],
            'person_statistics' => $dataStatistics['person_statistics'],
        ];
    }

    /**
     * 互动雷达统计信息.
     */
    private function dataStatistics(array $params): array
    {
        $user = user();
        ## 数据总览
        $today = date('Y-m-d');
        $statistics['total_person_num'] = $this->radarChannelLinkService->countRadarChannelLinkByCorpIdRadarId($user['corpIds'][0], (int) $params['id'], 'click_person_num');
        $statistics['total_click_num'] = $this->radarChannelLinkService->countRadarChannelLinkByCorpIdRadarId($user['corpIds'][0], (int) $params['id'], 'click_num');
        $statistics['today_person_num'] = $this->radarRecordService->countRadarRecordPersonByCorpIdRadarIdCreatedAt($user['corpIds'][0], (int) $params['id'], $today);
        $statistics['today_click_num'] = $this->radarRecordService->countRadarRecordByCorpIdRadarIdCreatedAt($user['corpIds'][0], (int) $params['id'], $today);
        ## 数据详情-点击次数top10
        $clickStatistics = $this->radarRecordService->getRadarRecordByCorpIdRadarIdGroupByChannelId($user['corpIds'][0], $params);
        if (! empty($clickStatistics)) {
            $clickStatistics = $this->arraySort($clickStatistics, 'total', 'desc');
            $clickStatistics = array_slice($clickStatistics, 0, 10);
            foreach ($clickStatistics as $key => $val) {
                $channelName = $this->radarChannelService->getRadarChannelById((int) $val['channelId'], ['name']);
                $clickStatistics[$key]['channelName'] = empty($channelName) ? '' : $channelName['name'];
                unset($clickStatistics[$key]['channelId']);
            }
        }
        ## 数据详情-点击人数top10
        $channel = $this->radarChannelLinkService->getRadarChannelLinksByRadarId((int) $params['id'], ['channel_id']);
        $personStatistics = [];
        foreach ($channel as $k => $v) {
            $channelName = $this->radarChannelService->getRadarChannelById((int) $v['channelId'], ['name']);
            $personStatistics[$k]['channelName'] = empty($channelName) ? '' : $channelName['name'];
            $personStatistics[$k]['total'] = $this->radarRecordService->countRadarRecordByCorpIdRadarIdChannelId($user['corpIds'][0], $v['channelId'], $params);
        }
        if (! empty($personStatistics)) {
            $personStatistics = $this->arraySort($personStatistics, 'total', 'desc');
            $personStatistics = array_slice($personStatistics, 0, 10);
        }

        return ['statistics' => $statistics, 'click_statistics' => $clickStatistics, 'person_statistics' => $personStatistics];
    }

    /**
     * 二维数组排列.
     * @param $array
     * @param $keys
     * @return array
     */
    private function arraySort($array, $keys, string $sort = 'asc')
    {
        $newArr = $valArr = [];
        foreach ($array as $key => $value) {
            $valArr[$key] = $value[$keys];
        }
        ($sort === 'asc') ? asort($valArr) : arsort($valArr);
        reset($valArr);
        foreach ($valArr as $key => $value) {
            $newArr[$key] = $array[$key];
        }
        return $newArr;
    }
}
