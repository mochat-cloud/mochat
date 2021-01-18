<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace App\Constants\ContactField;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 * @method static string getName(int $id)  获取字段标识
 * @method static string getLabel(int $id)  获取字段名称
 * @method static string getTypeEnum(int $id)  获取字段类型枚举
 * @method static string getOptions(int $id)  获取字段选项内容
 */
class OptionData extends AbstractConstants
{
    /**
     * @Name("phone")
     * @Label("手机号")
     * @TypeEnum("PHONE")
     * @Options("")
     */
    public const PHONE = 1;

    /**
     * @Name("name")
     * @Label("姓名")
     * @TypeEnum("TEXT")
     * @Options("")
     */
    public const NAME = 2;

    /**
     * @Name("gender")
     * @Label("性别")
     * @TypeEnum("RADIO")
     * @Options("男,女,未知")
     */
    public const GENDER = 3;

    /**
     * @Name("birthday")
     * @Label("生日")
     * @TypeEnum("DATE")
     * @Options("")
     */
    public const BIRTHDAY = 4;

    /**
     * @Name("age")
     * @Label("年龄")
     * @TypeEnum("NUMBER")
     * @Options("")
     */
    public const AGE = 5;

    /**
     * @Name("QQ")
     * @Label("QQ")
     * @TypeEnum("TEXT")
     * @Options("")
     */
    public const QQ = 6;

    /**
     * @Name("email")
     * @Label("邮箱")
     * @TypeEnum("EMAIL")
     * @Options("")
     */
    public const EMAIL = 7;

    /**
     * @Name("hobby")
     * @Label("爱好")
     * @TypeEnum("CHECKBOX")
     * @Options("游戏,阅读,音乐,运动,动漫,旅行,家居,曲艺,宠物,美食,娱乐,电影,电视剧,健康养生,数码,其他")
     */
    public const HOBBY = 8;

    /**
     * @Name("education")
     * @Label("学历")
     * @TypeEnum("SELECT")
     * @Options("博士,硕士,大学,大专,高中,初中,小学,其他")
     */
    public const EDUCATION = 9;

    /**
     * @Name("annualIncome")
     * @Label("年收入")
     * @TypeEnum("SELECT")
     * @Options("5万以下,5万-15万,15万-30万,30万以上,50-100万,100万-200万,200万-500万,500万-1000万,1000万-5000万")
     */
    public const ANNUAL_INCOME = 10;

    /**
     * @Name("industryBusiness")
     * @Label("行业")
     * @TypeEnum("SELECT")
     * @Options("IT/互联网/通信/电子,金融/投资/财会/保险,广告/媒体/出版/艺术,市场/销售/客服,人力资源/行政/高级管理,建筑/房产/物业,采购/贸易/物流/交通,咨询/法律/认证,生产/制造,生物/制药/医疗/护理,教育/培训/翻译/公务员,科研/环保/农业/能源,服务业,其他")
     */
    public const INDUSTRY_BUSINESS = 11;

    /**
     * @Name("company")
     * @Label("公司")
     * @TypeEnum("TEXT")
     * @Options("")
     */
    public const COMPANY = 12;

    /**
     * @Name("area")
     * @Label("区域")
     * @TypeEnum("TEXT")
     * @Options("")
     */
    public const AREA = 13;

    /**
     * @Name("address")
     * @Label("地址")
     * @TypeEnum("TEXT")
     * @Options("")
     */
    public const ADDRESS = 14;

    /**
     * @Name("idCard")
     * @Label("身份证")
     * @TypeEnum("TEXT")
     * @Options("")
     */
    public const ID_CARD = 15;

    /**
     * @Name("picture")
     * @Label("图片")
     * @TypeEnum("PICTURE")
     * @Options("")
     */
    public const PICTURE = 16;

    /**
     * @var int[] 数据列表
     */
    public static $optionData = [
        self::PHONE,
        self::NAME,
        self::GENDER,
        self::BIRTHDAY,
        self::AGE,
        self::QQ,
        self::EMAIL,
        self::HOBBY,
        self::EDUCATION,
        self::ANNUAL_INCOME,
        self::INDUSTRY_BUSINESS,
        self::COMPANY,
        self::AREA,
        self::ADDRESS,
        self::ID_CARD,
        self::PICTURE,
    ];
}
