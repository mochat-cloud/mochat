<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\RoomMessageBatchSend\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\RoomMessageBatchSend\Logic\StoreLogic;

/**
 * 客户群消息群发 - 新建群发消息.
 * @Controller
 */
class Store extends AbstractAction
{
    use ValidateSceneTrait;
    use AppTrait;

    /**
     * @Inject
     * @var StoreLogic
     */
    private $storeLogic;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/roomMessageBatchSend/store", methods="POST")
     */
    public function handle(): array
    {
        ## 参数验证
        $params = $this->request->all();
        $this->validated($params);
        $content = (array) json_decode($params['content'], true);
        ## 验证消息参数
        $content = $this->validContent($content);
        ## 接收参数
        $params = [
            'batchTitle' => $params['batchTitle'],
            'employeeIds' => $params['employeeIds'],
            'content' => $content,
            'sendWay' => $params['sendWay'],
            'definiteTime' => $params['definiteTime'] ?? null,
        ];
        $this->storeLogic->handle($params, user());
        return [];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'batchTitle' => 'required|max:100',
            'employeeIds' => 'required',
            'content' => 'required|json',
            'sendWay' => 'required|in:1,2',
            'definiteTime' => 'required_with:sendWay|date',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'batchTitle' => '群发名称 必填',
            'employeeIds' => '群主 必填',
            'content' => '群发消息必填',
            'sendWay' => '群发时间 必填',
        ];
    }

    /**
     * 验证消息内容参数.
     */
    protected function validContent(array $content): array
    {
        if (count($content) == 0) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '消息内容不能为空');
        }

        foreach ($content as $k => $item) {
            if (empty($item['msgType'])) {
                continue;
            }
            switch ($item['msgType']) {
                case 'text':
                    if (empty($item['content'])) {
                        throw new CommonException(ErrorCode::INVALID_PARAMS, '文字消息类型不能为空');
                    }
                    if (strlen($item['content']) > 400) {
                        throw new CommonException(ErrorCode::INVALID_PARAMS, '消息文本内容长度超过限制');
                    }
                    break;
                case 'image':
                    if (empty($item['media_id']) && empty($item['pic_url'])) {
                        throw new CommonException(ErrorCode::INVALID_PARAMS, '图片参数有误');
                    }
                    break;
                case 'link':
                    if (empty($item['title'])) {
                        throw new CommonException(ErrorCode::INVALID_PARAMS, '链接标题不能为空');
                    }
                    if (empty($item['url'])) {
                        throw new CommonException(ErrorCode::INVALID_PARAMS, '链接链接不能为空');
                    }
                    if (! empty($item['desc']) && strlen($item['desc']) > 250) {
                        throw new CommonException(ErrorCode::INVALID_PARAMS, '链接描述长度超过限制');
                    }
                    break;
                case 'miniprogram':
                    if (empty($item['title'])) {
                        throw new CommonException(ErrorCode::INVALID_PARAMS, '小程序消息标题不能为空');
                    }
                    if (strlen($item['title']) > 64) {
                        throw new CommonException(ErrorCode::INVALID_PARAMS, '小程序消息标题长度超过限制');
                    }
                    if (empty($item['pic_media_id'])) {
                        throw new CommonException(ErrorCode::INVALID_PARAMS, '小程序消息封面不能为空');
                    }
                    if (empty($item['appid'])) {
                        throw new CommonException(ErrorCode::INVALID_PARAMS, '小程序appid不能为空');
                    }
                    if (empty($item['page'])) {
                        throw new CommonException(ErrorCode::INVALID_PARAMS, '小程序page路径不能为空');
                    }
                    $content[$k]['pic_url'] = $item['pic_media_id'];
                    break;
                default:
                    throw new CommonException(ErrorCode::INVALID_PARAMS, '暂不支持的消息类型');
            }
        }
        return $content;
    }
}
