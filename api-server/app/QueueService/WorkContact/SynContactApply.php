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
 * 同步登录人员的客户
 * Class SynContactApply.
 */
class SynContactApply
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
     * @AsyncQueueMessage(pool="contact")
     * @param array $employee 通讯录成员
     * @param int $corpId 企业授信ID
     * @param string $wxCorpid 企业微信ID
     */
    public function handle(array $employee, int $corpId, string $wxCorpid): void
    {
        // 记录日志
        $this->logger->debug(sprintf('%s [%s]', '同步客户', date('Y-m-d H:i:s')));
        $this->ecClient = $this->wxApp($wxCorpid, 'contact')->external_contact;
        //获取客户列表
        $listRes = array_filter($this->getList($employee));
        if (empty($listRes)) {
            return;
        }
        // 转为一维数组
        $externalUserid = [];
        array_walk_recursive($listRes, function ($value) use (&$externalUserid) {
            array_push($externalUserid, $value);
        });
        // 获取客户详情
        $detailRes = array_filter($this->getDetail($externalUserid));

        $detail = [];
        foreach ($detailRes as $val) {
            foreach ($val as $v) {
                $detail[] = $v;
            }
        }

        if (empty($detail)) {
            return;
        }
        $params = [
            'employee' => $employee,
            'corpId'   => $corpId,
            'detail'   => $detail,
        ];
        //同步客户
        make(SynContactLogic::class)->handle($params);
    }

    /**
     * 协程请求
     * @return array
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
     * 获取客户列表.
     * @param $employee
     * @return array
     */
    private function getList($employee)
    {
        $newEmployee = array_chunk($employee, 3);

        return $this->coDeal($newEmployee, function ($data) {
            $arr = array_map(function ($v) {
                //获取客户列表
                $contact = $this->ecClient->list($v['wxUserId']);
                if ($contact['errcode'] == 0) {
                    return $contact['external_userid'];
                }
                $contact['errcode'] == 84061 || $this->logger->error(sprintf('同步客户-获取通讯录用户跟进的客户列表信息失败，error: %s [%s]', json_encode($contact), date('Y-m-d H:i:s')));
                return [];
            }, $data);
            return array_filter($arr);
        });
    }

    /**
     * 获取客户详情.
     * @param $externalUserid
     * @return array
     */
    private function getDetail($externalUserid)
    {
        $externalUserid = array_chunk(array_unique($externalUserid), 3);

        return $this->coDeal($externalUserid, function ($data) {
            $arr = array_map(function ($v) {
                $res = $this->ecClient->get($v);
                if ($res['errcode'] == 0) {
                    return [
                        'external_contact' => $res['external_contact'],
                        'follow_user'      => $res['follow_user'],
                    ];
                }
                $this->logger->error(sprintf('同步客户-获取客户详情信息失败，error: %s [%s]', json_encode($res), date('Y-m-d H:i:s')));
                return [];
            }, $data);
            return array_filter($arr);
        });
    }
}
