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

use App\Constants\WorkUpdateTime\Type;
use App\Contract\WorkContactTagPivotServiceInterface;
use App\Contract\WorkContactTagServiceInterface;
use App\Contract\WorkUpdateTimeServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;

/**
 * 客户标签列表（带分页）.
 *
 * Class Index
 * @Controller
 */
class Index extends AbstractAction
{
    /**
     * @Inject
     * @var WorkContactTagServiceInterface
     */
    private $contactTagService;

    /**
     * @Inject
     * @var WorkContactTagPivotServiceInterface
     */
    private $contactTagPivotService;

    /**
     * @Inject
     * @var WorkUpdateTimeServiceInterface
     */
    private $workUpdateTime;

    /**
     * @RequestMapping(path="/workContactTag/index", methods="GET")
     */
    public function handle(): array
    {
        //接收参数
        $params['page']    = $this->request->input('page');
        $params['perPage'] = $this->request->input('perPage');
        $params['groupId'] = $this->request->input('groupId');

        //查询最后一次同步标签时间
        $syncTagTime = $this->getTime();

        //查询条件
        $where['corp_id'] = user()['corpIds'];
        if (isset($params['groupId'])) {
            $where['contact_tag_group_id'] = $params['groupId'];
        }

        $columns = [
            'id',
            'name',
        ];
        $options = [
            'orderByRaw' => 'updated_at desc',
            'perPage'    => empty($params['perPage']) ? 20 : $params['perPage'],
        ];
        //获取标签列表
        $tagInfo = $this->contactTagService->getWorkContactTagList($where, $columns, $options);

        if (empty($tagInfo['data'])) {
            return [
                'page' => [
                    'perPage'   => 20,
                    'total'     => 0,
                    'totalPage' => 0,
                ],
                'list'        => [],
                'syncTagTime' => $syncTagTime,
            ];
        }

        //标签id
        $tagIds = array_column($tagInfo['data'], 'id');

        //根据标签id查询客户数
        $contactTag = $this->contactTagPivotService->countWorkContactTagPivotsByTagIds($tagIds);

        if (! empty($contactTag)) {
            $contactTag = array_column($contactTag, null, 'contact_tag_id');
        }

        //组装数据
        foreach ($tagInfo['data'] as &$raw) {
            $raw['contactNum'] = 0;
            if (isset($contactTag[$raw['id']])) {
                $raw['contactNum'] = $contactTag[$raw['id']]->total;
            }
        }
        unset($raw);

        return [
            'page' => [
                'perPage'   => isset($tagInfo['per_page']) ? $tagInfo['per_page'] : 20,
                'total'     => isset($tagInfo['total']) ? $tagInfo['total'] : 0,
                'totalPage' => isset($tagInfo['last_page']) ? $tagInfo['last_page'] : 0,
            ],
            'list'        => $tagInfo['data'],
            'syncTagTime' => $syncTagTime,
        ];
    }

    /**
     * 获取最后一次同步标签的时间.
     * @return mixed
     */
    private function getTime()
    {
        $type = Type::TAG;

        $res = $this->workUpdateTime->getWorkUpdateTimeByCorpIdType(user()['corpIds'], (int) $type, ['last_update_time']);

        return empty($res) ? '' : end($res)['lastUpdateTime'];
    }
}
