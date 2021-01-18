<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkRoomAutoPull;

use App\Constants\BusinessLog\Event;
use App\Constants\WorkRoomAutoPull\IsVerified;
use App\Contract\BusinessLogServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Contract\WorkRoomAutoPullServiceInterface;
use App\QueueService\WorkRoomAutoPull\QrCodeUpdateApply;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 自动拉群管理- 创建提交.
 *
 * Class StoreLogic
 */
class StoreLogic
{
    /**
     * @Inject
     * @var WorkRoomAutoPullServiceInterface
     */
    protected $workRoomAutoPullService;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var BusinessLogServiceInterface
     */
    private $businessLogService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @param array $params 请求参数
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \League\Flysystem\FileExistsException
     * @return array 响应数组
     */
    public function handle(array $params): array
    {
        ## 处理请求参数
        $params = $this->handleParams($params);
        ## 获取使用者的通讯录信息
        $employeeList = $this->getEmployeeList(json_decode($params['employees'], true));

        ## 数据操作
        Db::beginTransaction();
        try {
            ## 自动拉群
            $workRoomAutoPullId = $this->workRoomAutoPullService->createWorkRoomAutoPull($params);
            ## 记录业务日志
            $businessLog = [
                'business_id'  => $workRoomAutoPullId,
                'params'       => json_encode($params),
                'event'        => Event::ROOM_AUTO_PULL_CREATE,
                'operation_id' => user()['workEmployeeId'],
                'created_at'   => date('Y-m-d H:i:s'),
            ];
            $this->businessLogService->createBusinessLog($businessLog);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '自动拉群创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '自动拉群创建失败');
        }

        ## 生成-配置客户联系「联系我」方式-二维码
        $skipVerify = $params['is_verified'] == IsVerified::VERIFICATION ? false : true;

        (new QrCodeUpdateApply())->handle((int) $params['corp_id'], (int) $workRoomAutoPullId, array_column($employeeList, 'wxUserId'), $skipVerify);

        return [];
    }

    /**
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function handleParams(array $params): array
    {
        ## 使用成员(通讯录用户)
        $params['employees'] = json_encode(array_filter(explode(',', $params['employees'])));
        ## 客户标签
        $params['tags'] = json_encode(array_filter(explode(',', $params['tags'])));
        ## 创建时间
        $params['created_at'] = date('Y-m-d H:i:s');
        ## 更新时间
        $params['updated_at'] = date('Y-m-d H:i:s');

        return $params;
    }

    /**
     * @param array $employeeIds 企业员工通讯录集合
     * @return array 响应数组
     */
    private function getEmployeeList(array $employeeIds): array
    {
        $data = $this->workEmployeeService->getWorkEmployeesById($employeeIds, ['id', 'wx_user_id']);

        if (count($data) != count($employeeIds)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '使用者信息错误');
        }
        return $data;
    }
}
