<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkRoomGroup;

use App\Contract\WorkRoomGroupServiceInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 客户群分组管理- 更新提交.
 *
 * Class Update.
 * @Controller
 */
class Update extends AbstractAction
{
    use ValidateSceneTrait;

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
     * @RequestMapping(path="/workRoomGroup/update", methods="put")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $workRoomGroupId = $this->request->input('workRoomGroupId');
        $params          = [
            'name' => $this->request->input('workRoomGroupName'),
        ];
        ## 验证$workRoomGroupId
        $roomGroup = $this->workRoomGroupService->getWorkRoomGroupById((int) $workRoomGroupId, ['corp_id']);

        if (empty($roomGroup)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '该客户群分组不存在，不可操作');
        }
        ## 验证当前企业下客户群分组名称是否重复
        $roomGroups = $this->workRoomGroupService->getWorkRoomGroupsByCorpId((int) $roomGroup['corpId'], ['id', 'name']);

        $roomGroups = array_column($roomGroups, null, 'id');
        unset($roomGroups[$workRoomGroupId]);
        $roomGroupNameArr = array_column($roomGroups, 'name');
        if (in_array($params['name'], $roomGroupNameArr)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '该客户群分组名称已存在，不可更新');
        }

        try {
            ## 数据入表
            $this->workRoomGroupService->updateWorkRoomGroupById((int) $workRoomGroupId, $params);
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('%s [%s] %s', '客户群分组更新失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '客户群分组更新失败');
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
            'workRoomGroupId'   => 'required | integer| min:0 | bail',
            'workRoomGroupName' => 'required | string | min:1 | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'workRoomGroupId.required'   => '客户群分组ID 必填',
            'workRoomGroupId.integer'    => '客户群分组ID 必需为整数',
            'workRoomGroupId.min'        => '企业授信ID 不可小于1',
            'workRoomGroupName.required' => '分组名称 必填',
            'workRoomGroupName.string'   => '分组名称 必需是字符串类型',
            'workRoomGroupName.min'      => '分组名称 不可为空',
        ];
    }
}
