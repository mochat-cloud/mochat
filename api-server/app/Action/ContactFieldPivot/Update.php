<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\ContactFieldPivot;

use App\Logic\ContactFieldPivot\UpdateLogic;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 客户详情 - 编辑用户画像.
 *
 * Class Update
 * @Controller
 */
class Update extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var UpdateLogic
     */
    private $updateLogic;

    /**
     * @RequestMapping(path="/contactFieldPivot/update", methods="PUT")
     * @return array
     */
    public function handle()
    {
        //接收参数
        $params['userPortrait'] = $this->request->input('userPortrait');
        $params['contactId']    = $this->request->input('contactId');

        //校验参数
        $this->validated($params);

        return $this->updateLogic->handle($params);
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'contactId'    => 'required',
            'userPortrait' => 'required',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
            'contactId.required'    => '客户id必传',
            'userPortrait.required' => '修改内容必传',
        ];
    }
}
