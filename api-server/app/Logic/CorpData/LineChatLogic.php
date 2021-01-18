<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\CorpData;

use App\Contract\CorpDayDataServiceInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * 首页数据统计.
 *
 * Class LineChatLogic
 */
class LineChatLogic
{
    /**
     * 企业日数据表.
     * @Inject
     * @var CorpDayDataServiceInterface
     */
    private $dayData;

    /**
     * 企业id.
     * @var int
     */
    private $corpId;

    /**
     * @return array
     */
    public function handle()
    {
        $this->corpId = user()['corpIds'][0];

        $days = [];
        for ($i = 0; $i <= 31; ++$i) {
            $days[] = date('Y-m-d', strtotime(' -' . $i . 'day'));
        }

        //获取近31天数据
        return $this->getData($days);
    }

    /**
     * 获取31天数据.
     * @param $date
     * @return array
     */
    private function getData($date)
    {
        $columns = [
            'id',
            'add_contact_num',    //当日新增客户数
            'add_into_room_num',  //当日入群数
            'loss_contact_num',   //当日流失客户数
            'quit_room_num',      //当日退群数
            'date',
        ];
        //查询近31天数据
        $info = $this->dayData->getCorpDayDatasByCorpIdDateOther(
            (int) $this->corpId,
            $date,
            $columns,
            [['date', 'asc']],
            31
        );

        if (empty($info)) {
            return [];
        }

        return $info;
    }
}
