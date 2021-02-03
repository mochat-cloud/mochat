<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\Agent;


use App\Contract\WorkAgentServiceInterface;
use App\Logic\WeWork\AppTrait;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 应用-添加.
 * @author wangpeiyan732
 *
 * Class IndexLogic
 */
class StoreLogic
{
    use AppTrait;
    /**
     * @Inject
     * @var WorkAgentServiceInterface
     */
    private $workAgentClient;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;




    /**
     * 添加应用
     * @param array $user
     * @param array $params
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function handle(array $params, array $user): array
    {
        //开启事务
        Db::beginTransaction();
        try {
            // 创建应用
            $agentId = $this->createAgent($params, $user);
            // 更新应用
            $this->updateAgent($agentId, (int) $params['wxAgentId']);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollBack();
            $this->logger->error(sprintf('%s [%s] %s', '应用创建失败', date('Y-m-d H:i:s'), $e->getMessage()));
            throw new CommonException(ErrorCode::SERVER_ERROR, "应用创建失败,请输入正确的应用id和应用secret");
        }

        return  [];
    }

    /**
     * 单条创建.
     *
     * @param array $params 接受参数
     * @param array $user  用户参数
     * @return int 返回agentId
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    private function createAgent(array $params, array $user): int
    {
        $corpId = (int) $user['corpIds'][0];

        ## 添加应用
        $data = [
            'wx_agent_id'           => $params['wxAgentId'],
            'wx_secret'             => $params['wxSecret'],
            'corp_id'               => $corpId,
            'type'                  => $params['type'],
            'created_at'            => date('Y-m-d H:i:s'),
        ];
        $agentId = $this->workAgentClient->createWorkAgent($data);
        if (! $agentId) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '应用创建失败');
        }

        return $agentId;
    }


    /**
     * 获取应用详情
     *
     *
     * @param int $agentId 应用id
     * @param int $wxAgentId 微信应用id
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    private function getWxAgentDetail(int $agentId, int $wxAgentId): array
    {
        $ecClient = $this->wxAgentApp($agentId)->agent;
        //获取客户详情
        $res = $ecClient->get($wxAgentId);
        if ($res['errcode'] == 0) {
            return $res;
        }

        throw new CommonException(ErrorCode::URI_NOT_FOUND, '找不到应用');
    }

    /**
     * 更新企业应用信息
     * @param int $agentId 应用id
     * @param int $wxAgentId 微信应用id
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function updateAgent(int $agentId, int $wxAgentId): array
    {
        ## 获取企业微信详情
        $agentDetail = $this->getWxAgentDetail($agentId, $wxAgentId);
        $data = [
            'name'                  => $agentDetail['name'],
            'square_logo_url'       => $agentDetail['square_logo_url'],
            'description'           => $agentDetail['description'],
            'close'                 => $agentDetail['close'],
            'redirect_domain'       => $agentDetail['redirect_domain'],
            'report_location_flag'  => $agentDetail['report_location_flag'],
            'is_reportenter'        => $agentDetail['isreportenter'],
            'home_url'              => $agentDetail['home_url'],
            'updated_at'            => date('Y-m-d H:i:s'),
        ];

        $res = $this->workAgentClient->updateWorkAgentById($agentId, $data);
        if (! $res) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '应用更新失败');
        }
        return [];
    }

    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @return array|void
     */
    public function updateAgents(): void
    {
        ## 获取全部应用
        $agentData = $this->workAgentClient->getWorkAgents(['id', 'wx_agent_id','corp_id']);
        ## 异步处理
        if (empty($agentData)) {
            return;
        }
        ## 同步企业应用信息
        foreach ($agentData as $key => $agentInfo) {
            ## 更新应用详情
            $this->updateAgent($agentInfo['id'], (int) $agentInfo['wxAgentId']);
        }
        unset($agentInfo);
    }

}
