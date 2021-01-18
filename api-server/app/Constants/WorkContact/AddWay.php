<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Constants\WorkContact;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * 客户来源枚举.
 * @Constants
 * @method static getMessage($code, array $options = []) 获取枚举值
 */
class AddWay extends AbstractConstants
{
    /**
     * @Message("其他渠道")
     */
    const OTHER_CHANNELS = 0;

    /**
     * @Message("扫描二维码")
     */
    const SCAN_QR_CODE = 1;

    /**
     * @Message("搜索手机号")
     */
    const SEARCH_MOBILE_PHONE = 2;

    /**
     * @Message("名片分享")
     */
    const BUSINESS_CARD_SHARING = 3;

    /**
     * @Message("群聊")
     */
    const GROUP_CHAT = 4;

    /**
     * @Message("手机通讯录")
     */
    const MOBILE_PHONE_ADDRESS_BOOK = 5;

    /**
     * @Message("微信联系人")
     */
    const WE_CHAT_CONTACT = 6;

    /**
     * @Message("来自微信的添加好友申请")
     */
    const ADD_FRIEND_FROM_WE_CHAT = 7;

    /**
     * @Message("安装第三方应用时自动添加的客服人员")
     */
    const CUSTOMER_SERVICE = 8;

    /**
     * @Message("搜索邮箱")
     */
    const SEARCH_EMAIL = 9;

    /**
     * @Message("内部成员共享")
     */
    const IN_MEMBER_SHARE = 201;

    /**
     * @Message("管理员/负责人分配")
     */
    const ADMIN_ASSIGNMENT = 202;

    /**
     * @Message("渠道活码")
     */
    const CHANNEL_CODE = 1001;

    /**
     * @Message("自动拉群")
     */
    const AUTO_GROUP = 1002;

//    /**
//     * @Message("裂变引流")
//     */
//    const FISSION_DRAINAGE = 1003;
//
//    /**
//     * @Message("抽奖引流")
//     */
//    const DRAW = 1004;

    public static $Enum = [
        self::OTHER_CHANNELS => '其他渠道',
        self::SCAN_QR_CODE   => '扫描二维码', //以下四个为扫描二维码的方式
        self::CHANNEL_CODE   => '渠道活码',
        self::AUTO_GROUP     => '自动拉群',
        //  self::FISSION_DRAINAGE => '裂变引流',
        // self::DRAW             => '抽奖引流',
        self::SEARCH_MOBILE_PHONE       => '搜索手机号',
        self::BUSINESS_CARD_SHARING     => '名片分享',
        self::GROUP_CHAT                => '群聊',
        self::MOBILE_PHONE_ADDRESS_BOOK => '手机通讯录',
        self::WE_CHAT_CONTACT           => '微信联系人',
        self::ADD_FRIEND_FROM_WE_CHAT   => '来自微信的添加好友申请',
        self::CUSTOMER_SERVICE          => '安装第三方应用时自动添加的客服人员',
        self::SEARCH_EMAIL              => '搜索邮箱',
        self::IN_MEMBER_SHARE           => '内部成员共享',
        self::ADMIN_ASSIGNMENT          => '管理员/负责人分配',
    ];
}
