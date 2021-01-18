<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\WorkRoomAutoPull;

use App\Logic\WorkRoomAutoPull\UpdateLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 自动拉群管理- 更新提交.
 *
 * Class Update.
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
     * @RequestMapping(path="/workRoomAutoPull/update", methods="put")
     * @Middleware(PermissionMiddleware::class)
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \League\Flysystem\FileExistsException
     * @return array 返回数组
     */
    public function handle(): array
    {
        ## 参数验证
        $this->validated($this->request->all());
        ## 接收参数
        $params = [
            'workRoomAutoPullId' => $this->request->input('workRoomAutoPullId'),
            'is_verified'        => $this->request->input('isVerified'),
            'employees'          => $this->request->input('employees'),
            'tags'               => $this->request->input('tags'),
            'rooms'              => $this->request->input('rooms'),
        ];

        return $this->updateLogic->handle($params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'workRoomAutoPullId' => 'required | integer | min:0 | bail',
            'isVerified'         => 'required | integer | in:1,2, | bail',
            'employees'          => 'required | min:1 | bail',
            'tags'               => 'required | min:1 | bail',
            'rooms'              => 'required | json | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'workRoomAutoPullId.required' => '自动拉群ID 必填',
            'workRoomAutoPullId.integer'  => '自动拉群ID 必需为整数',
            'workRoomAutoPullId.min'      => '自动拉群ID 不可小于1',
            'isVerified.required'         => '添加验证 必填',
            'isVerified.integer'          => '添加验证 必需为整数',
            'isVerified.in'               => '添加验证 值必须在列表内：[1,2]',
            'employees.required'          => '使用成员 必填',
            'employees.string'            => '使用成员 必需是字符串类型',
            'employees.min'               => '使用成员  字符串不可为空',
            'tags.required'               => '客户标签 必填',
            'tags.string'                 => '客户标签 必需是字符串类型',
            'tags.min'                    => '客户标签  字符串不可为空',
            'rooms.required'              => '客户群聊 必填',
            'rooms.json'                  => '客户群聊 必需是JSON类型',
        ];
    }
}
