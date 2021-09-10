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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Context;
use MoChat\Framework\Middleware\Traits\Route;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Qbhy\HyperfAuth\Authenticatable;
use Qbhy\HyperfAuth\AuthManager;
use Qbhy\HyperfAuth\AuthMiddleware;
use Qbhy\HyperfAuth\Exception\UnauthorizedException;

class DashboardAuthMiddleware extends AuthMiddleware
{
    use Route;

    /**
     * @var string 路由白名单
     */
    protected $authWhiteRoutes;

    protected $guards = ['jwt'];

    public function __construct(ContainerInterface $container, ConfigInterface $config)
    {
        $this->auth = $container->get(AuthManager::class); // 父auth莫名无法注入成功
        $this->authWhiteRoutes = $config->get('framework.auth_white_routes', []);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->whiteListAuth($this->authWhiteRoutes)) {
            return $handler->handle($request);
        }

        foreach ($this->guards as $name) {
            $guard = $this->auth->guard($name);
            $user = $guard->user();

            if (! $user instanceof Authenticatable) {
                throw new UnauthorizedException("Without authorization from {$guard->getName()} guard", $guard);
            }

            $request = Context::override(ServerRequestInterface::class, function (ServerRequestInterface $request) use ($guard) {
                $token = $guard->parseToken();
                $jwt = $guard->getJwtManager()->parse($token);
                $uid = $jwt->getPayload()['uid'] ?? null;
                $user = $uid ? $guard->getProvider()->retrieveByCredentials($uid) : null;
                $userInfo = $user ? $user->toArray() : [];
                return $request->withAttribute('user', $userInfo);
            });
        }

        return $handler->handle($request);
    }
}
