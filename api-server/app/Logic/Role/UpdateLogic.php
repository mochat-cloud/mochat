<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\Role;

use App\Contract\RbacRoleServiceInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 角色-修改.
 *
 * Class UpdateLogic
 */
class UpdateLogic
{
    /**
     * @Inject
     * @var RbacRoleServiceInterface
     */
    protected $roleService;

    /**
     * @var int 企业id
     */
    protected $corpId;

    /**
     * @var int 租户id
     */
    protected $tenantId;

    /**
     * @param array $user 登录用户信息
     * @param array $params 请求参数
     * @return array 响应数组
     */
    public function handle(array $user, array $params): array
    {
        ## 验证名称是否存在
        $this->tenantId = $user['tenantId'];
        $this->nameIsUnique($params['name'], (int) $params['roleId']);

        $this->corpId = $user['corpIds'][0];
        ## 处理数据
        $data = $this->getRoleById($user, $params);

        ## 修改角色
        $res = $this->roleService->updateRbacRoleById((int) $params['roleId'], $data);
        if (! $res) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '角色更新失败');
        }

        return [];
    }

    /**
     * 更新角色.
     * @param array $user 用户信息
     * @param array $params 参数
     * @return array 响应数组
     */
    private function getRoleById(array $user, array $params): array
    {
        ## 更新数据
        $data = [
            'name'         => $params['name'],
            'remarks'      => $params['remarks'],
            'operate_id'   => $user['id'],
            'operate_name' => $user['name'],
        ];

        ## 根据角色id获取角色数据
        $role = $this->roleService->getRbacRolesByIdTenantId((int) $params['roleId'], (int) $user['tenantId']);

        if (empty($role)) {
            throw new CommonException(ErrorCode::URI_NOT_FOUND, '角色不存在');
        }

        if (empty($role['dataPermission'])) {
            $data['data_permission'] = json_encode([['corpId' => $this->corpId]]);
            return $data;
        }

        //不为空的话 找对应企业ID，存在修改，找到对应的企业id修改权限，不存在 追加企业+权限
        $dataPermission = json_decode($role['dataPermission'], true);

        $flag = 0;
        foreach ($dataPermission as $k => &$v) {
            if ($v['corpId'] == $this->corpId) {
                $flag                = 1;
                $v['permissionType'] = $params['dataPermission'];
            }
        }

        if (! empty($flag)) {
            $data['data_permission'] = json_encode($dataPermission);
            return $data;
        }

        array_push($dataPermission, ['corpId' => $this->corpId, 'permissionType' => $params['dataPermission']]);
        $data['data_permission'] = json_encode($dataPermission);

        return $data;
    }

    /**
     * 验证角色名称是否存在.
     *
     * @param string $name 角色名称
     * @param int $roleId 角色id
     * @return bool
     */
    private function nameIsUnique(string $name, int $roleId)
    {
        $existData = $this->roleService->getRoleByNameTenantId($name, $this->tenantId, ['id']);
        if (empty($existData)) {
            return true;
        }

        $existData['id'] == $roleId && $existData = [];

        if (! empty($existData)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, $name . '-该角色已存在');
        }
        return true;
    }
}
