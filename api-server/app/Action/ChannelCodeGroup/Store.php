<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\ChannelCodeGroup;

use App\Contract\ChannelCodeGroupServiceInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use MoChat\Framework\Action\AbstractAction;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;
use MoChat\Framework\Request\ValidateSceneTrait;

/**
 * 创建渠道码分组.
 *
 * Class Store
 * @Controller
 */
class Store extends AbstractAction
{
    use ValidateSceneTrait;

    /**
     * @Inject
     * @var ChannelCodeGroupServiceInterface
     */
    private $channelCodeGroup;

    /**
     * @RequestMapping(path="/channelCodeGroup/store", methods="POST")
     */
    public function handle()
    {
        //企业id
        $corpIds = user()['corpIds'];
        if (count($corpIds) != 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请先选择企业');
        }

        //接收参数
        $params['name'] = $this->request->input('name');

        //验证参数
        $this->validated($params);

        $data = [];
        foreach ($params['name'] as $val) {
            $data[] = [
                'corp_id' => $corpIds[0],
                'name'    => $val,
            ];
        }
        //查询是否已存在相同分组名称
        $info = $this->channelCodeGroup
            ->getChannelCodeGroupsByNames($params['name']);

        if (! empty($info)) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '已存在相同分组名');
        }

        $res = $this->channelCodeGroup->createChannelCodeGroups($data);

        if ($res != true) {
            throw new CommonException(ErrorCode::SERVER_ERROR, '渠道码分组创建失败');
        }
    }

    /**
     * @return string[] 规则
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    public function messages(): array
    {
        return [
            'name.required' => '分组名称必传',
        ];
    }
}
