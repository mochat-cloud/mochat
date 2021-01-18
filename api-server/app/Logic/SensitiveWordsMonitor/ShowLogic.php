<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\SensitiveWordsMonitor;

use App\Contract\SensitiveWordMonitorServiceInterface;
use App\Logic\WorkMessage\Traits\ContentTrait;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 敏感词监控-触发敏感词对话详情.
 *
 * Class ShowLogic
 */
class ShowLogic
{
    use ContentTrait;

    /**
     * @Inject
     * @var SensitiveWordMonitorServiceInterface
     */
    protected $monitorService;

    /**
     * @param int $monitorId 敏感词监控ID
     * @return array 响应数组
     */
    public function handle(int $monitorId): array
    {
//        $arr = [
//            [
//                'corpId' => 1,
//                'msgIds' => [
//                    '3361150443611976753_1607583202_external',
//                    '1666088971280008749_1607583202_external',
//                    '7698787533383897010_1607583206_external',
//                    '1461160175743982401_1607583955_external',
//                    '3728653401436638946_1607583956_external',
//                    '17787142178067180257_1607584134_external',
//                    '1623835855870545840_1607591226',
//                    '4416328276124475149_1607591459',
//                    '14695731017865939738_1607591816_external',
//                    '2764963073206783402_1607593640',
//                    '13004081528340725449_1607598763',
//                    '13672116068630786934_1607598816',
//                    '15882855847857549808_1607598846',
//                    '18398175902099569657_1607599103',
//                    '5687630234157907905_1607599160_external',
//                    '17802083817489869644_1607599178_external',
//                    '11904124251854993975_1607599222_external',
//                    '7191108627637225168_1607599222_external',
//                    '15270548617234669490_1607599267_external',
//                    '6336290811567768894_1607599387',
//                ],
//            ],
//
//        ];
//        $beforeList = make(MonitorMessage::class)->handle($arr);
        ## 获取敏感词监控信息
        $monitor = $this->monitorService->getSensitiveWordMonitorById($monitorId);
        if (empty($monitor)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '当前敏感词监控信息不存在');
        }
        $chatContent = json_decode($monitor['chatContent'], true);

        return empty($chatContent) ? [] : array_map(function ($item) {
            $item['msgContent'] = $this->contentFormat($item['msgContent'], (int) $item['msgType']);
            return $item;
        }, $chatContent);
    }
}
