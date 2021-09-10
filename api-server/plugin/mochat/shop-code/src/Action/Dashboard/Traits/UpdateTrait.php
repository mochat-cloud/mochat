<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\ShopCode\Action\Dashboard\Traits;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\Corp\Logic\AppTrait;
use MoChat\App\Utils\File;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Plugin\ShopCode\Contract\ShopCodeContract;
use Psr\Container\ContainerInterface;

trait UpdateTrait
{
    use AppTrait;

    /**
     * @Inject
     * @var CorpContract
     */
    protected $corpService;

    /**
     * @Inject
     * @var ShopCodeContract
     */
    protected $shopCodeService;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(RequestInterface $request, ContainerInterface $container)
    {
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * 生成二维码 二维码本地保存.
     * @throws \JsonException
     */
    private function handleQrcode(array $user, string $wxUserId, int $id): array
    {
        ##EasyWeChat配置客户联系「联系我」方式
        $res = $this->wxApp($user['corpIds'][0], 'contact')->contact_way->create(2, 2, [
            'skip_verify' => true,
            'state' => 'shopCode-' . $id,
            'user' => [$wxUserId],
        ]);
        if ($res['errcode'] !== 0) {
            $this->logger->error(sprintf('生成二维码 失败::[%s]', json_encode($res, JSON_THROW_ON_ERROR)));
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请求失败，部分员工未进行实名认证，请实名后重试'); //$res['msg']"生成二维码失败"
        }
        ## 二维码本地保存
        $pic = File::uploadUrlImage($res['qr_code'], 'image/shopCode/' . strval(microtime(true) * 10000) . '_' . uniqid() . '.jpg');
        $this->shopCodeService->updateShopCodeById($id, ['employee_qrcode' => json_encode(['wx_qrcode' => $res['qr_code'], 'local_qrcode' => $pic], JSON_THROW_ON_ERROR)]);
        return [];
    }
}
