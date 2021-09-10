<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace MoChat\App\WorkAgent\Action\Sidebar;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\App\WorkAgent\Logic\AuthLogic;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

/**
 * 企业微信侧边栏授权跳转.
 * @Controller
 */
class Auth extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var AuthLogic
     */
    private $authLogic;

    /**
     * @RequestMapping(path="/sidebar/agent/auth", methods="get,post")
     */
    public function handle(): Psr7ResponseInterface
    {
        $this->validated($this->request->all());
        return $this->execute();
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'target' => 'required',
            'agentId' => 'required',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'target.required' => '跳转地址不能为空',
            'agentId.required' => '应用Id不能为空',
        ];
    }

    private function execute(): Psr7ResponseInterface
    {
        $target = (string) $this->request->input('target');
        $code = (string) $this->request->input('code');
        $agentId = (int) $this->request->input('agentId');

        $redirectUri = $this->authLogic->handle($code, $agentId, $target);
        return $this->response->redirect($redirectUri);
    }
}
