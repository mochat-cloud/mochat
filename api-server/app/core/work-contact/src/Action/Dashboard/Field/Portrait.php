<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Action\Dashboard\Field;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\WorkContact\Constants\Field\Options;
use MoChat\App\WorkContact\Constants\Field\Status;
use MoChat\App\WorkContact\Contract\ContactFieldContract;
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
     * @var ContactFieldContract
     */
    private $contactFieldService;

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/contactField/portrait", methods="get")
     */
    public function index()
    {
        $res = $this->contactFieldService->getContactFieldsByStatusOrderByOrder((int) Status::EXHIBITION, ['id', 'label', 'type', 'options']);

        if (empty($res)) {
            return [];
        }

        $resData[] = [
            'fieldId' => 0,
            'name' => '全部',
        ];

        $data = [];
        foreach ($res as &$val) {
            //类型为图片的不在下拉框展示
            if ($val['type'] != Options::PICTURE) {
                $resData[] = [
                    'fieldId' => $val['id'],
                    'name' => $val['label'],
                    'type' => $val['type'],
                    'typeText' => Options::getMessage($val['type']),
                    'options' => $val['options'],
                ];
            }
        }
        unset($val);

        $resData[] = $data;

        return $resData;
    }
}
