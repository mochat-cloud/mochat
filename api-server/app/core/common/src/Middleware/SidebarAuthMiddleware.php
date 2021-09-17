<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Common\Middleware;

use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qbhy\HyperfAuth\Authenticatable;
use Qbhy\HyperfAuth\AuthMiddleware;
use Qbhy\HyperfAuth\Exception\UnauthorizedException;

class SidebarAuthMiddleware extends AuthMiddleware
{
    protected $guards = ['sidebar'];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        foreach ($this->guards as $name) {
            $guard = $this->auth->guard($name);
            $user = $guard->user();

            if (! $user instanceof Authenticatable) {
                throw new UnauthorizedException("Without authorization from {$guard->getName()} guard", $guard);
            }

            $request = Context::override(ServerRequestInterface::class, function (ServerRequestInterface $request) use ($user) {
                $userInfo = $user->toArray();
                // TODO 暂时兼容处理，需要优化 Logic 层不允许直接使用 user() 函数
                $userInfo['corpIds'] = [$userInfo['corpId']];
                $userInfo['workEmployeeId'] = $userInfo['id'];
                return $request->withAttribute('user', $userInfo);
            });
        }

        return $handler->handle($request);
    }
}
