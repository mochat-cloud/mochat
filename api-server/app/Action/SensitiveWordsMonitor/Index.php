<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\SensitiveWordsMonitor;

use App\Logic\SensitiveWordsMonitor\IndexLogic;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 敏感词监控-列表.
 *
 * Class Index.
 * @Controller
 */
class Index extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var IndexLogic
     */
    protected $indexLogic;

    /**
     * @RequestMapping(path="/sensitiveWordsMonitor/index", methods="get")
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 获取当前登录用户
        $user = user();
        ## 接收参数
        $params = [
            'employeeId'         => $this->request->input('employeeId', ''),
            'workRoomId'         => $this->request->input('workRoomId', 0),
            'intelligentGroupId' => $this->request->input('intelligentGroupId', 'no'),
            'triggerStart'       => $this->request->input('triggerStart', ''),
            'triggerEnd'         => $this->request->input('triggerEnd', ''),
            'page'               => $this->request->input('page', 1),
            'perPage'            => $this->request->input('perPage', 10),
        ];
        return $this->indexLogic->handle($params, $user);
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
