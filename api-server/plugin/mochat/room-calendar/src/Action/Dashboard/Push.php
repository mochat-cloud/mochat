<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomCalendar\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomCalendar\Contract\RoomCalendarContract;
use MoChat\Plugin\RoomCalendar\Contract\RoomCalendarPushContract;

/**
 * 群日历- 推送.
 *
 * Class Show.
 * @Controller
 */
class Push extends AbstractAction
{
    use ValidateSceneTrait;
    use AppTrait;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var RoomCalendarContract
     */
    protected $roomCalendarService;

    /**
     * @Inject
     * @var RoomCalendarPushContract
     */
    protected $roomCalendarPushService;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/dashboard/roomCalendar/push", methods="get")
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 接收参数
        $params = $this->request->all();
        ## 参数验证
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
            'owner' => 'required',
            'day' => 'required',
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
            'owner.required' => 'owner 必填',
            'day.required' => 'day 必填',
        ];
    }

    /**
     * @param $params
     * @throws \JsonException
     */
    private function handleData($params): array
    {
        ## 数据
        $calendar = $this->roomCalendarService->getRoomCalendarById((int) $params['id'], ['id', 'name', 'rooms', 'corp_id', 'create_user_id']);
        $push = $this->roomCalendarPushService->getRoomCalendarPushByRoomCalendarIdDay((int) $params['id'], $params['day'], ['day', 'push_content']);

        $roomArr = json_decode($calendar['rooms'], true, 512, JSON_THROW_ON_ERROR);
        foreach ($roomArr as $key => $val) {
            if ((int) $val['ownerId'] !== (int) $params['owner']) {
                unset($roomArr[$key]);
                continue;
            }
            $room = $this->workRoomService->getWorkRoomById((int) $val['id'], ['create_time']);
            $roomArr[$key]['created_at'] = $room['createTime'];
            unset($roomArr[$key]['id'], $roomArr[$key]['ownerId'], $roomArr[$key]['roomMax'], $roomArr[$key]['contact_num']);
        }
        $employee = $this->workEmployeeService->getWorkEmployeeById($calendar['createUserId'], ['name']);
        ##EasyWeChat上传图片
        $upload = $this->wxApp($calendar['corpId'], 'contact')->media;
        foreach ($push as $key => $val) {
            $pushContent = json_decode($val['pushContent'], true, 512, JSON_THROW_ON_ERROR);
            foreach ($pushContent as $k => $v) {
                if ($v['type'] === 'image') {
                    $new_file = dirname(__DIR__, 3) . '/storage/upload/static/' . $v['pic'];
                    $res = $upload->uploadImg(dirname(__DIR__, 3) . '/storage/upload/static/' . $new_file);
                    if ((int) $res['errcode'] === 0) {
                        $pushContent[$k]['pic_url'] = $res['url'];
                    } else {
                        throw new CommonException(ErrorCode::INVALID_PARAMS, '上传图片失败');
                    }
                    $pushContent[$k]['pic'] = file_full_url($v['pic']);
                }
            }
            $push[$key]['pushContent'] = $pushContent;
        }

        return [
            'employee' => $employee['name'],
            'rooms' => array_merge($roomArr),
            'push' => $push,
        ];
    }
}
