<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\ContactField;

use App\Constants\ContactField\Options;
use App\Constants\ContactField\Status;
use App\Contract\ContactFieldServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;

/**
 * 客户列表筛选 -- 用户画像.
 *
 * Class Portrait
 * @Controller
 */
class Portrait extends AbstractAction
{
    /**
     * @Inject
     * @var ContactFieldServiceInterface
     */
    private $contactFieldService;

    /**
     * @RequestMapping(path="/contactField/portrait", methods="get")
     */
    public function index()
    {
        $res = $this->contactFieldService->getContactFieldsByStatusOrderByOrder((int) Status::EXHIBITION, ['id', 'label', 'type', 'options']);

        if (empty($res)) {
            return [];
        }

        $resData[] = [
            'fieldId' => 0,
            'name'    => '全部',
        ];

        $data = [];
        foreach ($res as &$val) {
            //类型为图片的不在下拉框展示
            if ($val['type'] != Options::PICTURE) {
                $resData[] = [
                    'fieldId'  => $val['id'],
                    'name'     => $val['label'],
                    'type'     => $val['type'],
                    'typeText' => Options::getMessage($val['type']),
                    'options'  => $val['options'],
                ];
            }
        }
        unset($val);

        $resData[] = $data;

        return $resData;
    }
}
