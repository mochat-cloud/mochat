<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactSop\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\User\Contract\UserContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkContact\Contract\WorkContactTagContract;
use MoChat\App\WorkContact\Contract\WorkContactTagPivotContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\App\WorkMessage\Contract\WorkMessageContract;
use MoChat\Plugin\ContactSop\Contract\ContactSopContract;
use MoChat\Plugin\ContactTransfer\Contract\WorkUnassignedContract;

/**
 * Class InfoLogic.
 */
class InfoLogic
{
    /**
     * @Inject
     * @var ContactSopContract
     */
    protected $contactSopService;

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
     * @var UserContract
     */
    protected $userService;

    /**
     * @param $params
     * @throws \JsonException
     * @return array
     */
    public function handle($params)
    {
        $res = $this->contactSopService->getContactSopById((int) $params['id']);

        $employees = $this->workEmployeeService->getWorkEmployeesById(json_decode($res['employeeIds'], true, 512, JSON_THROW_ON_ERROR));

        $employeeArr = [];

        foreach ($employees as $item) {
            $employeeArr[] = [
                'employeeId' => $item['id'],
                'name' => $item['name'],
                'wxUserId' => $item['wxUserId'],
            ];
        }
        //处理创建者信息
        $username = $this->userService->getUserById($res['creatorId']);
        return [
            'id' => $res['id'],
            'name' => $res['name'],
            'creatorName' => isset($username['name']) ? $username['name'] : '',
            'employees' => $employeeArr,
            'setting' => json_decode($res['setting'], true, 512, JSON_THROW_ON_ERROR),
            'createTime' => $res['createdAt'],
        ];
    }
}
