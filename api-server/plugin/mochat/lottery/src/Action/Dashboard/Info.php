<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Lottery\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\Lottery\Contract\LotteryContactContract;
use MoChat\Plugin\Lottery\Contract\LotteryContactRecordContract;
use MoChat\Plugin\Lottery\Contract\LotteryContract;
use MoChat\Plugin\Lottery\Contract\LotteryPrizeContract;

/**
 * 抽奖活动- 修改详情.
 *
 * Class Show.
 * @Controller
 */
class Info extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var LotteryContract
     */
    protected $lotteryService;

    /**
     * @Inject
     * @var LotteryContactContract
     */
    protected $lotteryContactService;

    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var LotteryContactRecordContract
     */
    protected $lotteryContactRecordService;

    /**
     * @Inject
     * @var LotteryPrizeContract
     */
    protected $lotteryPrizeService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/dashboard/lottery/info", methods="get")
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
        $info = $this->lotteryService->getLotteryById((int) $id, ['id', 'name', 'description', 'type', 'time_type', 'start_time', 'end_time', 'contact_tags']);
        $info['contactTags'] = json_decode($info['contactTags'], true, 512, JSON_THROW_ON_ERROR);
        $prize = $this->lotteryPrizeService->getLotteryPrizeByLotteryId((int) $id, ['prize_set', 'is_show', 'exchange_set', 'draw_set', 'win_set', 'corp_card']);
        $prizeSet = json_decode($prize['prizeSet'], true, 512, JSON_THROW_ON_ERROR);
        foreach ($prizeSet as $key => $val) {
            $prizeSet[$key]['image'] = str_contains($val['image'], 'http') ? $val['image'] : file_full_url($val['image']);
        }
        $exchangeSet = json_decode($prize['exchangeSet'], true, 512, JSON_THROW_ON_ERROR);
        foreach ($exchangeSet as $key => $val) {
            $exchangeSet[$key]['employee_qr'] = file_full_url($val['employee_qr']);
        }
        $corp = json_decode($prize['corpCard'], true, 512, JSON_THROW_ON_ERROR);
        if (! empty($corp['logo'])) {
            $corp['logo'] = file_full_url($corp['logo']);
        }

        $prize['prizeSet'] = $prizeSet;
        $prize['exchangeSet'] = $exchangeSet;
        $prize['winSet'] = json_decode($prize['winSet'], true, 512, JSON_THROW_ON_ERROR);
        $prize['drawSet'] = json_decode($prize['drawSet'], true, 512, JSON_THROW_ON_ERROR);
        $prize['corpCard'] = $corp;
        return ['info' => $info, 'prize' => $prize];
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
}
