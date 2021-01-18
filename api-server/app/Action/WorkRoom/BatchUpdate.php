<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkRoom;

use App\Contract\WorkRoomGroupServiceInterface;
use App\Contract\WorkRoomServiceInterface;
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
 * 客户群管理-批量修改客户群分组.
 *
 * Class BatchUpdate.
 * @Controller
 */
class BatchUpdate extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkRoomServiceInterface
     */
    protected $workRoomService;

    /**
     * @Inject
     * @var WorkRoomGroupServiceInterface
     */
    protected $workRoomGroupService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/workRoom/batchUpdate", methods="put")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 获取当前登录用户
        $user = user();
        ## 接收参数
        $workRoomGroupId = $this->request->input('workRoomGroupId');
        $workRoomIds     = $this->request->input('workRoomIds');
        ## 此操作登录用户必须选择登录企业
        if (! isset($user['corpIds']) || count($user['corpIds']) >= 2) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }
        ## 验证客户群分组的有效性
        if ($workRoomGroupId != 0) {
            $roomGroup = $this->workRoomGroupService->getWorkRoomGroupById((int) $workRoomGroupId, ['corp_id']);
            if (empty($roomGroup)) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '该分组信息不存在，不可操作');
            }
            if (! in_array($roomGroup['corpId'], $user['corpIds'])) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, '该分组不归属当前登录企业，不可操作');
            }
        }
        $roomIdArr = array_filter(explode(',', $workRoomIds));

        try {
            ## 数据入表
            $this->workRoomService->updateWorkRoomsById($roomIdArr, ['room_group_id' => $workRoomGroupId]);
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('%s [%s] %s', '更新客户群分组失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '更新客户群分组失败');
        }

        return [];
    }

    /**
     * 验证规则.
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'workRoomIds'     => 'required | string| min:1 | bail',
            'workRoomGroupId' => 'required | integer | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'workRoomIds.required'     => '客户群ID 必填',
            'workRoomIds.string'       => '客户群ID 必需为字符串',
            'workRoomIds.min'          => '客户群ID 字符串长度不可小于1',
            'workRoomGroupId.required' => '客户群分组ID 必填',
            'workRoomGroupId.integer'  => '客户群分组ID 必需为整数',
        ];
    }
}
