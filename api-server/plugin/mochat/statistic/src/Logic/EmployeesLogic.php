<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Statistic\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;

/**
 * Class IndexLogic.
 */
class EmployeesLogic
{
    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployee;

    /**
     * 获取企业token和密钥.
     */
    public function getCorpTokenInfo(int $corpId): array
    {
        $res = $this->corpService->getCorpById($corpId);

        return [
            'wxCorpid' => $res['wxCorpid'],
            'contactSecret' => $res['contactSecret'],
        ];
    }

    /**
     * 获取企业成员列表.
     */
    public function getEmployees(int $corpId): array
    {
        return $this->workEmployee->getWorkEmployeesByCorpId($corpId);
    }

    /**
     * 根据id查询多条成员数据.
     * @return array
     */
    public function getEmployeesById(array $ids)
    {
        return $this->workEmployee->getWorkEmployeesById($ids);
    }
}
