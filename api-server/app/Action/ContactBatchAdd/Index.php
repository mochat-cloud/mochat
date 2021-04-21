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

use App\Logic\ContactBatchAdd\IndexLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Validation\Rule;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 导入客户-列表.
 *
 * Class Index.
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var IndexLogic
     */
    protected $indexLogic;

    /**
     * @RequestMapping(path="/contactBatchAdd/index", methods="get")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        $params['status']    = $this->request->input('status', null);
        $params['searchKey'] = $this->request->input('searchKey', null);
        $params['recordId']  = $this->request->input('recordId', 0);
        $this->validated($params);
        $params['corpId'] = user()['corpIds'][0];

        return $this->indexLogic->handle($params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'status' => [
                Rule::in([null, '0', '1', '2', '3']),
            ],
            'searchKey' => 'max:255',
            'recordId'  => 'numeric',
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
