<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\Greeting;

use App\Constants\Greeting\RangeType;
use App\Contract\GreetingServiceInterface;
use App\Contract\MediumServiceInterface;
use App\Contract\WorkEmployeeServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 欢迎语 - 新增客户回调.
 *
 * Class ContactCallBackLogic
 */
class ContactCallBackLogic
{
    /**
     * @Inject
     * @var WorkEmployeeServiceInterface
     */
    protected $workEmployeeService;

    /**
     * @Inject
     * @var GreetingServiceInterface
     */
    private $greetingService;

    /**
     * @Inject
     * @var MediumServiceInterface
     */
    private $mediumService;

    /**
     * @param string $wxUserId 用户微信ID
     * @return array 响应数组
     */
    public function getGreeting(string $wxUserId): array
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
