<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\Utils;

class Url
{
    /**
     * 获取 API 服务端 URL.
     */
    public static function getApiBaseUrl(): string
    {
        return rtrim(config('framework.api_base_url'), '/');
    }

    /**
     * 获取侧边栏 URL.
     */
    public static function getSidebarBaseUrl(): string
    {
        return rtrim(config('framework.sidebar_base_url'), '/');
    }

    /**
     * 获取后台面板URL.
     */
    public static function getDashboardBaseUrl(): string
    {
        return rtrim(config('framework.dashboard_base_url'), '/');
    }

    /**
     * 获取运营工具 URL.
     */
    public static function getOperationBaseUrl(): string
    {
        return rtrim(config('framework.operation_base_url'), '/');
    }
}
