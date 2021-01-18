<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\ContactFieldPivot;

use App\Constants\ContactField\Options;
use App\Constants\WorkContact\Event;
use App\Contract\ContactEmployeeTrackServiceInterface;
use App\Contract\ContactFieldPivotServiceInterface;
use Hyperf\Di\Annotation\Inject;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

/**
 * 修改用户画像.
 *
 * Class UpdateLogic
 */
class UpdateLogic
{
    /**
     * 用户画像表.
     * @Inject
     * @var ContactFieldPivotServiceInterface
     */
    private $contactFieldPivot;

    /**
     * 互动轨迹表.
     * @Inject
     * @var ContactEmployeeTrackServiceInterface
     */
    private $track;

    /**
     * 参数.
     * @var array
     */
    private $params;

    /**
     * @return array
     */
    public function handle(array $params)
    {
        $this->params = $params;

        //修改用户画像 并记录轨迹
        $this->update();

        return [];
    }

    /**
     * 修改用户画像 并记录轨迹.
     */
    private function update()
    {
        $userPortrait = json_decode($this->params['userPortrait'], true);
        if (empty($userPortrait)) {
            return;
        }

        $content    = '编辑用户画像：';
        $createData = [];
        //处理数据
        foreach ($userPortrait as $key => &$raw) {
            $value = $raw['value'];
            //多选
            if ($raw['type'] == Options::CHECKBOX) {
                //数组转字符串
                if (! empty($raw['value'])) {
                    $value = implode(',', $raw['value']);
                } else {
                    $value = '';
                }
            }

            //如果没有用户画像 则新增
            if (empty($raw['contactFieldPivotId'])) {
                //新增用户画像
                $createData[] = [
                    'contact_id'       => $this->params['contactId'],
                    'contact_field_id' => $raw['contactFieldId'],
                    'value'            => $value,
                ];
                //记录轨迹
                if (! empty($value)) {
                    //轨迹数据
                    $content .= $raw['name'] . ' ';
                }
            } else {
                //查询表中用户画像值
                $res = $this->contactFieldPivot->getContactFieldPivotById((int) $raw['contactFieldPivotId'], ['value']);
                //记录轨迹
                if (! empty($res) && $res['value'] != $value) {
                    //轨迹数据
                    $content .= $raw['name'] . ' ';
                }
                $updateRes = $this->contactFieldPivot
                    ->updateContactFieldPivotById((int) $raw['contactFieldPivotId'], ['value' => $value]);
                if (! is_int($updateRes)) {
                    throw new CommonException(ErrorCode::SERVER_ERROR, '编辑用户画像失败');
                }
            }
        }

        if ($content != '编辑用户画像：') {
            //记录轨迹
            $this->recordTrack($content);
        }

        if (! empty($createData)) {
            $createRes = $this->contactFieldPivot->createContactFieldPivots($createData);
            if ($createRes != true) {
                throw new CommonException(ErrorCode::SERVER_ERROR, '添加用户画像失败');
            }
        }
    }

    /**
     * 记录轨迹.
     * @param $content
     */
    private function recordTrack($content)
    {
        $data = [
            'employee_id' => user()['workEmployeeId'],
            'contact_id'  => $this->params['contactId'],
            'content'     => $content,
            'corp_id'     => user()['corpIds'][0],
            'event'       => Event::USER_PORTRAIT,
        ];

        $res = $this->track->createContactEmployeeTrack($data);
        if (! is_int($res)) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '记录轨迹失败');
        }
    }
}
