<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Action\Medium\Traits;

use Hyperf\Validation\Rule;
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
            'mediumGroupId'       => '分组ID',
            'type'                => '素材类型',
            'content'             => '内容数据',
            'content.title'       => '标题',
            'content.content'     => '内容',
            'content.imagePath'   => '图片',
            'content.description' => '描述',
            'content.imageLink'   => '跳转链接',
            'content.voicePath'   => '音频',
            'content.videoPath'   => '视频',
            'content.appid'       => '小程序appid',
            'content.page'        => '小程序路径',
            'content.filePath'    => '文件',
        ];
    }

    /**
     * 验证场景.
     * @return array|array[] 场景规则
     */
    public function scene(): array
    {
        return [
            'store' => [
                'mediumGroupId', 'type', 'content', 'content.title', 'content.content', 'content.imagePath', 'content.description',
                'content.imageLink', 'content.voicePath', 'content.videoPath', 'content.appid', 'content.page', 'content.filePath',
            ],
            'update' => [
                'id',
                'mediumGroupId', 'type', 'content', 'content.title', 'content.content', 'content.imagePath', 'content.description',
                'content.imageLink', 'content.voicePath', 'content.videoPath', 'content.appid', 'content.page', 'content.filePath',
            ],
            'show'        => ['id'],
            'destroy'     => ['id'],
            'groupUpdate' => ['id', 'mediumGroupId'],
        ];
    }

    /**
     * 扩展验证
     * @param array $inputs 请求参数
     * @param string $validateScene 场景
     */
    public function validateExtend(array $inputs, string $validateScene): void
    {
        ## 当前企业
        $corpIds = user('corpIds');
        if (count($corpIds) !== 1) {
            throw new CommonException(ErrorCode::INVALID_PARAMS, '请选择一个企业，再进行操作');
        }
    }

    /**
     * 验证规则.
     */
    protected function rules(array $inputs): array
    {
        return [
            'id'            => 'required|integer',
            'mediumGroupId' => 'required|integer',
            'type'          => 'required|in:1,2,3,4,5,6,7',
            'content'       => 'required|array',
            ## 文本
            'content.title' => Rule::requiredIf(function () use ($inputs) {
                return in_array($inputs['type'], [1, 3, 6]);
            }),
            'content.content' => 'required_if:type,1',
            ## 图片
            'content.imagePath' => Rule::requiredIf(function () use ($inputs) {
                return in_array($inputs['type'], [2, 3, 6]);
            }),
            ## 图文
            //            'content.title'            => 'required_if:type,3|max:32',
            'content.description' => 'max:128|string|nullable',
            //            'content.imagePath'            => 'required_if:type,3',
            'content.imageLink' => 'required_if:type,3',
            ## 音频
            'content.voicePath' => 'required_if:type,4',
            ## 视频
            'content.videoPath' => 'required_if:type,5',
            ## 小程序
            'content.appid' => 'required_if:type,6',
            'content.page'  => 'required_if:type,6',
            //            'content.title'    => 'required_if:type,6',
            //            'content.imagePath'    => 'required_if:type,6',
            ## 文件
            'content.filePath' => 'required_if:type,7',
        ];
    }
}
