<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\AutoTag\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\User\Contract\UserContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\AutoTag\Contract\AutoTagContract;
use MoChat\Plugin\AutoTag\Contract\AutoTagRecordContract;

/**
 * 自动打标签- 详情.
 *
 * Class Show.
 * @Controller
 */
class Show extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var AutoTagContract
     */
    protected $autoTagService;

    /**
     * @Inject
     * @var AutoTagRecordContract
     */
    protected $autoTagRecordService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var UserContract
     */
    protected $userService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/dashboard/autoTag/show", methods="get")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $id = $this->request->input('id');
        ## 查询数据
        return $this->handleData((int) $id);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'id' => 'required | integer | min:0 | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'id.required' => '活动ID 必填',
            'id.integer' => '活动ID 必需为整数',
            'id.min  ' => '活动ID 不可小于1',
        ];
    }

    /**
     * @throws \JsonException
     */
    private function handleData(int $id): array
    {
        ## 基本信息
        $autoTag = $this->autoTagService->getAutoTagById($id, ['id', 'name', 'employees', 'tag_rule', 'corp_id', 'on_off', 'create_user_id', 'created_at']);
        ## 处理创建者信息
        $username = $this->userService->getUserById($autoTag['createUserId']);
        $employees = $this->workEmployeeService->getWorkEmployeeByCorpIdWxUserIds((int) $autoTag['corpId'], explode(',', $autoTag['employees']), ['name', 'avatar']);
        foreach ($employees as $k => $v) {
            $employees[$k]['avatar'] = file_full_url($v['avatar']);
        }
        $tagRule = json_decode($autoTag['tagRule'], true, 512, JSON_THROW_ON_ERROR);
        $tags = array_column($tagRule[0]['tags'], 'tagname');
        $autoTag['tags'] = $tags;
        $autoTag['nickname'] = isset($username['name']) ? $username['name'] : '';
        $autoTag['employees'] = $employees;
        $autoTag['tagRule'] = json_decode($autoTag['tagRule'], true, 512, JSON_THROW_ON_ERROR);
        ## 数据统计
        $statistics['total_count'] = $this->autoTagRecordService->countAutoTagRecordByCorpIdAutoTagId((int) $autoTag['corpId'], $id);
        $statistics['today_count'] = $this->autoTagRecordService->countAutoTagRecordTodayByCorpIdAutoTagId((int) $autoTag['corpId'], $id);
        unset($autoTag['id'], $autoTag['corpId'], $autoTag['createUserId']);
        return [
            'auto_tag' => $autoTag,
            'statistics' => $statistics,
        ];
    }
}
