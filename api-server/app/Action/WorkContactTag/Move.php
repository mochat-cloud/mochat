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

use App\Logic\WorkContactTag\MoveLogic;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 批量移动客户标签.
 *
 * Class Move
 * @Controller
 */
class Move extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var MoveLogic
     */
    private $moveLogic;

    /**
     * @RequestMapping(path="/workContactTag/move", methods="PUT")
     */
    public function handle()
    {
        //接收参数
        $params['tagId']   = $this->request->input('tagId');
        $params['groupId'] = $this->request->input('groupId');

        //校验参数
        $this->validated($params);

        return $this->moveLogic->handle($params);
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'tagId'   => 'required',
            'groupId' => 'required',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
            'tagId.required'   => '标签id必传',
            'groupId.required' => '分组id必传',
        ];
    }
}
