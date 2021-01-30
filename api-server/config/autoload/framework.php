<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
return [
    // 接口域名
    'app_domain' => env('APP_DOMAIN', 'http://127.0.0.1:9501'),
    // 前端域名
    'js_domain' => env('JS_DOMAIN', 'http://127.0.0.1:9501'),

    // MoChat\Framework\Middleware\JwtAuthMiddleware jwt路由验证白名单
    'auth_white_routes' => [
        '/user/auth', '/weWork/callback', '/agent/oauth', '/', '/{wxVerifyTxt}',
    ],

    // MoChat\Framework\Middleware\ResponseMiddleware 原生响应格式的路由
    'response_raw_routes' => [
        '/weWork/callback', '/', '/{wxVerifyTxt}',
    ],

    // 会话内容存档
    'work_message_config' => [
        // 客服联系人方式（图片）
        'serviceContactUrl' => '',
        'chatWhitelistIp'   => [
            '127.0.0.1',
        ],
    ],

    'wework' => [
        'callback_path' => '/weWork/callback',
        'config'        => [
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',

            'log' => [
                'level' => 'debug',
                'file'  => BASE_PATH . '/runtime/logs/wechat.log',
            ],
        ],
        'default' => [
            'provider' => 'app',
        ],
        'providers' => [
            'app' => [
                'name'    => \MoChat\Framework\Provider\WeWork\AppProvider::class,
                'service' => App\Service\CorpProvidersService::class, //  需要实现 MoChat\Framework\Contract\WeWork\AppConfigurable 接口
            ],
            'user' => [
                'name'    => \MoChat\Framework\Provider\WeWork\UserProvider::class,
                'service' => App\Service\CorpProvidersService::class, //  需要实现 MoChat\Framework\Contract\WeWork\UserConfigurable 接口
            ],
            'externalContact' => [
                'name'    => \MoChat\Framework\Provider\WeWork\ExternalContactProvider::class,
                'service' => App\Service\CorpProvidersService::class, //  需要实现 MoChat\Framework\Contract\WeWork\ExternalContactConfigurable 接口
            ],
            'agent' => [
                'name'    => \MoChat\Framework\Provider\WeWork\AgentProvider::class,
                'service' => App\Service\CorpProvidersService::class, //  需要实现 MoChat\Framework\Contract\WeWork\AgentConfigurable 接口
            ],
        ],
    ],
];
