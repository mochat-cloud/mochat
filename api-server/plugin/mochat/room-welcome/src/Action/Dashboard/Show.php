<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomWelcome\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomWelcome\Contract\RoomWelcomeContract;

/**
 * 入群欢迎语详情.
 *
 * Class Show.
 * @Controller
 */
class Show extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var RoomWelcomeContract
     */
    protected $roomWelcomeService;

    /**
     * @RequestMapping(path="/dashboard/roomWelcome/show", methods="get")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $id = $this->request->input('id');
        ## 查询数据
        $data = $this->roomWelcomeService->getRoomWelcomeById((int) $id);
        if (empty($data)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '当前入群欢迎语信息不存在');
        }
        if (! empty($data['msgComplex'])) {
            $msgComplex = json_decode($data['msgComplex'], true, 512, JSON_THROW_ON_ERROR);
            if (! empty($msgComplex['pic'])) {
                $msgComplex['pic'] = file_full_url($msgComplex['pic']);
            }
            $data['msgComplex'] = json_encode($msgComplex, JSON_THROW_ON_ERROR);
        }
        return $data;
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'id' => 'required | integer | min:0 | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'id.required' => '入群欢迎语ID 必填',
            'id.integer' => '入群欢迎语ID 必需为整数',
            'id.min  ' => '入群欢迎语ID 不可小于1',
        ];
    }
}
