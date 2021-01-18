<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\User;

use App\Contract\RbacUserRoleServiceInterface;
use App\Contract\UserServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 子账户管理- 更新提交.
 *
 * Class UpdateLogic
 */
class UpdateLogic
{
    /**
     * @Inject
     * @var UserServiceInterface
     */
    protected $userService;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $employeeClient;

    /**
     * @Inject
     * @var RbacUserRoleServiceInterface
     */
    protected $rbacUserRoleService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @param array $params 请求参数
     * @param array $user 当前登录用户信息
     * @return array 响应数组
     */
    public function handle(array $params, array $user): array
    {
        ## 验证userId的有效性
        $userInfo = $this->userService->getUserById((int) $params['userId'], ['id']);
        if (empty($userInfo)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '当前账户不存在，不可操作');
        }
        ## 判断手机号是否重复
        $phoneUser = $this->userService->getUsersByPhone([$params['phone']], ['id']);
        if (! empty($phoneUser) && (count($phoneUser) >= 2 || $phoneUser[0]['id'] != $params['userId'])) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '手机号已存在，不可重复创建');
        }
        ## 创建入表数据
        $sqlData = $this->createSalData($params, $user);

        ## 数据入表
        $this->insertData($params, $sqlData);

        return [];
    }

    /**
     * @param array $params 请求参数
     * @param array $user 当前登录用户信息
     * @return array 响应数组
     */
    private function createSalData(array $params, array $user): array
    {
        $data   = [];
        $userId = (int) $params['userId'];
        ## 角色信息
        $oldRoleInfo = $this->findOldRoleInfo($userId);
        if (isset($oldRoleInfo['id'])) {
            if ($oldRoleInfo['id'] != $params['roleId']) {
                $data['deleteRole'] = [
                    'roleId' => (int) $oldRoleInfo['id'],
                ];
                empty($params['roleId']) || $data['addRole'] = [
                    'user_id'    => $userId,
                    'role_id'    => $params['roleId'],
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }
        } else {
            empty($params['roleId']) || $data['addRole'] = [
                'user_id'    => $userId,
                'role_id'    => $params['roleId'],
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }
        unset($params['roleId'], $params['userId']);
        ## 用户信息
        $data['updateUser'] = [
            'where' => ['id' => $userId],
            'data'  => $params,
        ];
        return $data;
    }

    /**
     * @param int $userId 用户ID
     * @return array 响应数组
     */
    private function findOldRoleInfo(int $userId): array
    {
        $roleInfo = $this->rbacUserRoleService->getRbacUserRoleByUserId($userId, ['id']);
        return $roleInfo ?? [];
    }

    /**
     * @param array $params 请求参数
     * @param array $sqlData 入表数据
     */
    private function insertData(array $params, array $sqlData)
    {
        $userId       = $sqlData['updateUser']['where']['id'];
        $employeeData = $this->employeeClient->getWorkEmployeesByMobile($params['phone'], ['id']);
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 用户
            $this->userService->updateUserById($userId, $sqlData['updateUser']['data']);
            ## 员工通讯录
            empty($employeeData) || $this->employeeClient->updateWorkEmployeesCaseIds(array_map(static function ($item) use ($userId) {
                $item['log_user_id'] = $userId;
                return $item;
            }, $employeeData));
            ## 旧角色
            isset($sqlData['deleteRole']) && $this->rbacUserRoleService->deleteRbacUserRole($sqlData['deleteRole']['roleId']);
            ## 新角色
            isset($sqlData['addRole']) && $this->rbacUserRoleService->createRbacUserRole($sqlData['addRole']);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '账户更新失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, '账户更新失败');
        }
    }
}
