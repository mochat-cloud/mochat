<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\QueueService\WorkRoomAutoPull;

use App\Contract\CorpServiceInterface;
use App\Contract\WorkRoomAutoPullServiceInterface;
use App\QueueService\Traits\contactWayQrCodeTrait;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 企业微信-配置客户联系「联系我」方式
 * Class QrCodeUpdateApply.
 */
class QrCodeUpdateApply
{
    use contactWayQrCodeTrait;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @AsyncQueueMessage(pool="contact")
     * @param int $workRoomAutoPullId 自动拉群表主键ID
     * @param int $corpId 企业微信授信ID
     * @param array $wxUserId 微信用户数组
     * @param bool $skipVerify 是否需要验证
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(int $corpId, int $workRoomAutoPullId, array $wxUserId, bool $skipVerify): void
    {
        ## 获取自动拉群详情
        $workRoomAutoPull = make(WorkRoomAutoPullServiceInterface::class)->getWorkRoomAutoPullById($workRoomAutoPullId, ['wx_config_id']);
        if (empty($workRoomAutoPull)) {
            ## 记录错误日志
            $this->logger->error(sprintf('%s [%s] workRoomAutoPullId: %s', '请求自动拉群信息为空', date('Y-m-d H:i:s'), $workRoomAutoPullId));
        } else {
            ## 获取企业微信授信信息
            $corp = make(CorpServiceInterface::class)->getCorpById($corpId, ['id', 'wx_corpid']);
            ## 配置客户联系「联系我」方式
            ## 自定义state,区分客户添加渠道
            $state = 'workRoomAutoPullId-' . $workRoomAutoPullId;
            ## 配置
            $config = [
                'skip_verify' => $skipVerify,
                'state'       => $state,
                'user'        => $wxUserId,
            ];
            if (empty($workRoomAutoPull['wxConfigId'])) {
                $AddQrCode = $this->AddQrCode($corp['wxCorpid'], 2, 2, $config);
                if ($AddQrCode['code'] == 0) {
                    ## 生成二维码上传到阿里云并更新到数据库
                    $this->handleQrCode($AddQrCode['data'], $workRoomAutoPullId);
                }
            } else {
                $this->updateQrCode($corp['wxCorpid'], $workRoomAutoPull['wxConfigId'], $config);
            }
        }
    }

    /**
     * @param array $wxQrCode 联系我活码信息
     * @param int $workRoomAutoPullId 自动拉群ID
     */
    private function handleQrCode(array $wxQrCode, int $workRoomAutoPullId)
    {
        try {
            ## 更新到数据表
            make(WorkRoomAutoPullServiceInterface::class)->updateWorkRoomAutoPullById($workRoomAutoPullId, ['qrcode_url' => $wxQrCode['qrCodePath'], 'wx_config_id' => $wxQrCode['wxConfigId']]);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('%s [%s] %s', '自动拉群更新二维码失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
        }
    }
}
