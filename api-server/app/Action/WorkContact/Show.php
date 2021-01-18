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

use App\Logic\WorkContact\ShowLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 查看客户详情基本信息.
 *
 * Class Show
 * @Controller
 */
class Show extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ShowLogic
     */
    protected $showLogic;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/workContact/show", methods="GET")
     */
    public function handle()
    {
        //接收参数
        $params['contactId']  = $this->request->input('contactId');
        $params['employeeId'] = $this->request->input('employeeId');
        //校验参数
        $this->validated($params);

        return $this->showLogic->handle($params);
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'contactId'  => 'required|integer|min:1|bail',
            'employeeId' => 'required|integer|min:1|bail',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
            'contactId.required'  => '客户id必传',
            'employeeId.required' => '员工id必传',
        ];
    }
}
