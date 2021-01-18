<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Model\Traits;

use App\Model\WorkEmployee;
use App\Tool\Rbac\Rbac;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Qbhy\HyperfAuth\Authenticatable;

trait UserTrait
{
    public function getId()
    {
        return $this->getKey();
    }

    public static function retrieveById($key): ?Authenticatable
    {
        $model = self::query()->find($key);
        ## requestSource[请求来源]:1-web端2-企业微信客户端
        [$model->corpIds, $model->workEmployeeId, $model->requestSource] = self::loginUserCopInfo($key);

        $rbacData = self::rbac($key);
        foreach ($rbacData as $rbacKey => $rbacVal) {
            $model->{$rbacKey} = $rbacVal;
        }
        return $model;
    }

    /**
     * 用户权限信息.
     * @param int $id 用户id
     * @return array ...
     */
    protected static function rbac(int $id): array
    {
        $res = [
            'roleId' => 0,
        ];
        $rbacTool = make(Rbac::class);

        ## 用户下角色信息
        $roles = $rbacTool->userRoles($id, ['id', 'name', 'data_permission', 'status']);
        if (empty($roles)) {
            return $res;
        }

        ## 用户角色id
        $res['roleId'] = $roles[0]['id'];
        return $res;
    }

    protected static function loginUserCopInfo(int $id): array
    {
        $headers = \Hyperf\Utils\Context::get(\Psr\Http\Message\ServerRequestInterface::class)->getHeaders();
        if (isset($headers['mochat-source-type'])
            && $headers['mochat-source-type'][0] == 'wechat-app'
        ) {
            $corpId = isset($headers['mochat-corp-id']) && is_numeric($headers['mochat-corp-id'][0]) ? (int) $headers['mochat-corp-id'][0] : 0;
            $data   = [[$corpId], self::employeeByIdCorpId($id, $corpId), 2];
        } else {
            $container = ApplicationContext::getContainer();
            $redis     = $container->get(Redis::class);
            $cacheData = $redis->get('mc:user.' . $id);
            if ($cacheData) {
                $cacheData = explode('-', $cacheData);
                $data      = [[(int) $cacheData[0]], (int) $cacheData[1]];
            } else {
                $data = self::employeeByLoginUserId($id);
            }
            array_push($data, 1);
        }

        return $data;
    }

    protected static function corpsById(int $id): array
    {
        $workEmployeeData = WorkEmployee::query()->where('log_user_id', $id)->get(['id', 'corp_id'])->toArray();
        return array_column($workEmployeeData, 'corpId');
    }

    protected static function employeeByIdCorpId(int $id, int $corpId): int
    {
        $res              = 0;
        $workEmployeeData = WorkEmployee::query()->where('log_user_id', $id)->where('corp_id', $corpId)->first(['id']);
        if (! empty($workEmployeeData)) {
            $res = $workEmployeeData->toArray()['id'];
        }
        return $res;
    }

    protected static function employeeByLoginUserId(int $loginUserId): array
    {
        $res              = [[], 0];
        $workEmployeeData = WorkEmployee::query()->where('log_user_id', $loginUserId)->first(['id', 'corp_id']);
        if (! empty($workEmployeeData)) {
            $workEmployeeData = $workEmployeeData->toArray();
            $res              = [[(int) $workEmployeeData['corpId']], $workEmployeeData['id']];
        }
        return $res;
    }
}
