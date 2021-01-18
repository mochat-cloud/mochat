<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Greeting;

use App\Contract\GreetingServiceInterface;
use App\Middleware\PermissionMiddleware;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 欢迎语-详情.
 *
 * Class Destroy
 * @Controller
 */
class Destroy extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var GreetingServiceInterface
     */
    private $greetingService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/greeting/destroy", methods="DELETE")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 获取登录用户信息
        $user = user();
        ## 接收参数
        $greetingId = $this->request->input('greetingId');
        ## 获取欢迎语信息
        $greeting = $this->greetingService->getGreetingById((int) $greetingId, ['id', 'corp_id']);
        if (empty($greeting)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '此欢迎语不存在，不可操作');
        }
        ## 判断欢迎语归属企业
        if ($user['corpIds'][0] != $greeting['corpId']) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '此欢迎语不归属当前企业，不可操作');
        }
        ## 数据操作
        try {
            ## 删除数据
            $this->greetingService->deleteGreeting((int) $greetingId);
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('%s [%s] %s', '欢迎语删除失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '欢迎语删除失败');
        }
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
            'greetingId' => 'required | integer | min:0 | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'greetingId.required' => '欢迎语ID 必填',
            'greetingId.integer'  => '欢迎语ID 必需为整数',
            'greetingId.min  '    => '欢迎语ID 不可小于1',
        ];
    }
}
