<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ContactBatchAdd\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Plugin\ContactBatchAdd\Contract\ContactBatchAddImportContract;

/**
 * 导入客户-未分配客户通知管理员.
 *
 * Class NoticeLeaderLogic
 */
class NoticeLeaderLogic
{
    /**
     * @Inject
     * @var ContactBatchAddImportContract
     */
    protected $contactBatchAddImportService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @param array $params 请求参数
     */
    public function handle(array $params): array
    {
        ## 获取未分配客户
        $result = $this->handleContact($params);
        if ($result) {
            $this->handleNotice($result);
        }
        return [];
    }

    /**
     * @param int $params 参数
     */
    private function handleContact(array $params): array
    {
        $corpId = $params['corpId'];

        $num = $this->contactBatchAddImportService->getContactBatchAddImportOptionWhereCount([
            ['corp_id', '=', $corpId],
            ['status', '=', 0],
            ['upload_at', '<=', date('Y-m-d 23:59:59', time() - $params['pendingTimeOut'] * 86400)],
        ]);

        return [
            'pendingLeaderId' => $params['pendingLeaderId'], ## 管理员ID
            'num' => $num, ## 未分配客户数
        ];
    }

    private function handleNotice(array $params): array
    {
        // TODO 通知
        var_dump($params);
        return [];
    }
}
