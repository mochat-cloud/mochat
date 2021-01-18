<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkMessageConfig;

use App\Contract\CorpServiceInterface;
use App\Contract\WorkMessageConfigServiceInterface;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 聊天记录申请-列表.
 *
 * Class CorpIndex.
 * @Controller
 */
class CorpIndex extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @Inject
     * @var WorkMessageConfigServiceInterface
     */
    protected $workMessageConfigService;

    /**
     * @RequestMapping(path="/workMessageConfig/corpIndex", methods="get")
     * @Middleware(PermissionMiddleware::class)
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 获取当前登录用户
        $user = user();
        ## 接收参数
        $corpName = $this->request->input('corpName', '');
        $page     = $this->request->input('page', 1);
        $perPage  = $this->request->input('perPage', 10);

        ## 组织查询条件
        $where   = [];
        $options = [
            'page'       => $page,
            'perPage'    => $perPage,
            'orderByRaw' => 'id desc',
        ];
        empty($corpName) || $where[] = ['name', 'LIKE', '%' . $corpName . '%'];
        ## 限定查询企业范围-只可检索当前登录用户归属的企业
        $where[] = ['id', 'IN', $user['corpIds']];
        ## 查询字段
        $columns = [
            'id',
            'name',
            'wx_corpid',
            'created_at',
        ];

        $res = $this->corpService->getCorpList($where, $columns, $options);

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
        ## 获取企业聊天记录申请
        $messageConfigList                              = $this->workMessageConfigService->getWorkMessageConfigsByCorpId(array_column($res['data'], 'id'), ['corp_id', 'chat_status', 'chat_apply_status', 'created_at']);
        empty($messageConfigList) || $messageConfigList = array_column($messageConfigList, null, 'corpId');
        ## 处理分页数据
        $data['page']['total']     = $res['total'];
        $data['page']['totalPage'] = $res['last_page'];

        ## 处理数据
        foreach ($res['data'] as &$corp) {
            $corp['wxCorpId']         = $corp['wxCorpid'];
            $corp['corpId']           = $corp['id'];
            $corp['corpName']         = $corp['name'];
            $corp['chatStatus']       = isset($messageConfigList[$corp['id']]) ? $messageConfigList[$corp['id']]['chatStatus'] : 0;
            $corp['chatApplyStatus']  = isset($messageConfigList[$corp['id']]) ? $messageConfigList[$corp['id']]['chatApplyStatus'] : 0;
            $corp['messageCreatedAt'] = isset($messageConfigList[$corp['id']]) ? $messageConfigList[$corp['id']]['createdAt'] : '';
            unset($corp['id'], $corp['name']);
        }
        $data['list'] = $res['data'];

        return $data;
    }

    /**
     * 验证规则.
     *
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
