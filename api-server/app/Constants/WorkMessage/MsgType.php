<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Constants\WorkMessage;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 * @method static string getMessage(int $type)  获取消息类型
 */
class MsgType extends AbstractConstants
{
    /**
     * @Message("文本")
     */
    public const TEXT = 1;

    /**
     * @Message("图片")
     */
    public const PICTURE = 2;

    /**
     * @Message("图文")
     */
    public const PICTURE_TEXT = 3;

    /**
     * @Message("音频")
     */
    public const VOICE = 4;

    /**
     * @Message("视频")
     */
    public const VIDEO = 5;

    /**
     * @Message("小程序")
     */
    public const MINI_PROGRAM = 6;

    /**
     * @Message("文件")
     */
    public const FILE = 7;

    /**
     * @Message("撤回消息")
     */
    public const REVOKE = 8;

    /**
     * @Message("同意会话聊天内容")
     */
    public const AGREE = 9;

    /**
     * @Message("名片")
     */
    public const CARD = 10;

    /**
     * @Message("位置")
     */
    public const LOCATION = 11;

    /**
     * @Message("表情")
     */
    public const EMOTION = 12;

    /**
     * @Message("链接")
     */
    public const LINK = 13;

    /**
     * @Message("会话记录消息")
     */
    public const CHAT_RECORD = 14;

    /**
     * @Message("会话记录消息item")
     */
    public const CHAT_RECORD_ITEM = 15;

    /**
     * @Message("待办消息")
     */
    public const TODO = 16;

    /**
     * @Message("投票消息")
     */
    public const VOTE = 17;

    /**
     * @Message("填表消息")
     */
    public const COLLECT = 18;

    /**
     * @Message("红包消息")
     */
    public const RED_PACKET = 19;

    /**
     * @Message("会议邀请消息")
     */
    public const MEETING = 20;

    /**
     * @Message("在线文档消息")
     */
    public const DOC_MSG = 21;

    /**
     * @Message("MarkDown")
     */
    public const MARKDOWN = 22;

    /**
     * @Message("日程消息")
     */
    public const CALENDAR = 23;

    /**
     * @Message("混合消息")
     */
    public const MIXED = 24;

    /**
     * @Message("音频存档消息")
     */
    public const MEETING_VOICE_CALL = 25;

    /**
     * @Message("音频共享文档消息")
     */
    public const VOIP_DOC_SHARE = 26;

    /**
     * @var array 固定的类型
     */
    public static $fixedType = [
        'text'  => self::TEXT,
        'image' => self::PICTURE,
        'news'  => self::PICTURE_TEXT,
        'voice' => self::VOICE,
        'video' => self::VIDEO,
        'weapp' => self::MINI_PROGRAM,
        'file'  => self::FILE,
    ];

    /**
     * @var array 其它的类型
     */
    public static $otherType = [
        'revoke'             => self::REVOKE,
        'agree'              => self::AGREE,
        'card'               => self::CARD,
        'location'           => self::LOCATION,
        'emotion'            => self::EMOTION,
        'link'               => self::LINK,
        'chatrecord'         => self::CHAT_RECORD,
        'chatrecord_item'    => self::CHAT_RECORD_ITEM,
        'todo'               => self::TODO,
        'vote'               => self::VOTE,
        'collect'            => self::COLLECT,
        'redpacket'          => self::RED_PACKET,
        'meeting'            => self::MEETING,
        'docmsg'             => self::DOC_MSG,
        'markdown'           => self::MARKDOWN,
        'calendar'           => self::CALENDAR,
        'mixed'              => self::MIXED,
        'meeting_voice_call' => self::MEETING_VOICE_CALL,
        'voip_doc_share'     => self::VOIP_DOC_SHARE,
    ];
}
