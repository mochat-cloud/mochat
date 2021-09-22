<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Listener;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use MoChat\App\Corp\Contract\CorpContract;
use MoChat\App\WorkContact\Contract\WorkContactContract;
use MoChat\App\WorkContact\Contract\WorkContactEmployeeContract;
use MoChat\App\WorkContact\Event\UpdateContactRawEvent;
use MoChat\App\WorkContact\Logic\UpdateContactCallBackLogic;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Framework\WeWork\WeWork;
use Psr\Container\ContainerInterface;

/**
 * 编辑企业客户事件.
 *
 * @Listener(priority=9999)
 */
class UpdateContactRawListener implements ListenerInterface
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var WeWork
     */
    protected $client;

    public function listen(): array
    {
        return [
            UpdateContactRawEvent::class,
        ];
    }

    /**
     * @param UpdateContactRawEvent $event
     */
    public function process(object $event)
    {
        $message = $event->message;
        //如果有员工微信id和客户微信id
        if (! isset($message['UserID'], $message['ExternalUserID'])) {
            return;
        }

        $this->client = make(WeWork::class);

        $ecClient = $this->client->provider('externalContact')->app()->external_contact;

        $contactId = 0;
        $employeeId = 0;
        $corpId = 0;

        //查询企业id
        $corpInfo = make(CorpContract::class)->getCorpsByWxCorpId($message['ToUserName'], ['id']);
        if (empty($corpInfo)) {
            return;
        }
        $corpId = (int) $corpInfo['id'];

        //查询客户id
        $contactInfo = make(WorkContactContract::class)->getWorkContactByWxExternalUserId($message['ExternalUserID'], ['id']);
        if (empty($contactInfo)) {
            return;
        }
        $contactId = (int) $contactInfo['id'];

        //查询员工id
        $employeeInfo = make(WorkEmployeeContract::class)->getWorkEmployeeByWxUserIdCorpId($message['UserID'], $corpId, ['id']);
        if (empty($employeeInfo)) {
            return;
        }
        $employeeId = (int) $employeeInfo['id'];

        if ($contactId == 0 || $employeeId == 0 || $corpId == 0) {
            return;
        }

        //查询客户员工关联关系
        $contactEmployee = make(WorkContactEmployeeContract::class)->getWorkContactEmployeeByOtherId($employeeId, $contactId, ['id']);
        //如果有关联关系 修改信息
        if (empty($contactEmployee)) {
            return;
        }

        //获取客户详情
        $res = $ecClient->get($message['ExternalUserID']);
        if ($res['errcode'] !== 0 || empty($res['follow_user'])) {
            return;
        }

        $params = [
            'wxUserId' => $message['UserID'],
            'corpId' => $corpId,
            'contactId' => $contactId,
            'employeeId' => $employeeId,
            'followUser' => $res['follow_user'],
        ];
        make(UpdateContactCallBackLogic::class)->handle($params);
    }
}
