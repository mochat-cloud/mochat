<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\ContactField\Traits;

use Overtrue\Pinyin\Pinyin;

trait UpdateTrait
{
    protected function handleUpdateParam(array $param, array $data): array
    {
        ## 数据处理
        $param['options'] = json_encode($param['options'], JSON_UNESCAPED_UNICODE);
        if ($data['isSys']) {
            $data['options'] = json_encode($data['options'], JSON_UNESCAPED_UNICODE);
            $param           = array_merge($param, $data);
        } else {
            $pinyin        = new Pinyin();
            $param['name'] = $pinyin->permalink($param['label'], '', PINYIN_ASCII_TONE);
        }

        return [
            'id'      => $param['id'],
            'name'    => $param['name'],
            'label'   => $param['label'],
            'type'    => $param['type'],
            'options' => $param['options'],
            'order'   => $param['order'],
            'status'  => $param['status'],
        ];
    }
}
