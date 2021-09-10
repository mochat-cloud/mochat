<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\AutoTag\Action\Dashboard;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\App\User\Contract\UserContract;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\AutoTag\Contract\AutoTagContract;
use Psr\Container\ContainerInterface;

/**
 * 自动打标签-列表.
 *
 * Class Index.
 * @Controller
 */
class Index
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var AutoTagContract
     */
    protected $autoTagService;

    /**
     * @Inject
     * @var UserContract
     */
    protected $userService;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(RequestInterface $request, ContainerInterface $container)
    {
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @RequestMapping(path="/dashboard/autoTag/index", methods="get")
     * @throws \JsonException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 获取当前登录用户
        $user = user();
        ## 验证参数
        $this->validated($this->request->all());
        ## 接收参数
        $params = [
            'type' => $this->request->input('type'),
            'name' => $this->request->input('name'),
            'tags' => $this->request->input('tags'),
            'page' => $this->request->input('page', 1),
            'perPage' => $this->request->input('perPage', 10000),
        ];
        $params = $this->handleParams($user, $params);
        return $this->getAutoTagList($params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'type' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'type.required' => '打标签方式 必传',
        ];
    }

    /**
     * 处理参数.
     * @param array $user 用户信息
     * @param array $params 接受参数
     * @return array 响应数组
     */
    private function handleParams(array $user, array $params): array
    {
        $where['corp_id'] = $user['corpIds'][0];
        if ($user['isSuperAdmin'] === 0) {
            $where['create_user_id'] = $user['id'];
        }
        $where['type'] = $params['type'];
        if (isset($params['name']) && ! empty($params['name'])) {
            $where[] = ['name', 'LIKE', '%' . $params['name'] . '%'];
        }
        if (isset($params['tags']) && ! empty($params['tags'])) {
            $where[] = ['tags', 'LIKE', '%' . implode(',', $params['tags']) . '%'];
        }
        $options = [
            'perPage' => $params['perPage'],
            'page' => $params['page'],
            'orderByRaw' => 'id desc',
        ];

        return ['where' => $where, 'options' => $options];
    }

    /**
     * 获取自动打标签列表.
     * @param array $params 参数
     * @throws \JsonException
     * @return array 响应数组
     */
    private function getAutoTagList(array $params): array
    {
        $columns = ['id', 'name', 'mark_tag_count', 'fuzzy_match_keyword', 'exact_match_keyword', 'tag_rule', 'on_off', 'create_user_id', 'created_at'];
        $autoTagList = $this->autoTagService->getAutoTagList($params['where'], $columns, $params['options']);
        $list = [];
        $data = [
            'page' => [
                'perPage' => $this->perPage,
                'total' => '0',
                'totalPage' => '0',
            ],
            'list' => $list,
        ];

        return empty($autoTagList['data']) ? $data : $this->handleData($autoTagList);
    }

    /**
     * 数据处理.
     * @param array $autoTagList 列表数据
     * @throws \JsonException
     * @return array 响应数组
     */
    private function handleData(array $autoTagList): array
    {
        $list = [];
        foreach ($autoTagList['data'] as $key => $val) {
            //处理创建者信息
            $username = $this->userService->getUserById($val['createUserId']);
            $tagRule = json_decode($val['tagRule'], true, 512, JSON_THROW_ON_ERROR);
            $tags = array_column($tagRule[0]['tags'], 'tagname');
            $list[$key] = [
                'id' => $val['id'],
                'name' => $val['name'],
                'mark_tag_count' => $val['markTagCount'],
                'fuzzy_match_keyword' => explode(',', $val['fuzzyMatchKeyword']),
                'exact_match_keyword' => explode(',', $val['exactMatchKeyword']),
                'tags' => $tags,
                'on_off' => $val['onOff'],
                'nickname' => isset($username['name']) ? $username['name'] : '',
                'created_at' => $val['createdAt'],
            ];
        }
        $data['page']['total'] = $autoTagList['total'];
        $data['page']['totalPage'] = $autoTagList['last_page'];
        $data['list'] = $list;
        return $data;
    }
}
