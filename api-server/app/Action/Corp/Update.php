<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Corp;

use App\Action\Corp\Traits\RequestTrait;
use App\Contract\CorpServiceInterface;
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
 * 企业微信授权- 更新提交.
 *
 * Class Update.
 * @Controller
 */
class Update extends AbstractAction
{
    use ValidateSceneTrait;
    use RequestTrait;

    /**
     * @Inject
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/corp/update", methods="put")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all(), 'update');
        ## 接收参数
        $corpId = $this->request->input('corpId');
        $params = [
            'name'            => $this->request->input('corpName'),
            'wx_corpid'       => $this->request->input('wxCorpId'),
            'employee_secret' => $this->request->input('employeeSecret'),
            'contact_secret'  => $this->request->input('contactSecret'),
        ];
        ## 验证主键ID的有效性
        $corpInfo = $this->corpService->getCorpById($corpId);

        if (empty($corpInfo)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '当前企业信息不存在，不可操作');
        }

        try {
            ## 数据入表
            $this->corpService->updateCorpById($corpId, $params);
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('%s [%s] %s', '企业微信授信更新失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '企业微信授信更新失败');
        }
        return [];
    }
}
