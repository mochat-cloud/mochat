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

    /**
     * 获取授权跳转连接.
     */
    public static function getAuthRedirectUrl(int $moduleType, int $id, array $query = []): string
    {
        $target = '';
        switch ($moduleType) {
            case 1:
                $url = '/auth/roomClockIn?id=' . $id;
                $query = http_build_query(['id' => $id]);
                $target = "/roomClockIn?{$query}";
                break;
            case 2:
                $url = '/auth/lottery?id=' . $id;
                $query = http_build_query(['id' => $id, 'source' => $query['source']]);
                $target = "/lottery?{$query}";
                break;
            case 3:
            case 4:
            case 5:
                $url = '/auth/shopCode?id=' . $id;
                $type = static::getShopCode($moduleType);
                $query = http_build_query(['id' => $id, 'type' => $type]);
                $target = "/shopCode?{$query}";
                break;
            case 6:
                $url = '/auth/radar?id=' . $id;
                $query = http_build_query(['id' => $id, 'type' => $query['type'], 'employee_id' => $query['employee_id'], 'target_id' => $query['target_id']]);
                $target = "/radar?{$query}";
                break;
            case 7:
                $url = '/auth/workFission?id=' . $id;
                $query = http_build_query(['id' => $id]);
                $target = "/workFission?{$query}";
                break;
            case 8:
                $url = '/auth/roomFission?id=' . $id;
                $query = http_build_query(['id' => $id, 'parent_union_id' => $query['parent_union_id'], 'wx_user_id' => $query['wx_user_id']]);
                $target = "/roomFission?{$query}";
                break;
        }

        $baseUrl = static::getOperationBaseUrl();

        if (empty($target)) {
            return $baseUrl;
        }

        return $baseUrl . $url . '&target=' . urlencode($target);
    }

    protected static function getShopCode($moduleType): int
    {
        switch ($moduleType) {
            case 3:
                $type = 1;
                break;
            case 4:
                $type = 2;
                break;
            case 5:
                $type = 3;
                break;
            default:
                $type = 1;
                break;
        }
        return $type;
    }
}
