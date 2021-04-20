<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\QueueService\WorkContact;

use App\Logic\WeWork\AppTrait;
use App\Logic\WorkContact\SynContactLogic;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Exception\ParallelExecutionException;
use Hyperf\Utils\Parallel;

/**
 * 分组同步单一成员跟进客户信息
 * Class SynContactApplyByGroup.
 */
class SynContactApplyByGroup
{
    use AppTrait;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @var
     */
    private $ecClient;

    /**
     * @param array $employee 通讯录成员
     * @param int $corpId 企业授信ID
     * @param string $wxCorpid 企业微信ID
     * @param array $wxContactIds 客户微信ID列表
     */
    public function handle(array $employee, int $corpId, string $wxCorpid, array $wxContactIds): void
    {
        $this->ecClient = $this->wxApp($wxCorpid, 'contact')->external_contact;
        ## 从微信拉取客户详情
        $wxContactDetails = $this->getWxContactDetail($wxContactIds);
        if (empty($wxContactDetails) || empty(array_filter($wxContactDetails))) {
            return;
        }
        (new SynContactLogic())->handle($employee, $corpId, $wxContactIds, array_filter($wxContactDetails));
    }

    /**
     * @return array 响应数据
     */
    protected function coDeal(array $arr, \Closure $call)
    {
        $parallel = new Parallel();
        foreach ($arr as $item) {
            $parallel->add(function () use ($item, $call) {
                return $call($item);
            });
        }
        $results = [];
        try {
            $results = $parallel->wait();
        } catch (ParallelExecutionException $e) {
            $this->logger->error($e->getMessage());
        }

        return $results;
    }

    /**
     * 从微信拉取客户详情.
     * @return mixed
     */
    private function getWxContactDetail(array $wxContactIds): array
    {
        ## 协程拉取客户详情
        return $this->coDeal($wxContactIds, function ($wxContactId) {
            $res = $this->ecClient->get($wxContactId);
            if ($res['errcode'] == 0) {
                return [
                    'external_contact' => $res['external_contact'],
                    'follow_user'      => $res['follow_user'],
                ];
            }
            $this->logger->error(sprintf('同步客户-获取客户详情信息失败，error: %s [%s]', json_encode($res), date('Y-m-d H:i:s')));
            return [];
        });
    }
}
