<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkRoom\Action\Dashboard\Group;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\WorkRoom\Contract\WorkRoomContract;
use MoChat\App\WorkRoom\Contract\WorkRoomGroupContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 客户群分组管理- 删除.
 *
 * Class Destroy.
 * @Controller
 */
class Destroy extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkRoomGroupContract
     */
    protected $workRoomGroupService;

    /**
     * @Inject
     * @var WorkRoomContract
     */
    protected $workRoomService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/workRoomGroup/destroy", methods="delete")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $workRoomGroupId = $this->request->input('workRoomGroupId');

        ## 验证$workRoomGroupId
        $roomGroup = $this->workRoomGroupService->getWorkRoomGroupById((int) $workRoomGroupId, ['id']);

        if (empty($roomGroup)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '该客户群分组不存在，不可操作');
        }
        ## 开启事务
        Db::beginTransaction();

        try {
            ## 客户群分组表
            $this->workRoomGroupService->deleteWorkRoomGroup((int) $workRoomGroupId);

            ## 将当前分组下的客户群移到未分组
            $this->workRoomService->updateWorkRoomsByRoomGroupId((int) $workRoomGroupId, ['room_group_id' => 0]);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '客户群分组删除失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '客户群分组删除失败');
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
            'workRoomGroupId' => 'required | integer| min:0 | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'workRoomGroupId.required' => '客户群分组ID 必填',
            'workRoomGroupId.integer' => '客户群分组ID 必需为整数',
            'workRoomGroupId.min' => '企业授信ID 不可小于1',
        ];
    }
}
