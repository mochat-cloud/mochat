<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\Plugin\Radar\Action\Dashboard;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use MoChat\App\Common\Middleware\DashboardAuthMiddleware;
use MoChat\App\Rbac\Middleware\PermissionMiddleware;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use MoChat\Plugin\Radar\Contract\RadarContract;

/**
 * 互动雷达- 修改详情.
 *
 * Class Show.
 * @Controller
 */
class Info extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var RadarContract
     */
    protected $radarService;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    private $logger;

    /**
     * @RequestMapping(path="/dashboard/radar/info", methods="get")
     * @Middlewares({
     *     @Middleware(DashboardAuthMiddleware::class),
     *     @Middleware(PermissionMiddleware::class)
     * })
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $id = $this->request->input('id');
        ## 查询数据
        return $this->handleData($id);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'id' => 'required | integer | min:0 | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'id.required' => '活动ID 必填',
            'id.integer' => '活动ID 必需为整数',
            'id.min  ' => '活动ID 不可小于1',
        ];
    }

    /**
     * @param $id
     * @throws \JsonException
     */
    private function handleData($id): array
    {
        ## 互动雷达信息
        $info = $this->radarService->getRadarById((int) $id, ['id', 'type', 'title', 'link', 'link_title', 'link_description', 'link_cover', 'pdf_name', 'pdf', 'article_type', 'article', 'employee_card', 'action_notice', 'dynamic_notice', 'tag_status', 'contact_tags', 'contact_grade']);
        if (! empty($info['linkCover'])) {
            $info['linkCover'] = file_full_url($info['linkCover']);
        }
        if ($info['type'] === 3) {
            $info['article'] = json_decode($info['article'], true, 512, JSON_THROW_ON_ERROR);
            if ($info['articleType'] === 2) {
                $info['article']['cover_url'] = file_full_url($info['article']['cover_url']);
            }
        }
        if ($info['tagStatus'] === 1) {
            $info['contactTags'] = json_decode($info['contactTags'], true, 512, JSON_THROW_ON_ERROR);
        }
        return $info;
    }
}
