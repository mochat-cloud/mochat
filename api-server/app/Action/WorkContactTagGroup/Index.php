<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkContactTagGroup;

use App\Contract\WorkContactTagGroupServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;

/**
 * 客户标签分组列表.
 *
 * Class Index
 * @Controller
 */
class Index extends AbstractAction
{
    /**
     * @Inject
     * @var WorkContactTagGroupServiceInterface
     */
    private $contactTagGroupService;

    /**
     * @RequestMapping(path="/workContactTagGroup/index", methods="GET")
     */
    public function handle()
    {
        $res = $this->contactTagGroupService
            ->getWorkContactTagGroupsByCorpId(user()['corpIds'], ['id', 'group_name']);

        if (empty($res)) {
            return [];
        }

        array_walk($res, function (&$item) {
            $item['groupId'] = $item['id'];

            unset($item['id']);
        });

        $data = [
            'groupId'   => 0,
            'groupName' => '未分组',
        ];

        $res[] = $data;

        return $res;
    }
}
