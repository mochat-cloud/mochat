<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkContactTag;

use App\Contract\WorkContactTagServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;

/**
 * 所有标签.
 *
 * Class AllTag
 * @Controller
 */
class AllTag extends AbstractAction
{
    /**
     * @Inject
     * @var WorkContactTagServiceInterface
     */
    private $contactTagService;

    /**
     * @RequestMapping(path="/workContactTag/allTag", methods="GET")
     */
    public function handle(): array
    {
        $params['groupId'] = $this->request->input('groupId');

        $columns = [
            'id',
            'name',
        ];

        //获取所选分组下的标签列表
        if (is_numeric($params['groupId'])) {
            $tagInfo = $this->contactTagService
                ->getWorkContactTagsByCorpIdGroupIds(user()['corpIds'], [$params['groupId']], $columns);

            if (empty($tagInfo)) {
                return [];
            }
        } else {
            //获取所有标签
            $tagInfo = $this->contactTagService
                ->getWorkContactTagsByCorpId(user()['corpIds'], $columns);
            if (empty($tagInfo)) {
                return [];
            }
        }

        return $tagInfo;
    }
}
