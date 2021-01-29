<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\User;

use App\Contract\CorpServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 子账户管理- 登录用户信息详情.
 *
 * Class LoginShow.
 * @Controller
 */
class LoginShow extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @RequestMapping(path="/user/loginShow", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 登录用户
        $user = user();
        ## 查询用户绑定企业信息
        $columns      = ['id', 'name', 'mobile', 'position', 'gender', 'email', 'avatar', 'thumb_avatar', 'telephone', 'alias', 'status', 'qr_code', 'external_position', 'address'];
        $workEmployee = ! isset($user['workEmployeeId']) || empty($user['workEmployeeId']) ? [] : $this->workEmployeeService->getWorkEmployeeById((int) $user['workEmployeeId'], $columns);
        $corp         = isset($user['corpIds']) && count($user['corpIds']) == 1 ? $this->corpService->getCorpById((int) $user['corpIds'][0], ['id', 'name']) : [];
        ## 组织响应数据
        return [
            'userId'                   => $user['id'],
            'userPhone'                => $user['phone'],
            'userName'                 => $user['name'],
            'userGender'               => $user['gender'],
            'userDepartment'           => $user['department'],
            'userPosition'             => $user['position'],
            'userLoginTime'            => $user['loginTime'],
            'userStatus'               => $user['status'],
            'employeeId'               => $user['workEmployeeId'],
            'employeeName'             => empty($workEmployee) ? '' : $workEmployee['name'],
            'employeeMobile'           => empty($workEmployee) ? '' : $workEmployee['mobile'],
            'employeePosition'         => empty($workEmployee) ? '' : $workEmployee['position'],
            'employeeGender'           => empty($workEmployee) ? 0 : $workEmployee['gender'],
            'employeeEmail'            => empty($workEmployee) ? '' : $workEmployee['email'],
            'employeeAvatar'           => empty($workEmployee) ? '' : file_full_url($workEmployee['avatar']),
            'employeeThumbAvatar'      => empty($workEmployee) ? '' : file_full_url($workEmployee['thumbAvatar']),
            'employeeTelephone'        => empty($workEmployee) ? '' : $workEmployee['telephone'],
            'employeeAlias'            => empty($workEmployee) ? '' : $workEmployee['alias'],
            'employeeStatus'           => empty($workEmployee) ? 0 : $workEmployee['status'],
            'employeeQrCode'           => empty($workEmployee) ? '' : $workEmployee['qrCode'],
            'employeeExternalPosition' => empty($workEmployee) ? '' : $workEmployee['externalPosition'],
            'employeeAddress'          => empty($workEmployee) ? '' : $workEmployee['address'],
            'corpId'                   => empty($corp) ? 0 : $corp['id'],
            'corpName'                 => empty($corp) ? '' : $corp['name'],
        ];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [];
    }
}
