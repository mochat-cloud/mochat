<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action;

use App\Contract\CorpServiceInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\Context;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\WeWork\Callback;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @Controller
 */
class WeWorkCallback extends AbstractAction
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/weWork/callback", methods="get,post")
     */
    public function handle()
    {
        // get回调参数处理
        $this->reloadRequest();

        $callBackClient = make(Callback::class);
        $this->logger   = $this->container->get(LoggerFactory::class)->get('wework-callback-info');

        $this->logger($callBackClient);
        try {
            return $callBackClient->handle();
        } catch (\Exception $e) {
            $this->logger->error(sprintf('微信回调失败::[%s]', $e->getMessage()));
        }
        return '';
    }

    /**
     * 回调调试日志.
     * @param callback $callBackClient ...
     */
    private function logger(Callback $callBackClient): void
    {
        $env = env('APP_ENV');
        if ($env === 'production') {
            return;
        }

        $params    = $this->request->all();
        $decParams = $callBackClient->getWxServer()->getMessage();
        $this->logger->info(sprintf('微信回调接收成功::加密信息[%s]; 解密信息[%s]', json_encode($params), json_encode($decParams)));

        ## dev环境打印
        $env === 'dev' && dump("微信回调接收成功::解密信息如下\n", $decParams);
    }

    private function reloadRequest(): void
    {
        if ($this->request->isMethod('POST')) {
            return;
        }
        $corId = (int) $this->request->input('cid', 0);
        if (! $corId) {
            return;
        }
        $corData = $this->container->get(CorpServiceInterface::class)->getCorpById($corId, ['id', 'wx_corpid']);
        if (empty($corData)) {
            return;
        }
        $request = Context::get(ServerRequestInterface::class)->withQueryParams(array_merge($this->request->query(), [
            'ToUserName' => $corData['wxCorpid'],
        ]));
        Context::set(ServerRequestInterface::class, $request);
    }
}
