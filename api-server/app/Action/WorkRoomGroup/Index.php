<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkRoomGroup;

use App\Contract\WorkRoomGroupServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 客户群分组管理-列表.
 *
 * Class Index.
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var WorkRoomGroupServiceInterface
     */
    protected $workRoomGroupService;

    /**
     * @RequestMapping(path="/workRoomGroup/index", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 接收参数
        $page    = $this->request->input('page', 1);
        $perPage = $this->request->input('perPage', '10');
        ## 当前登陆者
        $user = user();

        ## 组织查询条件
        $where            = [];
        $corpIds          = isset($user['corpIds']) ? $user['corpIds'] : [];
        $where['corp_id'] = ['corp_id', 'IN', $corpIds];
        $options          = [
            'page'    => $page,
            'perPage' => $perPage,
        ];
        ## 查询字段
        $columns = [
            'id',
            'name',
            'corp_id',
            'created_at',
        ];

        $res = $this->workRoomGroupService->getWorkRoomGroupList($where, $columns, $options);

        ## 组织响应数据
        $data = [
            'page' => [
                'perPage'   => $perPage,
                'total'     => 0,
                'totalPage' => 0,
            ],
            'list' => [],
        ];

        if (empty($res['data'])) {
            return $data;
        }
        ## 处理分页数据
        $data['page']['total']     = $res['total'];
        $data['page']['totalPage'] = $res['last_page'];

        ## 处理数据
        foreach ($res['data'] as &$v) {
            $v['workRoomGroupId']   = $v['id'];
            $v['workRoomGroupName'] = $v['name'];
            unset($v['id'], $v['name']);
        }
        $data['list'] = $res['data'];

        return $data;
    }

    /**
     * 验证规则.
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [];
    }
}
