<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\ContactField\Traits;

use App\Contract\ContactFieldServiceInterface;
use Hyperf\Utils\ApplicationContext;
use MoChat\Framework\Constants\ErrorCode;
use MoChat\Framework\Exception\CommonException;

trait RequestTrait
{
    /**
     * 属性替换.
     * @return array|string[] ...
     */
    public function attributes(): array
    {
        return [
            'id'      => 'ID',
            'label'   => '字段名称',
            'type'    => '字段类型',
            'options' => '选项内容',
            'order'   => '排序展示',
            'status'  => '使用状态',
        ];
    }

    /**
     * 验证场景.
     * @return array|array[] 场景规则
     */
    public function scene(): array
    {
        return [
            'store'        => ['label', 'type', 'options', 'order', 'status'],
            'update'       => ['id', 'label', 'type', 'options', 'order', 'status'],
            'show'         => ['id'],
            'destroy'      => ['id'],
            'statusUpdate' => ['id', 'status'],
        ];
    }

    /**
     * 验证规则.
     */
    protected function rules(): array
    {
        return [
            'id'      => 'required|integer',
            'label'   => 'required|max:8',
            'type'    => 'required',
            'options' => 'array',
            'order'   => 'integer',
            'status'  => 'integer|in:0,1',
        ];
    }

    /**
     * 获取已定义验证规则的错误消息.
     */
    protected function messages(): array
    {
        return [
            'status.integer' => '状态必须为数字',
            'status.in'      => '状态必须在 0, 1中选择一个',
            'options.array'  => ':attribute 格式错误: 必须为数组',
        ];
    }

    /**
     * 其它验证
     * @param array $inputs ...
     * @param string $scene ...
     */
    protected function validateExtend(array $inputs, string $scene): void
    {
        if (in_array($scene, ['store', 'update'])) {
            $where   = isset($inputs['id']) ? [['id', '!=', $inputs['id']]] : [];
            $isExist = ApplicationContext::getContainer()->get(ContactFieldServiceInterface::class)->existContactFieldsByLabel(
                $inputs['label'],
                $where
            );
            if ($isExist) {
                throw new CommonException(ErrorCode::INVALID_PARAMS, sprintf('属性[%s]已经存在', $inputs['label']));
            }
        }
    }
}
