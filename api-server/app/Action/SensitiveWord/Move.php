<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\SensitiveWord;

use App\Action\Corp\Traits\RequestTrait;
use App\Contract\CorpServiceInterface;
use App\Contract\SensitiveWordServiceInterface;
use App\Logic\User\Traits\UserTrait;
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
 * 敏感词词库- 移动提交.
 *
 * Class Move.
 * @Controller
 */
class Move extends AbstractAction
{
    use ValidateSceneTrait;
    //use RequestTrait;
    //use UserTrait;

    /**
     * @Inject
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @Inject
     * @var SensitiveWordServiceInterface
     */
    protected $sensitiveWordService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/sensitiveWord/move", methods="put")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all(), 'update');

        ## 接收参数
        $id      = (int) $this->request->input('sensitiveWordId');
        $groupId = (int) $this->request->input('groupId');

        try {
            ## 数据入表
            $updateRes = $this->sensitiveWordService->updateSensitiveWordById($id, ['group_id' => $groupId]);
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('%s [%s] %s', '敏感词移动失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '敏感词移动失败');
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
            'sensitiveWordId' => 'required | numeric | bail',
            'groupId'         => 'required | numeric | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'sensitiveWordId.required' => '敏感词id 必填',
            'sensitiveWordId.numeric'  => '敏感词id 必须为数字类型',
            'groupId.required'         => '敏感词分组id 必填',
            'groupId.numeric'          => '敏感词分组id 必须为数字类型',
        ];
    }
}
