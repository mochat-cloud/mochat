<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\WorkFission\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContactContract;
use MoChat\Plugin\WorkFission\Contract\WorkFissionContract;

/**
 * 任务宝 - 获取统计数据.
 *
 * Class Store.
 * @Controller
 */
class Statistics extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @var \Laminas\Stdlib\RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var WorkFissionContactContract
     */
    protected $workFissionContactService;

    /**
     * @Inject
     * @var WorkFissionContract
     */
    protected $workFissionService;

    /**
     * Statistics constructor.
     */
    public function __construct(RequestInterface $request, WorkFissionContactContract $workFissionContactService, WorkFissionContract $workFissionService)
    {
        $this->request = $request;
        $this->workFissionContactService = $workFissionContactService;
        $this->workFissionService = $workFissionService;
    }

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/workFission/statistics", methods="get")
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
        $params = $this->request->all();
        $this->validated($params, 'store');

        return $this->getStatistics($user, $params);
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

    /**
     * @param $params
     * @throws \JsonException
     */
    private function getStatistics(array $user, $params): array
    {
        $data = [];
        $fissionIds = json_decode($params['fission_ids'], true, 512, JSON_THROW_ON_ERROR);
        $firstUser = $this->workFissionContactService->countWorkFissionContactByFissionIds($fissionIds);
        $data['user']['user_count'] = $this->workFissionContactService->countWorkFissionContactsByFissionId($fissionIds);
        $data['user']['loss_count'] = $this->workFissionContactService->countWorkFissionContactsLossByFissionId($fissionIds);
        $data['user']['insert_count'] = $data['user']['user_count'] - $data['user']['loss_count'];

        $data['user']['today_increase_count'] = $this->workFissionContactService->countWorkFissionContactsTodayByFissionId($fissionIds);
        $data['user']['new_increase_count'] = $this->workFissionContactService->countWorkFissionContactsNewByFissionId($fissionIds);
        $data['user']['new_loss_count'] = $this->workFissionContactService->countWorkFissionContactsNewLossByFissionId($fissionIds);
        $data['user']['net_increase'] = $data['user']['new_increase_count'] - $data['user']['new_loss_count'];
        $data['user']['invite_count'] = $this->workFissionContactService->sumWorkFissionContactsInviteByFissionId($fissionIds);
        $data['user']['fission_rate'] = $data['user']['user_count'] === 0 ? 0 : number_format(($data['user']['user_count'] - $firstUser) / $data['user']['user_count'] * 100, 2) . '%';
        $data['user']['insert_rate'] = $data['user']['user_count'] === 0 ? 0 : number_format(($data['user']['user_count'] - $data['user']['loss_count']) / $data['user']['user_count'] * 100, 2) . '%';
        $data['user']['share_rate'] = $data['user']['user_count'] === 0 ? 0 : number_format($data['user']['invite_count'] / $data['user']['user_count'] * 100, 2) . '%';
        $data['active'] = $this->workFissionService->getWorkFissionByCorpId($user['corpIds'][0], ['id', 'active_name']);
        $data['level']['first_level'] = $this->workFissionContactService->countWorkFissionContactsByLevel($fissionIds, (int) 1);
        $data['level']['second_level'] = $this->workFissionContactService->countWorkFissionContactsByLevel($fissionIds, (int) 2);
        $data['level']['third_level'] = $this->workFissionContactService->countWorkFissionContactsByLevel($fissionIds, (int) 3);
        $data['employee'] = $this->getEmployee($fissionIds);
        return $data;
    }

    private function getEmployee($fissionIds): array
    {
        $fission = $this->workFissionService->getWorkFissionsById($fissionIds, ['service_employees']);
        $emp = [];
        $k = 0;
        foreach ($fission as $key => $val) {
            foreach (json_decode($val['serviceEmployees'], true) as $ke => $va) {
                $emp[$k] = $va;
                ++$k;
            }
        }
        return $this->unique_arr($emp, 'id');
    }

    private function unique_arr($array2D, $item)
    {
        $temp = [];
        foreach ($array2D as $key => $value) {
            $temp[$value[$item]] = $value;
        }
        return array_merge($temp);
    }
}
