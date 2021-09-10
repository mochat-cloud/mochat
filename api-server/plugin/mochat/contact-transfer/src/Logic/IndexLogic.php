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
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkMessage\Contract\WorkMessageContract;
use MoChat\Plugin\ContactTransfer\Contract\WorkTransferLogContract;
use MoChat\Plugin\ContactTransfer\Contract\WorkUnassignedContract;

/**
 * Class IndexLogic.
 */
class IndexLogic
{
    use AppTrait;

    /**
     * @Inject
     * @var WorkTransferLogContract
     */
    protected $workTransferLogService;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkUnassignedContract
     */
    protected $workUnassignedService;

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
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * 获分配在职或离职客户.
     * @param $params
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return array
     */
    public function handle($params)
    {
        $wx = $this->wxApp($params['corpId'], 'contact')->external_contact;
        $result = [];
        //离职分配
        if ($params['type'] == 1) {
            foreach ($params['list'] as $param) {
                $temp = $wx->transferCustomer([$param->contactWxId], $param->employeeWxId, $params['takeoverUserId'], '');
                $result[] = $temp;
                if ($temp['errcode'] === 0) {
                    $this->workTransferLogService->createWorkTransferLog([
                        'corp_id' => $params['corpId'],
                        'status' => 1,
                        'type' => 1,
                        'name' => $this->workContactService->getWorkContactByCorpIdWxExternalUserId($params['corpId'], $param->contactWxId),
                        'contact_id' => $param->contactWxId,
                        'handover_employee_id' => $param->employeeWxId,
                        'takeover_employee_id' => $params['takeoverUserId'],
                    ]);
                }
            }
        }
        //在职分配
        if ($params['type'] == 2) {
            foreach ($params['list'] as $param) {
                $temp = $wx->transferCustomer([$param->contactWxId], $param->employeeWxId, $params['takeoverUserId'], '');
                $result[] = $temp;
                if ($temp['errcode'] === 0) {
                    $this->workTransferLogService->createWorkTransferLog([
                        'corp_id' => $params['corpId'],
                        'status' => 2,
                        'type' => 1,
                        'name' => $this->workContactService->getWorkContactByCorpIdWxExternalUserId($params['corpId'], $param->contactWxId)['name'],
                        'contact_id' => $param->contactWxId,
                        'handover_employee_id' => $param->employeeWxId,
                        'takeover_employee_id' => $params['takeoverUserId'],
                    ]);
                }
            }
        }

        return $result;
    }
}
