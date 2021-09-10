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
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\AutoTag\Action\Dashboard\Traits\UpdateTrait;
use MoChat\Plugin\AutoTag\Contract\AutoTagContract;
use MoChat\Plugin\AutoTag\Contract\AutoTagRecordContract;

/**
 * 自动打标签- 详情-分时段打标签客户数据.
 *
 * Class ShowContactTime.
 * @Controller
 */
class ShowContactTime extends AbstractAction
{
    use ValidateSceneTrait;
    use UpdateTrait;

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
     * @var WorkContactContract
     */
    protected $workContactService;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/dashboard/autoTag/showContactTime", methods="get")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
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
        $this->validated($this->request->all());
        ## 接收参数
        $params = [
            'auto_tag_id' => $this->request->input('id'),
            'contact_name' => $this->request->input('contact_name'),
            'employee' => $this->request->input('employee', ''),
            'rule_id' => $this->request->input('rule_id', ''),
            'start_time' => $this->request->input('start_time', null),
            'end_time' => $this->request->input('end_time', null),
            'page' => $this->request->input('page', 1),
            'perPage' => $this->request->input('perPage', 10000),
        ];
        $this->updateContactId();
//        return [333];
        return $this->handleData($params);
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

    private function handleData(array $params): array
    {
        ## 查询数据
        $res = $this->autoTagRecordService->getAutoTagRecordByTimeSearch($params);
        ## 组织响应数据
        $data = [
            'page' => [
                'perPage' => $params['perPage'],
                'total' => 0,
                'totalPage' => 0,
            ],
            'list' => [],
        ];
        if (empty($res['data'])) {
            return $data;
        }
        ## 处理分页数据
        $data['page']['total'] = $res['total'];
        $data['page']['totalPage'] = $res['last_page'];
        $data['list'] = [];
        foreach ($res['data'] as $item) {
            $item['contactAvatar'] = file_full_url($item['contactAvatar']);
            $data['list'][] = $item;
        }
        return $data;
    }
}
