<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', function () {
    return 'Hello MoChat';
});
Router::get('/favicon.ico', function () {
    return '';
});

// 注册路由别名，兼容历史路由
Router::addRoute(['GET', 'POST'], '/weWork/callback', 'MoChat\App\Corp\Action\Dashboard\WeWorkCallback@handle');

Router::addRoute(['GET', 'POST'], '/load/{params?}', 'MoChat\App\OfficialAccount\Action\Operation\AuthRedirect@handle');
