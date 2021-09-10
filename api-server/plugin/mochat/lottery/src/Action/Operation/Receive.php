<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Lottery\Action\Operation;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\Lottery\Contract\LotteryContactRecordContract;

/**
 * H5 - 领取任务奖励.
 *
 * Class Receive.
 * @Controller
 */
class Receive extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var LotteryContactRecordContract
     */
    protected $lotteryContactRecordService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/operation/lottery/receive", methods="put")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $params = $this->request->all();
        $this->validated($params);

        return $this->updateContactRecord($params);
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
            'id.required' => '获奖记录ID 必填',
            'id.integer' => '获奖记录ID 必需为整数',
            'id.min  ' => '获奖记录ID 不可小于1',
        ];
    }

    /**
     * @param $params
     * @return array|array[]
     */
    private function updateContactRecord($params): array
    {
        ## 数据操作
        Db::beginTransaction();
        try {
            ## 抽奖记录
            $this->lotteryContactRecordService->updateLotteryContactRecordById((int) $params['id'], ['receive_status' => 1]);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '领奖失败', date('Y-m-d H:i:s'), $e->getMessage()));
            $this->logger->error($e->getTraceAsString());
            throw new CommonException(ErrorCode::SERVER_ERROR, $e->getMessage()); //'活动创建失败'
        }

        return [];
    }
}
