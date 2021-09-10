<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkContact\Action\Sidebar\ProcessStatus;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\Common\Middleware\SidebarAuthMiddleware;
use MoChat\App\WorkContact\Contract\ContactProcessContract;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 客户详情 - 跟进状态.
 *
 * Class Index
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ContactProcessContract
     */
    private $contactProcess;

    /**
     * @Middlewares({
     *     @Middleware(SidebarAuthMiddleware::class)
     * })
     * @RequestMapping(path="/sidebar/contactProcessStatus/index", methods="GET")
     */
    public function handle()
    {
        //校验参数
        $corpId = user()['corpId'];
        $list = $this->contactProcess->getContactProcessesByCorpId($corpId, ['id', 'name']);
        if (! empty($list)) {
            return $list;
        }
        //无跟进状态，创建默认项
        $createAt = date('Y-m-d H:i:s');
        $data = [['corp_id' => $corpId, 'name' => '新客户', 'order' => 1, 'created_at' => $createAt], ['corp_id' => $corpId, 'name' => '初步沟通', 'order' => 2, 'created_at' => $createAt], ['corp_id' => $corpId, 'name' => '意向客户', 'order' => 3, 'created_at' => $createAt], ['corp_id' => $corpId, 'name' => '付款客户', 'order' => 4, 'created_at' => $createAt], ['corp_id' => $corpId, 'name' => '无意向客户', 'order' => 5, 'created_at' => $createAt]];
        $this->contactProcess->createContactProcesses($data);
        return $this->contactProcess->getContactProcessesByCorpId($corpId, ['id', 'name']);
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
        ];
    }
}
