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

use App\Logic\WorkRoomAutoPull\StoreLogic;
use App\Middleware\PermissionMiddleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 自动拉群管理- 创建提交.
 *
 * Class Store.
 * @Controller
 */
class Store extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var StoreLogic
     */
    protected $storeLogic;

    /**
     * @RequestMapping(path="/workRoomAutoPull/store", methods="post")
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
            'corp_id'       => $this->request->input('corpId'),
            'qrcode_name'   => $this->request->input('qrcodeName'),
            'is_verified'   => $this->request->input('isVerified'),
            'leading_words' => $this->request->input('leadingWords', ''),
            'employees'     => $this->request->input('employees'),
            'tags'          => $this->request->input('tags'),
            'rooms'         => $this->request->input('rooms'),
        ];

        return $this->storeLogic->handle($params);
    }

    /**
     * 验证规则.
     *
     * @return array 响应数据
     */
    protected function rules(): array
    {
        return [
            'corpId'       => 'required | integer | min:0 | bail',
            'qrcodeName'   => 'required | string | between:1,30 | bail',
            'isVerified'   => 'required | integer | in:1,2, | bail',
            'leadingWords' => 'required | string | between:1,1000 | bail',
            'employees'    => 'required | string | min:1 | bail',
            'tags'         => 'required | string | min:1 | bail',
            'rooms'        => 'required | json | bail',
        ];
    }

    /**
     * 验证错误提示.
     * @return array 响应数据
     */
    protected function messages(): array
    {
        return [
            'corpId.required'       => '公司授信ID 必填',
            'corpId.integer'        => '公司授信ID 必需为整数',
            'corpId.min'            => '公司授信ID 不可小于1',
            'qrcodeName.required'   => '扫码名称 必填',
            'qrcodeName.string'     => '扫码名称 必需是字符串类型',
            'qrcodeName.between'    => '扫码名称  字符串最大长度30',
            'isVerified.required'   => '添加验证 必填',
            'isVerified.integer'    => '添加验证 必需为整数',
            'isVerified.in'         => '添加验证 值必须在列表内：[1,2]',
            'leadingWords.required' => '入群引导语 必填',
            'leadingWords.string'   => '入群引导语 必需是字符串类型',
            'leadingWords.between'  => '扫码名称  字符串最大长度1000',
            'employees.required'    => '使用成员 必填',
            'employees.string'      => '使用成员 必需是字符串类型',
            'employees.min'         => '使用成员  字符串不可为空',
            'tags.required'         => '客户标签 必填',
            'tags.string'           => '客户标签 必需是字符串类型',
            'tags.min'              => '客户标签  字符串不可为空',
            'rooms.required'        => '客户群聊 必填',
            'rooms.json'            => '客户群聊 必需是JSON类型',
        ];
    }
}
