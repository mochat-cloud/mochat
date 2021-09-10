<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\QueueService\Tag;

use Hyperf\Contract\StdoutLoggerInterface;
use MoChat\App\Corp\Utils\WeWorkFactory;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;

class MarkTags
{
    public function handle($corpId, array $contact, array $tags)
    {
        if (empty($tags)) {
            return;
        }

        $weWorkContactApp = make(WeWorkFactory::class)->getContactApp($corpId);
        $employeeService = make(WorkEmployeeContract::class);
        $logger = make(StdoutLoggerInterface::class);
        $employee = $employeeService->getWorkEmployeeById((int) $contact['employeeId'], ['wx_user_id']);

        $tagData = [
            'userid' => $employee['wxUserId'],
            'external_userid' => $contact['wxExternalUserid'],
            'add_tag' => $tags,
        ];
        $wxTagRes = $weWorkContactApp->external_contact->markTags($tagData);
        if ($wxTagRes['errcode'] != 0) {
            // 记录错误日志
            $logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '请求企业微信客户打标签错误', date('Y-m-d H:i:s'), json_encode($tagData), json_encode($wxTagRes)));
        }
    }
}
