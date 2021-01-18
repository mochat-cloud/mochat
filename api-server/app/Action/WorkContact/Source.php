<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkContact;

use App\Constants\WorkContact\AddWay;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * 客户来源.
 *
 * Class Source
 * @Controller
 */
class Source
{
    /**
     * @RequestMapping(path="/workContact/source", methods="get")
     */
    public function handle()
    {
        $res = AddWay::$Enum;

        $data = [];
        foreach ($res as $key => $val) {
            $data[] = [
                'addWay'     => $key,
                'addWayText' => $val,
            ];
        }

        return $data;
    }
}
