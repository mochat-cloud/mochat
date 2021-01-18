<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkContact;

use App\Logic\WorkContact\LossContactLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 流失客户列表.
 *
 * Class LossContact
 * @Controller
 */
class LossContact extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var LossContactLogic
     */
    private $lossContactLogic;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/workContact/lossContact", methods="GET")
     */
    public function handle()
    {
        $corpId = user()['corpIds'];
        if (count($corpId) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请先选择企业');
        }

        //接收参数
        $params['employeeId'] = $this->request->input('employeeId');
        $params['page']       = $this->request->input('page');
        $params['perPage']    = $this->request->input('perPage');

        return $this->lossContactLogic->handle($params);
    }
}
