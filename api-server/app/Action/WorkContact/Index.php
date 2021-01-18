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

use App\Logic\WorkContact\IndexLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 客户列表.
 *
 * Class Index
 * @Controller
 */
class Index extends AbstractAction
{
    /**
     * @Inject
     * @var IndexLogic
     */
    protected $indexLogic;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/workContact/index", methods="GET")
     */
    public function handle()
    {
        $corpId = user()['corpIds'];
        if (count($corpId) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请先选择企业');
        }

        //接收参数
        $params = $this->request->inputs(
            [
                'keyWords', 'remark', 'fieldId', 'fieldType', 'fieldValue', 'gender', 'addWay', 'roomId', 'groupNum',
                'employeeId', 'startTime', 'endTime', 'businessNo', 'page', 'perPage',
            ],
            [
                'page' => 1, 'perPage' => 20,
            ]
        );

        return $this->indexLogic->handle($params);
    }
}
