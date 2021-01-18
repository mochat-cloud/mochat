<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\ChannelCodeGroup;

use App\Contract\ChannelCodeGroupServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;

/**
 * 渠道码分组列表.
 *
 * Class Index
 * @Controller
 */
class Index extends AbstractAction
{
    /**
     * @Inject
     * @var ChannelCodeGroupServiceInterface
     */
    private $channelCodeGroup;

    /**
     * @RequestMapping(path="/channelCodeGroup/index", methods="GET")
     */
    public function handle()
    {
        $res = $this->channelCodeGroup
            ->getChannelCodeGroupsByCorpId(user()['corpIds'], ['id', 'name']);

        if (empty($res)) {
            return [];
        }

        array_walk($res, function (&$item) {
            $item['groupId'] = $item['id'];

            unset($item['id']);
        });

        $data = [
            'name'    => '未分组',
            'groupId' => 0,
        ];

        $res[] = $data;

        return $res;
    }
}
