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
use MoChat\App\User\Contract\UserContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;

/**
 * Class IndexLogic.
 */
class TopListLogic
{
    /**
     * @Inject
     * @var UserContract
     */
    protected $userService;

    /**
     * @Inject
     * @var WorkContactEmployeeContract
     */
    protected $workContactEmployeeService;

    public function sortHandle($a, $b)
    {
        return $b['total'] - $a['total'];
    }

    /**
     * 获取指定长度的排行榜成员.
     */
    public function getTopList(int $num): array
    {
        $user = user();
        $list = $this->workContactEmployeeService->countEmployeesWorkContactByCorpId($user['corpIds'][0]);
        $total = $this->workContactEmployeeService->countWorkContactEmployeesByCorpId($user['corpIds'][0], [1]);

        usort($list, [$this, 'sortHandle']);

        return [
            'total' => $total,
            'list' => array_slice($list, 0, $num),
        ];
    }
}
