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

use App\Action\ContactField\Traits\RequestTrait;
use App\Contract\SensitiveWordServiceInterface;
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
 * 删除 - 动作.
 *
 * @Controller
 */
class Destroy extends AbstractAction
{
    use ValidateSceneTrait;
    //use RequestTrait;

    /**
     * @Inject
     * @var SensitiveWordServiceInterface
     */
    protected $sensitiveWordService;

    /**
     * @Middleware(PermissionMiddleware::class)
     * @RequestMapping(path="/sensitiveWord/destroy", methods="DELETE")
     */
    public function handle(): array
    {
        ## 验证参数
        $this->validated($this->request->all(), 'destroy');
        $id = (int) $this->request->input('sensitiveWordId');

        ## 删除数据
        $client = $this->container->get(SensitiveWordServiceInterface::class);
        $res    = $client->deleteSensitiveWord($id);
        if (! $res) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '删除失败');
        }

        return [];
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'sensitiveWordId' => 'required | numeric | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'sensitiveWordId.required' => '敏感词id 必填',
            'sensitiveWordId.numeric'  => '敏感词id 必须为数字类型',
        ];
    }
}
