<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Lottery\Logic;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Utils\File;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\Lottery\Contract\LotteryContract;
use MoChat\Plugin\Lottery\Contract\LotteryPrizeContract;

/**
 * 抽奖活动-修改.
 *
 * Class UpdateLogic
 */
class UpdateLogic
{
    /**
     * @Inject
     * @var LotteryContract
     */
    protected $lotteryService;

    /**
     * @Inject
     * @var LotteryPrizeContract
     */
    protected $lotteryPrizeService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * StoreLogic constructor.
     */
    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @throws \Exception
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 处理参数
        $data = $this->handleParam($user, $params);
        ## 创建活动
        $this->updateLottery((int) $params['lottery']['id'], $data);

        return [];
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @throws \Exception
     * @return array 响应数组
     */
    private function handleParam(array $user, array $params): array
    {
        ## 基本信息
        $data['lottery'] = [
            'description' => $params['lottery']['description'],
            'start_time' => isset($params['lottery']['start_time']) ? $params['lottery']['start_time'] : '',
            'end_time' => isset($params['lottery']['end_time']) ? $params['lottery']['end_time'] : '',
        ];

        ##奖品
        if (isset($params['prize']['corp_card']['logo'])) {
            $params['prize']['corp_card']['logo'] = File::uploadBase64Image($params['prize']['corp_card']['logo'], 'image/lottery/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
        }
        $data['prize'] = [
            'prize_set' => json_encode($params['prize']['prize_set'], JSON_THROW_ON_ERROR),
            'corp_card' => isset($params['prize']['corp_card']) ? json_encode($params['prize']['corp_card'], JSON_THROW_ON_ERROR) : '{}',
        ];
        return $data;
    }

    /**
     * 奖品设置.
     * @throws \Exception
     */
    private function prizeSet(array $prizeSet): array
    {
        foreach ($prizeSet as $key => $val) {
            $prizeSet[$key]['image'] = File::uploadBase64Image($val['image'], 'image/lottery/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
        }
        return $prizeSet;
    }

    /**
     * 兑奖设置.
     * @throws \Exception
     */
    private function exchangeSet(array $exchangeSet): array
    {
        foreach ($exchangeSet as $key => $val) {
            empty($val['employee_qr']) || $exchangeSet[$key]['employee_qr'] = File::uploadBase64Image($val['employee_qr'], 'image/lottery/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
        }
        return $exchangeSet;
    }

    /**
     * 修改活动.
     * @param array $params 参数
     * @return array 响应数值
     */
    private function updateLottery(int $id, array $params): array
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 创建活动
            $this->lotteryService->updateLotteryById($id, $params['lottery']);
            $prize = $this->lotteryPrizeService->getLotteryPrizeByLotteryId($id, ['id']);
            $this->lotteryPrizeService->updateLotteryPrizeById((int) $prize['id'], $params['prize']);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '活动修改失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }
        return [];
    }
}
