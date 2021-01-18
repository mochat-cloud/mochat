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

use App\Logic\WorkContactTag\UpdateLogic;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 修改客户标签.
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
    protected $updateLogic;

    /**
     * @RequestMapping(path="/workContactTag/update", methods="PUT")
     */
    public function handle()
    {
        //接收参数
        $params['tagId']    = $this->request->input('tagId');
        $params['groupId']  = $this->request->input('groupId');
        $params['tagName']  = $this->request->input('tagName');
        $params['isUpdate'] = $this->request->input('isUpdate');

        //验证参数
        $this->validated($params);

        return $this->updateLogic->handle($params);
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'tagId'    => 'required',
            'groupId'  => 'required',
            'tagName'  => 'required',
            'isUpdate' => 'required',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
            'tagId.required'    => '标签id必传',
            'groupId.required'  => '分组id必传',
            'tagName.required'  => '标签名称必传',
            'isUpdate.required' => '是否修改信息必传',
        ];
    }
}
