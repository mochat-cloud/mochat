<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkRoomAutoPull;

use App\Logic\WorkRoomAutoPull\ShowLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 自动拉群管理- 详情.
 *
 * Class Show.
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
     * @RequestMapping(path="/workRoomAutoPull/show", methods="GET")
     * @Middleware(PermissionMiddleware::class)
     * @return array 响应数据
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $params = [
            'workRoomAutoPullId' => $this->request->input('workRoomAutoPullId'),
        ];

        return $this->showLogic->handle((int) $params['workRoomAutoPullId']);
    }

    /**
     * 验证规则.
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'workRoomAutoPullId' => 'required | integer | min:0 | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'workRoomAutoPullId.required' => '自动拉群ID 必填',
            'workRoomAutoPullId.integer'  => '自动拉群ID 必需为整数',
            'workRoomAutoPullId.min'      => '自动拉群ID 不可小于1',
        ];
    }
}
