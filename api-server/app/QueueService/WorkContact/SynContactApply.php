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

use App\Contract\CorpServiceInterface;
use App\Logic\WeWork\AppTrait;
use App\Logic\WorkContact\SynContactLogic;
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;
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
     * 企业信息.
     * @var array
     */
    private $corp;

    /**
     * @AsyncQueueMessage(pool="contact")
     * @param $employee
     * @param $corpId
     */
    public function handle($employee, $corpId): void
    {
        //记录日志
        $this->logger->debug(sprintf('%s [%s]', '同步客户', date('Y-m-d H:i:s')));

        ## 获取企业微信信息
        $this->corp = make(CorpServiceInterface::class)->getCorpById($corpId, ['id', 'wx_corpid']);

        //获取客户列表
        $listRes = $this->getList($employee);
        $this->logger->debug(sprintf('%s [%s]', '同步客户列表', date('Y-m-d H:i:s')), ['list' => $listRes, 'employee' => $employee, 'corp' => $this->corp]);

        if (empty($listRes)) {
            return;
        }

        //转为一维数组
        $externalUserid = [];
        array_walk_recursive($listRes, function ($value) use (&$externalUserid) {
            array_push($externalUserid, $value);
        });

        //获取客户详情
        $detailRes = $this->getDetail($externalUserid);

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

        try {
            $results = $parallel->wait();
        } catch (ParallelExecutionException $e) {
            //dump($e);

            $this->logger->error($e->getMessage());
            // $e->getResults() 获取协程中的返回值。
            // $e->getThrowables() 获取协程中出现的异常。
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
            $ecClient = $this->wxApp($this->corp['wxCorpid'], 'contact')->external_contact;

            $list = [];
            foreach ($data as &$value) {
                //获取客户列表
                $contact = $ecClient->list($value['wxUserId']);
                if ($contact['errcode'] == 0) {
                    $list[] = $contact['external_userid'];
                }
            }
            return $list;
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
            $detail = [];
            foreach ($data as $value) {
                $ecClient = $this->wxApp($this->corp['wxCorpid'], 'contact')->external_contact;
                //获取客户详情
                $res = $ecClient->get($value);
                if ($res['errcode'] == 0) {
                    if (! empty($res['external_contact'])) {
                        $detail[] = [
                            'external_contact' => $res['external_contact'],
                            'follow_user'      => $res['follow_user'],
                        ];
                    }
                }
            }
            return $detail;
        });
    }
}
