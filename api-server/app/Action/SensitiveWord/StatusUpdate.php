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
 * 敏感词词库- 更新提交.
 *
 * Class StatusUpdate.
 * @Controller
 */
class StatusUpdate extends AbstractAction
{
    use ValidateSceneTrait;
    //use RequestTrait;

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
     * @RequestMapping(path="/sensitiveWord/statusUpdate", methods="put")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all(), 'statusUpdate');

        ## 接收参数
        $id     = (int) $this->request->input('sensitiveWordId');
        $status = (int) $this->request->input('status');

        try {
            ## 数据入表
            $this->sensitiveWordService->updateSensitiveWordById($id, ['status' => $status]);
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('%s [%s] %s', '敏感词状态更新失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '敏感词状态更新失败');
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
            'status'          => 'required | integer |  in:1,2, | bail',
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
            'status.required'          => '状态 必填',
            'status.integer'           => '状态 必须为整数',
            'status.in'                => '状态 值必须在列表内：[1,2]',
        ];
    }
}
