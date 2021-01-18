<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\QueueService\Traits;

use App\Logic\WeWork\AppTrait;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use League\Flysystem\Filesystem;

trait contactWayQrCodeTrait
{
    use AppTrait;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $respondData = [
        'code' => 0,
        'data' => [],
    ];

    /**
     * @param string $wxCorpId 企业微信企业ID
     * @param int $type 联系方式类型,1-单人, 2-多人
     * @param int $scene 场景，1-在小程序中联系，2-通过二维码联系
     * @param array $config 参照easyWechat文档
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return array 响应数组
     */
    public function AddQrCode(string $wxCorpId, int $type, int $scene, array $config): array
    {
        $contactWayClient = $this->wxApp($wxCorpId, 'contact')->contact_way;
        $addWxQrCode      = $contactWayClient->create($type, $scene, $config);
        if ($addWxQrCode['errcode'] != 0) {
            ## 记录错误日志
            $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '请求企业微信创建联系我二维码失败', date('Y-m-d H:i:s'), json_encode(['type' => $type, 'scene' => $scene, 'config' => $config]), json_encode($addWxQrCode)));
            $this->respondData['code'] = $addWxQrCode['errcode'];
            return $this->respondData;
        }
        ## 生成二维码上传到阿里云
        return $this->uploadQrCode($addWxQrCode);
    }

    /**
     * @param string $wxCorpId 企业微信企业ID
     * @param string $wxConfigId 二维码微信唯一标识
     * @param array $config 参照easyWechat文档
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return array 响应数组
     */
    public function updateQrCode(string $wxCorpId, string $wxConfigId, array $config): array
    {
        $contactWayClient = $this->wxApp($wxCorpId, 'contact')->contact_way;
        $upWxQrCode       = $contactWayClient->update($wxConfigId, $config);
        if ($upWxQrCode['errcode'] != 0) {
            ## 记录错误日志
            $this->logger->error(sprintf('%s [%s] 请求数据：%s 响应结果：%s', '请求企业微信更新联系我二维码失败', date('Y-m-d H:i:s'), json_encode(['wxConfigId' => $wxConfigId, 'config' => $config]), json_encode($upWxQrCode)));
            $this->respondData['code'] = $upWxQrCode['errcode'];
        } else {
            $this->respondData['code'] = 0;
        }
        return $this->respondData;
    }

    /**
     * @param array $wxQrCode 微信生成二维码响应数组
     * @return array 响应数组
     */
    public function uploadQrCode(array $wxQrCode): array
    {
        $filesystem   = make(Filesystem::class);
        $pathFileName = 'ContactWayClient/QrCode/' . time() . '.png';
        $stream       = file_get_contents($wxQrCode['qr_code'], true);

        try {
            ## 文件上传
            $filesystem->write($pathFileName, $stream);
            $this->respondData['data'] = [
                'qrCodePath' => $pathFileName,
                'wxConfigId' => $wxQrCode['config_id'],
            ];
        } catch (\Exception $e) {
            $this->logger->error(sprintf('%s [%s] %s', '联系我二维码失败阿里云上传失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            $this->respondData['code'] = 1;
        }
        return $this->respondData;
    }
}
