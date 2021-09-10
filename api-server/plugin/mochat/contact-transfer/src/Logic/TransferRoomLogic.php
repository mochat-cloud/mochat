<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactTransfer\Logic;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkContact\Contract\WorkContactRoomContract;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkMessage\Contract\WorkMessageContract;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\ContactTransfer\Contract\WorkTransferLogContract;
use MoChat\Plugin\ContactTransfer\Contract\WorkUnassignedContract;
use Psr\SimpleCache\CacheInterface;

/**
 * Class TransferRoomLogic.
 */
class TransferRoomLogic
{
    use AppTrait;

    /**
     * @Inject
     * @var WorkTransferLogContract
     */
    protected $workTransferLogService;

    /**
     * @Inject
     * @var WorkContactRoomContract
     */
    protected $workContactRoomService;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var WorkContactEmployeeContract
     */
    protected $workContactEmployeeService;

    /**
     * @Inject
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @Inject
     * @var WorkContactTagContract
     */
    protected $workContactTagService;

    /**
     * @Inject
     * @var WorkContactTagPivotContract
     */
    protected $workContactTagPivotService;

    /**
     * @var WorkMessageContract
     */
    protected $workMessageService;

    /**
     * @Inject
     * @var WorkUnassignedContract
     */
    protected $workUnassignedService;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function setStateAll()
    {
        $logId = $this->cache->get('log_id');
        if (! $logId) {
            //默认0
            $this->cache->set('log_id', 0, 3600);
            $logId = $this->cache->get('log_id');
        }

        $this->logger->info('当前状态缓存id：' . $logId);

        $logData = $this->workTransferLogService->getNeedStateLogs($logId);

        if ($logData != false) {
            $nowId = $logData['id'];
            $this->cache->set('log_id', $nowId, 3600);
        } else {
            $this->cache->set('log_id', 0, 3600);
            return;
        }

        ##EasyWeChat 离职继承查询客户接替状态
        $wx = $this->wxApp($logData['corpId'], 'contact')->external_contact;
        $stateData = $wx->transferResult($logData['handoverEmployeeId'], $logData['takeoverEmployeeId']);
        if ($stateData['errcode'] === 0) {
            $stateData = $stateData['customer'];
            foreach ($stateData as $stateDatum) {
                if ($logData['contactId'] == $stateDatum['external_userid']) {
                    $logData['state'] = $stateDatum['status'];
                }
            }

            $this->workTransferLogService->updateWorkTransferLogById($logData['id'], $logData);
        }
    }

    /**
     * 获取离职待分配群列表.
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return array
     */
    public function transferRoom(array $params)//transferGroupChat
    {
        $res = $this->wxApp($params['corpId'], 'contact')->external_contact->transferGroupChat($params['list'], $params['takeoverUserId']);
        if ($res['errcode'] !== 0) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '获取离职待分配群列表失败');
        }
        return $res['failed_chat_list'];
    }
}
