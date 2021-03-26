<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\ContactBatchAdd;

use App\Contract\RbacUserRoleServiceInterface;
use App\Contract\UserServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use Qbhy\HyperfAuth\AuthManager;
use Qbhy\SimpleJwt\JWTManager;

/**
 * 导入客户-导入提交.
 *
 * Class ImportStoreLogic
 */
class ImportStoreLogic
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
     * @var AuthManager
     */
    protected $authManager;

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
        // TODO 去重
        ## 验证员工id
        ## 认证标签
        
        ## 处理数据 分配员工
        $params = $this->handleParams($params);
        ## 数据入表
        ## 包括日志
        $this->insertData($params, $user);

        return [];
    }

    /**
     * @param array $params 请求参数
     * @return array 响应数组
     */
    private function handleParams(array $params): array
    {
        return $params;
    }

    /**
     * @param array $params 请求参数
     * @param array $user 当前登录用户信息
     */
    private function insertData(array $params, array $user)
    {
    }
}
