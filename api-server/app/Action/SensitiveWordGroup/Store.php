<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\SensitiveWordGroup;

use App\Contract\CorpServiceInterface;
use App\Contract\SensitiveWordGroupServiceInterface;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 敏感词词库-添加分组.
 *
 * Class Store.
 * @Controller
 */
class Store extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @Inject
     * @var SensitiveWordGroupServiceInterface
     */
    protected $sensitiveWordGroupService;

    /**
     * 企业ID.
     * @var int
     */
    protected $corpId;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/sensitiveWordGroup/store", methods="post")
     * @return array 返回数组
     */
    public function handle(): array
    {
        $user = user();
        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 参数验证
        $this->corpId = (int) $user['corpIds'][0];
        $params       = $this->request->all();
        $this->validated($params, 'store');

        ## 创建
        $insertId = $this->createSensitiveWordGroup($params, $user);

        if (! $insertId) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '添加失败');
        }

        return [];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'name' => 'required | string | min:1 | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'name.required' => '敏感词分组名称 必填',
            'name.string'   => '敏感词分组名称 必须为字符串',
            'name.min'      => '敏感词分组名称 字符串长度不可小于1',
        ];
    }

    /**
     * 创建.
     * @param array $params 接收参数
     * @param array $user 用户数据
     * @return bool|int
     */
    private function createSensitiveWordGroup(array $params, array $user)
    {
        if (strpos($params['name'], ',')) {
            $name = explode(',', $params['name']);
        } else {
            $name = $params['name'];
        }

        $data = [];
        if (! is_array($name)) {
            $data = [
                'name'        => $name,
                'corp_id'     => $this->corpId,
                'user_id'     => $user['id'],
                'employee_id' => $user['workEmployeeId'],
            ];
            $this->nameIsUnique($data['name']);
            return $this->sensitiveWordGroupService->createSensitiveWordGroup($data);
        }

        foreach ($name as $key => $val) {
            $this->nameIsUnique($val);
            $data[$key] = [
                'name'        => $val,
                'corp_id'     => $this->corpId,
                'user_id'     => $user['id'],
                'employee_id' => $user['workEmployeeId'],
            ];
        }

        return $this->sensitiveWordGroupService->createSensitiveWordGroups($data);
    }

    /**
     * 验证分组名称是否存在.
     * @param string $name 分组名称
     * @return bool
     */
    private function nameIsUnique(string $name)
    {
        $client    = $this->container->get(SensitiveWordGroupServiceInterface::class);
        $existData = $client->getSensitiveWordGroupByNameCorpId($name, $this->corpId);
        if (! empty($existData)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, $name . '-该分组名称已存在');
        }
        return true;
    }
}
