<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkEmployee;

use App\QueueService\WorkEmployee\EmployeeApply;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;

/**
 * 同步企业成员 - 页面.
 * @Controller
 */
class SynEmployee extends AbstractAction
{
    /**
     * @var EmployeeApply
     */
    protected $employeeApply;

    /**
     * @RequestMapping(path="/workEmployee/synEmployee", methods="PUT")
     */
    public function handle()
    {
        $params['corpIds'] = user()['corpIds'];
        //同步成员信息
        make(EmployeeApply::class)->handle($params['corpIds']);
    }
}
