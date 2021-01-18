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
use App\Contract\WorkContactEmployeeServiceInterface;
use App\Contract\WorkContactServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use App\Logic\WorkContact\UpdateContactCallBackLogic;
use MoChat\Framework\WeWork\WeWork;

/**
 * 编辑企业客户回调.
 * Class UpdateContactCallBackApply.
 */
class UpdateContactCallBackApply
{
    /**
     * @var WeWork
     */
    protected $client;

    /**
     * @param $wxResponse
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function handle(array $wxResponse): void
    {
        //如果有员工微信id和客户微信id
        if (isset($wxResponse['UserID'], $wxResponse['ExternalUserID'])) {
            $this->client = make(WeWork::class);

            $ecClient = $this->client->provider('externalContact')->app()->external_contact;

            $contactId  = 0;
            $employeeId = 0;
            $corpId     = 0;
            //查询客户id
            $contactInfo = make(WorkContactServiceInterface::class)->getWorkContactByWxExternalUserId($wxResponse['ExternalUserID'], ['id']);
            if (! empty($contactInfo)) {
                $contactId = $contactInfo['id'];
            }

            //查询员工id
            $employeeInfo = make(WorkEmployeeServiceInterface::class)->getWorkEmployeeByWxUserId($wxResponse['UserID'], ['id']);
            if (! empty($employeeInfo)) {
                $employeeId = $employeeInfo['id'];
            }

            //查询企业id
            $corpInfo = make(CorpServiceInterface::class)->getCorpsByWxCorpId($wxResponse['ToUserName'], ['id']);
            if (! empty($corpInfo)) {
                $corpId = $corpInfo['id'];
            }
            if ($contactId != 0 && $employeeId != 0 && $corpId != 0) {
                //查询客户员工关联关系
                $contactEmployee = make(WorkContactEmployeeServiceInterface::class)->getWorkContactEmployeeByOtherId($employeeId, $contactId, ['id']);
                //如果有关联关系 修改信息
                if (! empty($contactEmployee)) {
                    //获取客户详情
                    $res = $ecClient->get($wxResponse['ExternalUserID']);
                    if ($res['errcode'] == 0 && ! empty($res['follow_user'])) {
                        $params = [
                            'wxUserId'   => $wxResponse['UserID'],
                            'corpId'     => $corpId,
                            'contactId'  => $contactId,
                            'employeeId' => $employeeId,
                            'followUser' => $res['follow_user'],
                        ];
                        make(UpdateContactCallBackLogic::class)->handle($params);
                    }
                }
            }
        }
    }
}
