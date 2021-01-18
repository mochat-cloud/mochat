<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\QueueService\ChannelCode;

use App\Contract\ChannelCodeServiceInterface;
use App\Contract\CorpServiceInterface;
use App\QueueService\Traits\contactWayQrCodeTrait;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 新建渠道码 - 配置客户联系「联系我」方式
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
     * @param int $channelCodeId 渠道码表主键ID
     * @param int $corpId 企业微信授信ID
     * @param array $wxUserId 微信用户数组
     * @param bool $skipVerify 是否需要验证
     * @param string $wxConfigId 微信配置id
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(int $corpId, int $channelCodeId, array $wxUserId, bool $skipVerify, $wxConfigId): void
    {
        // @AsyncQueueMessage(pool="contact")
        ## 获取企业微信授信信息
        $corp = make(CorpServiceInterface::class)->getCorpById($corpId, ['id', 'wx_corpid']);
        ## 配置客户联系「联系我」方式
        ## 自定义state,区分客户添加渠道 此为渠道码
        $state = 'channelCode-' . $channelCodeId;
        ## 配置
        $config = [
            'skip_verify' => $skipVerify,
            'state'       => $state,
            'user'        => $wxUserId,
        ];
        if (empty($wxConfigId)) {
            //生成二维码上传到阿里云
            $AddQrCode = $this->AddQrCode($corp['wxCorpid'], 2, 2, $config);
            if ($AddQrCode['code'] == 0) {
                //更新到数据库
                $this->handleQrCode($AddQrCode['data'], $channelCodeId);
            }
        } else {
            $this->updateQrCode($corp['wxCorpid'], $wxConfigId, $config);
        }
    }

    /**
     * @param array $wxQrCode 联系我活码信息
     * @param int $channelCodeId 渠道码ID
     */
    private function handleQrCode(array $wxQrCode, int $channelCodeId)
    {
        try {
            ## 更新到数据表
            make(ChannelCodeServiceInterface::class)->updateChannelCodeById($channelCodeId, ['qrcode_url' => $wxQrCode['qrCodePath'], 'wx_config_id' => $wxQrCode['wxConfigId']]);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('%s [%s] %s', '渠道码更新二维码失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
        }
    }
}
