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
    // 接口URL
    'api_base_url' => env('API_BASE_URL', 'http://127.0.0.1:9501'),
    // 后台URL
    'dashboard_base_url' => env('DASHBOARD_BASE_URL', 'http://127.0.0.1:9501'),
    // 侧边栏URL
    'sidebar_base_url' => env('SIDEBAR_BASE_URL', 'http://127.0.0.1:9501'),
    // 运营工具URL
    'operation_base_url' => env('OPERATION_BASE_URL', 'http://127.0.0.1:9501'),

    // MoChat\Framework\Middleware\JwtAuthMiddleware jwt路由验证白名单
    'auth_white_routes' => [
        '/dashboard/user/auth', '/dashboard/weWork/callback', '/dashboard/agent/oauth', '/', '/dashboard/{wxVerifyTxt}', '/dashboard/workFission/inviteFriends', '/dashboard/workFission/taskData', '/dashboard/workFission/receive', '/dashboard/workFission/poster', '/dashboard/wxUserInfo', '/dashboard/clockIn/contactData', '/dashboard/clockIn/contactClockIn', '/dashboard/clockIn/receive', '/dashboard/clockIn/clockInRanking', '/dashboard/roomInfinite/qrCode', '/dashboard/lottery/contactData', '/dashboard/lottery/contactLottery', '/dashboard/lottery/receive', '/dashboard/roomCalendar/push', '/dashboard/radar/getRadar', '/dashboard/shopCode/areaCode', '/dashboard/shopCode/wechat', '/dashboard/publicAuthCallback', '/dashboard/roomFission/inviteFriends', '/dashboard/roomFission/receive', '/dashboard/roomFission/poster', '/dashboard/authCallback', '/dashboard/contactSop/getSopInfo', '/dashboard/getAppid', '/dashboard/openUserInfo', '/dashboard/contactSop/getSopTipInfo', '/dashboard/qyWxSession', '/dashboard/wxJsSdk/config', '/dashboard/roomSop/getSopInfo', '/dashboard/roomSop/logState', '/dashboard/roomSop/tipSopList', '/dashboard/roomSop/tipSopAddRoom', '/dashboard/roomSop/tipSopDelRoom', '/dashboard/roomCalendar', '/dashboard/roomManage', '/dashboard/roomQuality', '/dashboard/setRoomCalendar', '/dashboard/setRoomQuality', '/dashboard/delRoomQuality',
    ],

    // MoChat\Framework\Middleware\ResponseMiddleware 原生响应格式的路由
    'response_raw_routes' => [
        '/weWork/callback',
        '/dashboard/corp/weWorkCallback',
        '/',
        '/{wxVerifyTxt}',
        '/dashboard/officialAccount/authEventCallback',
        '/dashboard/{appId}/officialAccount/messageEventCallback',
        '/operation/officialAccount/authRedirect',
        '/sidebar/agent/auth',
    ],

    // 会话内容存档
    'work_message_config' => [
        // 客服联系人方式（图片）
        'serviceContactUrl' => '',
        'chatWhitelistIp' => [
            '127.0.0.1',
        ],
    ],

    'wework' => [
        'callback_path' => '/dashboard/corp/weWorkCallback',
        'config' => [
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',

            'log' => [
                'level' => 'debug',
                'file' => BASE_PATH . '/runtime/logs/wechat.log',
            ],
        ],
        'default' => [
            'provider' => 'app',
        ],
        'providers' => [
            'app' => [
                'name' => \MoChat\Framework\Provider\WeWork\AppProvider::class,
                'service' => MoChat\App\Corp\Service\CorpProvidersService::class, //  需要实现 MoChat\Framework\Contract\WeWork\AppConfigurable 接口
            ],
            'user' => [
                'name' => \MoChat\Framework\Provider\WeWork\UserProvider::class,
                'service' => MoChat\App\Corp\Service\CorpProvidersService::class, //  需要实现 MoChat\Framework\Contract\WeWork\UserConfigurable 接口
            ],
            'externalContact' => [
                'name' => \MoChat\Framework\Provider\WeWork\ExternalContactProvider::class,
                'service' => MoChat\App\Corp\Service\CorpProvidersService::class, //  需要实现 MoChat\Framework\Contract\WeWork\ExternalContactConfigurable 接口
            ],
            'agent' => [
                'name' => \MoChat\Framework\Provider\WeWork\AgentProvider::class,
                'service' => MoChat\App\Corp\Service\CorpProvidersService::class, //  需要实现 MoChat\Framework\Contract\WeWork\AgentConfigurable 接口
            ],
        ],
    ],

    'wechat_open_platform' => [
        // 开放平台第三方平台 APPID,
        'app_id' => env('WECHAT_OPEN_PLATFORM_APP_ID'),
        // 开放平台第三方平台 Secret,
        'secret' => env('WECHAT_OPEN_PLATFORM_SECRET'),
        // 开放平台第三方平台 Token,
        'token' => env('WECHAT_OPEN_PLATFORM_TOKEN'),
        // 开放平台第三方平台 AES Key
        'aes_key' => env('WECHAT_OPEN_PLATFORM_AES_KEY'),

        'log' => [
            'default' => 'dev', // 默认使用的 channel，生产环境可以改为下面的 prod
            'channels' => [
                // 测试环境
                'dev' => [
                    'driver' => 'daily',
                    'path' => BASE_PATH . '/runtime/logs/wechat.log',
                    'level' => 'debug',
                ],
            ],
        ],
    ],
];
