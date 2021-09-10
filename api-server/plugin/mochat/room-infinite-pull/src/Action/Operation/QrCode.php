<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomInfinitePull\Action\Operation;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomInfinitePull\Contract\RoomInfiniteContract;
use MoChat\Plugin\RoomQuality\Contract\RoomQualityRecordContract;
use Psr\Container\ContainerInterface;

/**
 * 无限拉群-H5-群活码.
 *
 * Class Show.
 * @Controller
 */
class QrCode
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var RoomInfiniteContract
     */
    protected $roomInfiniteService;

    /**
     * @Inject
     * @var RoomQualityRecordContract
     */
    protected $roomQualityRecordService;

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
     * @var WorkContactRoomContract
     */
    protected $workContactRoomService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * Show constructor.
     */
    public function __construct(RequestInterface $request, ContainerInterface $container)
    {
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * @RequestMapping(path="/operation/roomInfinitePull/qrCode", methods="get")
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 验证参数
        $params = $this->request->all();
        $this->validated($params);
        $roomInfinite = $this->roomInfiniteService->getRoomInfiniteById((int) $params['id'], ['title', 'describe', 'logo', 'qw_code', 'total_num']);
        $qwCode = json_decode($roomInfinite['qwCode'], true, 512, JSON_THROW_ON_ERROR);
        $totalNum = $roomInfinite['totalNum'];
        $qrcode = '';
        foreach ($qwCode as $k => $v) {
            $qwCode[$k]['status'] = 1;
            if ($totalNum < (int) $v['upper_limit']) {
                $qrcode = file_full_url($v['qrcode']);
                break;
            }
            $qwCode[$k]['status'] = 2;
            $totalNum -= (int) $v['upper_limit'];
        }
        $logo = empty($roomInfinite['logo']) ? '' : file_full_url($roomInfinite['logo']);
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 更新活动
            $this->roomInfiniteService->updateRoomInfiniteById((int) $params['id'], ['total_num' => $roomInfinite['totalNum'] + 1, 'qw_code' => json_encode($qwCode, JSON_THROW_ON_ERROR)]);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '无限拉群修改失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
        return ['roomName' => $roomInfinite['title'], 'describe' => $roomInfinite['describe'], 'logo' => $logo, 'qrcode' => $qrcode];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'id' => 'required | integer | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'id.required' => '质检id 必填',
            'id.integer' => '质检id 必须为整型',
        ];
    }
}
