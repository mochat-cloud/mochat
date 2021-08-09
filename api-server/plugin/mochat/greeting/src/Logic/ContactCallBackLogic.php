<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Greeting\Logic;

use Hyperf\Di\Annotation\Inject;
use MoChat\App\Medium\Contract\MediumContract;
use MoChat\App\WorkEmployee\Contract\WorkEmployeeContract;
use MoChat\Plugin\AutoTag\Logic\ContactCallBackLogic as AutoTagLogic;
use MoChat\Plugin\Greeting\Constants\RangeType;
use MoChat\Plugin\Greeting\Contract\GreetingContract;

/**
 * 欢迎语 - 新增客户回调.
 *
 * Class ContactCallBackLogic
 */
class ContactCallBackLogic
{
    /**
     * @Inject
     * @var WorkEmployeeContract
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var AutoTagLogic
     */
    protected $autoTagLogic;

    /**
     * @Inject
     * @var GreetingContract
     */
    private $greetingService;

    /**
     * @Inject
     * @var MediumContract
     */
    private $mediumService;

    /**
     * @param string $wxUserId 用户微信ID
     * @param string $externalUserID 客户微信ID
     * @param int $corpId 企业id
     * @return array[]
     */
    public function getGreeting(string $wxUserId, string $externalUserID, int $corpId): array
    {
        $data = [
            'tags'    => [],
            'content' => [],
        ];
        ## 查询客户跟进人信息[企业通讯录]
        $employee = $this->workEmployeeService->getWorkEmployeeByWxUserId($wxUserId, ['id', 'corp_id']);
        if (empty($employee)) {
            return $data;
        }
        ## 查询员工欢迎语
        $greetingList = $this->greetingService->getGreetingsByCorpId((int) $employee['corpId'], ['id', 'medium_id', 'words', 'range_type', 'employees']);
        if (empty($greetingList)) {
            return $data;
        }
        $commonGreeting = [];
        foreach ($greetingList as $greeting) {
            ## 检索通用欢迎语
            $greeting['rangeType'] == RangeType::ALL && $commonGreeting = [
                'text'     => $greeting['words'],
                'mediumId' => $greeting['mediumId'],
            ];
            ## 检索指定成员欢迎语
            $employees = empty($greeting['employees']) ? [] : json_decode($greeting['employees'], true);
            if (! in_array($employee['id'], $employees)) {
                continue;
            }
            $data['content'] = [
                'text'     => $greeting['words'],
                'mediumId' => $greeting['mediumId'],
            ];
        }
        if (empty($data['content']) && ! empty($commonGreeting)) {
            $data['content'] = $commonGreeting;
        }
        if (isset($data['content']['mediumId'])) {
            $data['content']['medium'] = $this->getMedium((int) $data['content']['mediumId']);
            unset($data['content']['mediumId']);
        }
        $params       = ['contactWxExternalUserid' => $externalUserID, 'wxUserId' => $wxUserId, 'corpId' => (int) $corpId];
        $autoTag      = $this->autoTagLogic->getAutoTag($params);
        $data['tags'] = $autoTag['tags'];
        return $data;
    }

    /**
     * @param int $mediumId 素材库ID
     * @return array 响应数组
     */
    private function getMedium(int $mediumId): array
    {
        $medium = $this->mediumService->getMediumById($mediumId, ['id', 'type', 'content']);
        return empty($medium) ? [] : [
            'mediumType'    => $medium['type'],
            'mediumContent' => json_decode($medium['content'], true),
        ];
    }
}
