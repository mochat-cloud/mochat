<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\ContactBatchAdd;

use App\Logic\ContactBatchAdd\AllotLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 导入客户-勾选客户分配员工.
 *
 * Class Index.
 * @Controller
 */
class Allot extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var AllotLogic
     */
    protected $allotLogic;

    /**
     * @RequestMapping(path="/contactBatchAdd/allot", methods="post")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        $user                 = user();
        $params['id']         = $this->request->input('id');
        $params['employeeId'] = $this->request->input('employeeId');
        $this->validated($params);

        return $this->allotLogic->handle($params, $user);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'id'         => 'required|array',
            'employeeId' => 'required|numeric',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [];
    }
}
