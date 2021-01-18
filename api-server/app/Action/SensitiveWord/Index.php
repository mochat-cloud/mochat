<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\SensitiveWord;

use App\Constants\BusinessLog\Event;
use App\Constants\SensitiveWordsMonitor\Source;
use App\Contract\CorpServiceInterface;
use App\Contract\SensitiveWordMonitorServiceInterface;
use App\Contract\SensitiveWordServiceInterface;
use App\Logic\Common\Traits\BusinessLogTrait;
use App\Logic\User\Traits\UserTrait;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 敏感词词库-列表.
 *
 * Class Index.
 * @Controller
 */
class Index extends AbstractAction
{
    //use ValidateSceneTrait;
    //use UserTrait;
    use BusinessLogTrait;

    /**
     * @Inject
     * @var CorpServiceInterface
     */
    protected $corpService;

    /**
     * @Inject
     * @var SensitiveWordServiceInterface
     */
    protected $sensitiveWordService;

    /**
     * @Inject
     * @var SensitiveWordMonitorServiceInterface
     */
    protected $sensitiveWordMonitorService;

    /**
     * 每页条数.
     */
    protected $perPage = 10;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/sensitiveWord/index", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        $user = user();

        ## 判断用户绑定企业信息
        if (! isset($user['corpIds']) || count($user['corpIds']) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '未选择登录企业，不可操作');
        }

        ## 搜索参数
        $params['keyWords'] = $this->request->input('keyWords', null);
        $params['groupId']  = $this->request->input('groupId', null);
        $params['page']     = $this->request->input('page', 1);
        $params['perPage']  = $this->request->input('perPage', $this->perPage);
        $where              = $this->getWhere($user, $params);

        ## 请求
        $columns           = ['id', 'name', 'group_id', 'employee_num', 'contact_num', 'created_at', 'status'];
        $sensitiveWordList = $this->sensitiveWordService->getSensitiveWordList($where['where'], $columns, $where['options']);

        ## 格式化数据
        return $this->handleData($sensitiveWordList);
    }

    /**
     * 搜索条件.
     * @param array $user 用户信息
     * @param array $params 接受参数
     */
    protected function getWhere(array $user, array $params): array
    {
        $where = [];

        if (empty($user['dataPermission'])) {
            ## 企业id
            $where['corp_id'] = (int) $user['corpIds'][0];
        } else {
            $ids     = $this->getBusinessIds($user['deptEmployeeIds'], [Event::SENSITIVE_WORD_CREATE]);
            $where[] = ['id', 'IN', $ids];
        }

        ## 敏感词名称
        if (! empty($params['keyWords'])) {
            $where[] = ['name', 'LIKE', '%' . $params['keyWords'] . '%'];
        }
        ## 敏感词分组id
        if (! empty($params['groupId'])) {
            $where['group_id'] = $params['groupId'];
        }
        ## 分页参数
        $options = [
            'perPage'    => $params['perPage'],
            'page'       => $params['page'],
            'orderByRaw' => 'id desc',
        ];

        return ['where' => $where, 'options' => $options];
    }

    /**
     * 数据处理.
     * @param array $listRes 查询数据
     */
    protected function handleData(array $listRes): array
    {
        $data = [
            'page' => [
                'perPage'   => $this->perPage,
                'total'     => '0',
                'totalPage' => '0',
            ],
            'list' => [],
        ];
        if (empty($listRes['data'])) {
            return $data;
        }

        ## 获取触发次数
        $sensitiveIds = array_column($listRes['data'], 'id');
        $employeeNum  = $this->getSensitiveNum($sensitiveIds, Source::EMPLOYEE);
        $contactNum   = $this->getSensitiveNum($sensitiveIds);
        ## 处理敏感词数据
        array_walk($listRes['data'], function (&$v) use ($employeeNum, $contactNum) {
            $v['sensitiveWordId'] = $v['id'];
            $v['employeeNum'] = isset($employeeNum[$v['sensitiveWordId']]) ? $employeeNum[$v['sensitiveWordId']] : 0;
            $v['contactNum'] = isset($contactNum[$v['sensitiveWordId']]) ? $contactNum[$v['sensitiveWordId']] : 0;
            unset($v['id']);
        });

        ## 分页数据
        $data['page']['total']     = $listRes['total'];
        $data['page']['totalPage'] = $listRes['last_page'];
        $data['list']              = $listRes['data'];
        return $data;
    }

    /**
     * 敏感词词库统计
     * @param array $sensitiveIds 敏感词词库id
     * @param int $source 默认1-客户 2-员工
     */
    private function getSensitiveNum(array $sensitiveIds, int $source = Source::CONTACT): array
    {
        $res = $this->sensitiveWordMonitorService->countBySensitiveWordIdSource($sensitiveIds, $source);
        if (empty($res)) {
            return [];
        }
        return array_column($res, 'total', 'sensitiveWordId');
    }
}
