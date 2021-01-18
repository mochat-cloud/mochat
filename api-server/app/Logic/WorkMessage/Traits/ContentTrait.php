<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Logic\WorkMessage\Traits;

use App\Constants\WorkMessage\MsgType;

trait ContentTrait
{
    /**
     * 处理消息.
     * @param array $content ...
     * @param int $msgType ...
     * @return array ...
     */
    protected function contentFormat(array $content, int $msgType): array
    {
        $newContent = [
            'content'     => '',
            'ossFullPath' => '',
        ];
        ## 文字
        isset($content['content']) && $newContent['content'] = $content['content'];
        ## 图片全地址
        isset($content['ossPath']) && $newContent['ossFullPath'] = file_full_url($content['ossPath']);

        ## 其它类型
        if (in_array($msgType, MsgType::$otherType, true)) {
            $newContent['content'] = MsgType::getMessage($msgType);
            switch ($msgType) {
                case MsgType::MIXED:
                    $newContent['item'] = array_map(function ($item) {
                        return $this->contentItemFormat($item);
                    }, $content['item']);
                    break;
                case MsgType::LOCATION:
                    $newContent['content'] = '[地址]' . $content['title'] . '/' . $content['address'];
                    break;
                case MsgType::MINI_PROGRAM:
                    $newContent['content'] = '[小程序]' . $content['displayname'] . '/' . $content['title'] . '/' . $content['description'];
                    break;
                case MsgType::CARD:
                    $newContent['content'] = '[个人名片]' . $content['corpname'] . '/' . $content['userid'];
                    break;
                case MsgType::RED_PACKET:
                    $newContent['content'] = '[红包]总个数:' . $content['totalcnt'] . '/总金额' . $content['totalamount'] * 0.01;
                    break;
                case MsgType::DOC_MSG:
                    $newContent['content'] = '[在线文档]' . $content['title'] . '/' . $content['doc_creator'] . '/链接:' . $content['link_url'];
                    break;
                default:
                    break;
            }
        } else {
            ## 基本类型
            switch ($msgType) {
                ## 小程序
                case MsgType::MINI_PROGRAM:
                    $newContent = $content;
                    break;
            }
        }

        return $newContent;
    }

    /**
     * 处理消息Item.
     * @param array $item ...
     * @return array ...
     */
    protected function contentItemFormat(array $item): array
    {
        $newItem = [
            'content'     => '',
            'ossFullPath' => '',
        ];

        switch ($item['type']) {
            case 'text':
                $newItem['content'] = '[混合消息-文本]' . $item['content'];
                break;
            case 'image':
            case 'emotion':
            isset($item['ossPath']) && $newItem['ossFullPath'] = file_full_url($item['ossPath']);
                break;
            default:
                break;
        }

        return $newItem;
    }
}
