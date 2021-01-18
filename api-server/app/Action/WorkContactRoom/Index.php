<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkContactRoom;

use App\Logic\WorkContactRoom\IndexLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 客户群成员管理-列表.
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
     * @RequestMapping(path="/workContactRoom/index", methods="get")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $params = [
            'workRoomId' => $this->request->input('workRoomId'),
            'status'     => $this->request->input('status'),
            'name'       => $this->request->input('name'),
            'startTime'  => $this->request->input('startTime'),
            'endTime'    => $this->request->input('endTime'),
            'page'       => $this->request->input('page', 1),
            'perPage'    => $this->request->input('perPage', '10'),
        ];

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
            'workRoomId' => 'required | integer| min:0 | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'workRoomId.required' => '客户群ID 必填',
            'workRoomId.integer'  => '客户群ID 必需为整数',
            'workRoomId.min'      => '客户群ID 值不可小于1',
        ];
    }
}
