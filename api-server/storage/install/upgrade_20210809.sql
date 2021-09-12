
CREATE TABLE IF NOT EXISTS `mc_auto_tag`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NULL DEFAULT 1 COMMENT '类型（1：关键词打标签。2：客户入群行为打标签。3：分时段打标签）',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '规则名称',
  `employees` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '生效成员（关键词打标签，分时段打标签）',
  `fuzzy_match_keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '模糊匹配关键词',
  `exact_match_keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '精准匹配关键词',
  `tag_rule` json NULL COMMENT '标签规则',
  `tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '标签组',
  `on_off` tinyint(1) NULL DEFAULT 1 COMMENT '规则状态（1：开，2：关）',
  `mark_tag_count` int(11) NULL DEFAULT 0 COMMENT '已打标签数',
  `tenant_id` int(11) NULL DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) NULL DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '自动打标签-基本信息表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_auto_tag_record`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `auto_tag_id` int(11) NOT NULL COMMENT '标签id(mc_auto_tag.id)',
  `contact_id` int(11) NOT NULL COMMENT '客户id(mc_work_contact.id)',
  `tag_rule_id` int(11) NULL DEFAULT NULL COMMENT '标签规则ID',
  `wx_external_userid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '客户wx_external_userid',
  `employee_id` int(11) NULL DEFAULT NULL COMMENT '所属员工id(mc_work_employee.id)',
  `keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '触发关键词',
  `contact_room_id` int(11) NULL DEFAULT 0 COMMENT '客户群id',
  `tags` json NULL COMMENT '标签',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业ID(mc_corp.id)',
  `trigger_count` int(11) NULL DEFAULT NULL COMMENT '触发次数',
  `status` tinyint(1) NULL DEFAULT NULL COMMENT '状态（0：未打标签，1：已打标签）',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '自动打标签-记录表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_clock_in`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '活动名称',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '活动说明',
  `type` tinyint(1) NOT NULL COMMENT '打卡类型（1：连续打卡，2：累计打卡）',
  `tasks` json NOT NULL COMMENT '任务设置',
  `time_type` tinyint(1) NOT NULL COMMENT '截止日期（1：永久有效，2，自定义活动时间）',
  `start_time` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '开始时间（为空永久有效，不为空自定义活动时间）',
  `end_time` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '结束时间',
  `employee_qrcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '客服二维码（用户领奖）',
  `corp_card_status` tinyint(255) NULL DEFAULT NULL COMMENT '企业名片状态（0：关，1开）',
  `corp_card` json NULL COMMENT '企业名片（头像、名称、简介）',
  `contact_clock_tags` json NULL COMMENT '客户标签',
  `point` int(11) NULL DEFAULT NULL COMMENT '客户积分',
  `tenant_id` int(11) NULL DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) NULL DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '群打卡-基本信息表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_clock_in_contact`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `clock_in_id` int(11) NOT NULL COMMENT '活动id',
  `union_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '微信union_id',
  `contact_id` int(11) NULL DEFAULT 0 COMMENT '客户id（mc_work_contact.id。不能匹配时为0）',
  `nickname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '昵称',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '头像',
  `employee_ids` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '企业员工',
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '城市',
  `contact_clock_tags` json NULL COMMENT '客户标签',
  `total_day` int(11) NULL DEFAULT 0 COMMENT '总打卡天数',
  `series_day` int(11) NULL DEFAULT 0 COMMENT '连续打卡天数',
  `status` tinyint(11) NULL DEFAULT 0 COMMENT '状态（0：未完成，1：已完成）',
  `receive_level` tinyint(1) NULL DEFAULT 0 COMMENT '已领取奖励任务阶段',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '群打卡-客户表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_clock_in_contact_record`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL COMMENT '客户id（mc_clock_in_contact.id）',
  `day` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '打卡时间',
  `type` tinyint(1) NOT NULL COMMENT '打卡类型（1：连续打卡，2：累计打卡）',
  `clock_in_id` int(11) NOT NULL COMMENT '活动id（mc_clock_in.id）',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '群打卡-客户打卡记录表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_lottery`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '活动名称',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '活动说明',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '活动模板（roulette：转盘）',
  `time_type` tinyint(1) NOT NULL COMMENT '截止日期（1：永久有效，2，自定义活动时间）',
  `start_time` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '开始时间（为空永久有效，不为空自定义活动时间）',
  `end_time` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '结束时间',
  `contact_tags` json NULL COMMENT '客户标签',
  `tenant_id` int(11) NULL DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) NULL DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '抽奖活动-基本信息表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_lottery_contact`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lottery_id` int(11) NOT NULL COMMENT '活动id',
  `union_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '微信union_id',
  `contact_id` int(11) NULL DEFAULT 0 COMMENT '客户id（mc_work_contact.id。不能匹配时为0）',
  `nickname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '昵称',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '头像',
  `employee_ids` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '企业员工',
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '城市',
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '来源',
  `grade` int(11) NULL DEFAULT 0 COMMENT '客户评分',
  `contact_tags` json NULL COMMENT '客户标签',
  `draw_num` int(11) NULL DEFAULT 0 COMMENT '抽奖次数',
  `win_num` int(11) NULL DEFAULT 0 COMMENT '获奖次数',
  `status` tinyint(11) NULL DEFAULT 0 COMMENT '状态（0：未完成，1：已完成）',
  `write_off` tinyint(1) NULL DEFAULT 0 COMMENT '核销（0：未核销，1：已核销）',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '抽奖活动--客户表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_lottery_contact_record`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lottery_id` int(11) NOT NULL COMMENT '活动id',
  `contact_id` int(11) NOT NULL COMMENT '客户id（mc_lottery_contact.id）',
  `prize_id` int(11) NOT NULL COMMENT '奖品id',
  `prize_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '奖品名称',
  `receive_status` tinyint(1) NULL DEFAULT 0 COMMENT '领奖状态（0：未领取。1：已领取）',
  `receive_qr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '客服二维码链接',
  `receive_type` tinyint(1) NOT NULL COMMENT '兑奖方式（1：客服二维码，2：兑换码）',
  `receive_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '兑换码',
  `write_off` tinyint(255) NOT NULL DEFAULT 0 COMMENT '核销（0：未核销，1：已核销）',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '抽奖活动-客户参与记录表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_lottery_prize`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lottery_id` int(11) NOT NULL COMMENT '活动id',
  `prize_set` json NULL COMMENT '奖品设置',
  `is_show` tinyint(1) NOT NULL COMMENT '实时展示已中奖客户记录(0:否，1：是）',
  `exchange_set` json NULL COMMENT '兑奖设置',
  `draw_set` json NULL COMMENT '抽奖限制设置',
  `win_set` json NULL COMMENT '中奖限制设置',
  `corp_card` json NULL COMMENT '企业名片（头像、名称、简介）',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '抽奖活动-奖品信息表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_official_account`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `appid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '第三方平台 appid',
  `authorized_status` tinyint(1) NULL DEFAULT NULL COMMENT '授权状态（1：授权成功，2：更新授权，3：取消授权）',
  `authorizer_appid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '公众号或小程序的 appid',
  `authorization_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '授权码，可用于获取授权信息',
  `pre_auth_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '预授权码\r\n预授权码',
  `head_img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '头像',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '头像服务器地址',
  `business_info` json NULL COMMENT '{\"open_pay\": 0, \"open_shake\": 0, \"open_scan\": 0, \"open_card\": 0, \"open_store\": 0}',
  `modules` json NULL COMMENT '[\"contact_way_region\", \"raffle_activity\", \"check_in\", \"radar\"]',
  `news_offset` tinyint(1) NULL DEFAULT NULL COMMENT '（0：已关闭，1：开启）',
  `nickname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '企业昵称',
  `service_type_info` tinyint(1) NULL DEFAULT 0 COMMENT '公众号类型（0：订阅号，1由历史老帐号升级后的订阅号：，2：服务号）',
  `verify_type_info` tinyint(1) NULL DEFAULT 0 COMMENT '服务号\r\n公众号认证类型(-1:未认证，0：微信认证，:1：新浪微博认证，2：腾讯微博认证，3：已资质认证通过但还未通过名称认证，4：已资质认证通过、还未通过名称认证，但通过了新浪微博认证',
  `original_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `func_info` json NULL COMMENT '[\r\n      {\r\n        \"funcscope_category\": {\r\n          \"id\": 1\r\n        }\r\n      },\r\n      {\r\n        \"funcscope_category\": {\r\n          \"id\": 2\r\n        }\r\n      }\r\n    ]',
  `principal_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `alias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '公众号所设置的微信号，可能为空',
  `qrcode_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '二维码图片的 UR',
  `local_qrcode_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '二维码图片的 UR(服务器地址）',
  `callback_suffix` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `callback_verified` tinyint(1) NULL DEFAULT NULL,
  `user_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '原始id',
  `encoding_aes_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '第三方平台消息加解密  Key',
  `notify_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '第三方平台appserect',
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '第三方平台token',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '授权时间',
  `tenant_id` int(11) NULL DEFAULT 0 COMMENT '租户id',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) NULL DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '公众号授权表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_official_account_set`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `official_account_id` tinyint(1) NULL DEFAULT NULL,
  `type` tinyint(1) NULL DEFAULT NULL COMMENT '授权模块（1：群打卡，2：抽奖活动，3：门店活码，4：互动雷达）',
  `tenant_id` int(11) NULL DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) NULL DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '公众号设置表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_radar`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL COMMENT '雷达类型（1：链接，2：PDF，3：文章）',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '雷达标题',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '雷达链接',
  `link_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '链接标题',
  `link_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '链接摘要',
  `link_cover` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '链接封面',
  `pdf_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'PDF名称',
  `pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '雷达PDF',
  `article_type` tinyint(1) NULL DEFAULT NULL COMMENT '雷达文章类型（1：提取公众号文章，2:新建文章素材）',
  `article` json NULL COMMENT '雷达文章',
  `employee_card` tinyint(1) NULL DEFAULT 0 COMMENT '成员名片（0：不显示，1：显示）',
  `action_notice` tinyint(1) NULL DEFAULT 0 COMMENT '行为通知（0：不通知，1：通知）',
  `dynamic_notice` tinyint(1) NULL DEFAULT 0 COMMENT '动态通知（0：不通知，1：通知）',
  `contact_tags` json NULL COMMENT '客户标签',
  `tag_status` tinyint(1) NULL DEFAULT NULL COMMENT '客户标签(0:否，1：是)',
  `contact_grade` json NULL COMMENT '客户评分',
  `tenant_id` int(11) NULL DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) NULL DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '互动雷达-基本信息表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_radar_channel`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '渠道名称',
  `tenant_id` int(11) NULL DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) NULL DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '互动雷达-渠道信息表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_radar_channel_link`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `radar_id` int(11) NOT NULL COMMENT '雷达id(mc_radar.id)',
  `channel_id` int(11) NOT NULL COMMENT '渠道id(mc_radar_channel.id)',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '渠道链接',
  `employee_id` int(11) NOT NULL DEFAULT 0 COMMENT '员工id',
  `click_num` int(11) NOT NULL DEFAULT 0 COMMENT '点击次数',
  `click_person_num` int(11) NOT NULL DEFAULT 0 COMMENT '点击人数',
  `tenant_id` int(11) NULL DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) NULL DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `Un_INDEX`(`radar_id`, `channel_id`, `corp_id`, `employee_id`) USING BTREE COMMENT '唯一索引'
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '互动雷达-渠道链接信息表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_radar_record`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `radar_id` int(11) NOT NULL COMMENT '雷达id(mc_radar.id)',
  `channel_id` int(11) NOT NULL COMMENT '渠道id(mc_radar_channel.id)',
  `type` tinyint(1) NOT NULL COMMENT '客户类型（1：企业客户，2：非企业客户）',
  `union_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '客户union_id',
  `nickname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '昵称',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '头像',
  `contact_id` int(11) NULL DEFAULT NULL COMMENT '客户id',
  `employee_id` int(11) NOT NULL COMMENT '企业员工id',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '内容',
  `corp_id` int(11) NOT NULL DEFAULT 0 COMMENT '企业id',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '互动雷达-客户表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_calendar`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '日历名称',
  `rooms` json NULL COMMENT '群聊',
  `on_off` tinyint(1) NULL DEFAULT 1 COMMENT '开关（1：开，2：关）',
  `tenant_id` int(11) NULL DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) NULL DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '群日历-基本信息表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_calendar_push`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_calendar_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '群日历id',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '推送内容名称',
  `day` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '推送时间',
  `push_content` json NULL COMMENT '发送内容',
  `on_off` tinyint(1) NULL DEFAULT 1 COMMENT '开关（1：开，2：关）',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态（1：未推送，2：已推送）',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '群日历-推送信息表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_calendar_record`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_calendar_id` int(11) NOT NULL COMMENT '群日历id',
  `push_ids` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '推送消息ids',
  `day` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '推送时间',
  `room_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '群聊id',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB  CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '群日历-推送信息记录表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_fission`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `official_account_id` int(11) NULL DEFAULT 0 COMMENT '公众号id',
  `active_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '活动名称',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '活动结束时间',
  `target_count` int(11) NULL DEFAULT 0 COMMENT '活动目标人数',
  `new_friend` tinyint(1) NULL DEFAULT 0 COMMENT '必须新好友才能助力（0：否，1：是）',
  `delete_invalid` tinyint(1) NULL DEFAULT 0 COMMENT '好友退出全部群聊后助力失效（0：否，1：是）',
  `receive_employees` json NULL COMMENT '领奖客服成员',
  `auto_pass` tinyint(1) NULL DEFAULT NULL COMMENT '自动通过好友申请',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态（1：进行中，2：已完成）',
  `tenant_id` int(11) NULL DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) NOT NULL DEFAULT 0 COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '群裂变-基础信息主表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_fission_contact`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fission_id` int(11) NOT NULL DEFAULT 0 COMMENT '活动ID',
  `union_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '客户微信id',
  `nickname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '客户微信昵称',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '客户微信头像',
  `parent_union_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '0' COMMENT '上级（被谁邀请来的）',
  `level` tinyint(1) NULL DEFAULT 0 COMMENT '裂变等级',
  `contact_id` int(11) NOT NULL DEFAULT 0 COMMENT '客户ID',
  `employee` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '添加的员工',
  `invite_count` int(11) NOT NULL DEFAULT 0 COMMENT '邀请数量',
  `loss` tinyint(1) NULL DEFAULT 0 COMMENT '是否已流失（被删除好友）（0：否，1：是）',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '完成状态。（0：未完成，1：已完成）',
  `receive_status` tinyint(1) NULL DEFAULT 0 COMMENT '领取状态（0：未领取，1：已领取）',
  `is_new` tinyint(1) NOT NULL DEFAULT 0 COMMENT '新客户（0：老，1：新）',
  `external_user_id` varchar(55) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '外部联系人external_userid',
  `room_id` int(255) NULL DEFAULT NULL COMMENT '群聊ID',
  `join_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '入群状态（0：未入群，1：已入群）',
  `write_off` tinyint(1) NOT NULL DEFAULT 0 COMMENT '核销（0：未核销，1：已核销）',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '群裂变-客户参与' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_fission_invite`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fission_id` int(11) NOT NULL DEFAULT 0 COMMENT '活动ID',
  `type` tinyint(1) NOT NULL DEFAULT 2 COMMENT '类型（1：邀请，2：暂不邀请）',
  `employees` json NULL COMMENT '所属员工',
  `choose_contact` json NULL COMMENT '筛选客户条件',
  `text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '邀请文案',
  `link_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '邀请链接标题',
  `link_desc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '邀请链接描述',
  `link_pic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '邀请链接封面图',
  `wx_link_pic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '微信图片地址',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '群裂变-邀请客户参与' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_fission_poster`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fission_id` int(11) NOT NULL DEFAULT 0 COMMENT '活动ID',
  `cover_pic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '海报背景图片',
  `avatar_show` tinyint(1) NULL DEFAULT NULL COMMENT '头像是否显示。0：不显示，1：显示',
  `nickname_show` tinyint(1) NULL DEFAULT NULL COMMENT '昵称是否显示。0：不显示，1：显示',
  `nickname_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '颜色',
  `qrcode_w` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '二维码宽度',
  `qrcode_h` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '二维码高度',
  `qrcode_x` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '二维码X值',
  `qrcode_y` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '二维码Y值',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '群裂变-海报' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_fission_room`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fission_id` int(11) NOT NULL DEFAULT 0 COMMENT '活动ID',
  `room_qrcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '群聊二维码',
  `room_wx_qrcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '群聊二维码微信图片地址',
  `room` json NULL COMMENT '群聊',
  `room_max` int(11) NULL DEFAULT 0 COMMENT '群人数上限',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '群裂变-群聊' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_fission_welcome`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fission_id` int(11) NOT NULL DEFAULT 0 COMMENT '活动ID',
  `text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '文字欢迎语',
  `link_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '链接标题',
  `link_desc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '链接描述',
  `link_pic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '链接封面地址',
  `link_wx_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '微信图片地址',
  `template_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '欢迎语素材id',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '群裂变-欢迎语' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_infinite`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '二维码头像',
  `title_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '群名称设置（0：关闭，1：开启）',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '群名称',
  `describe_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '入群引导语（0：关闭，1：开启）',
  `describe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '入群引导语',
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '头像',
  `qw_code` json NOT NULL COMMENT '企微活码（qrcode，upper_limit，status状态（0：未开始，1：拉人中，2：已停用））',
  `total_num` int(11) NOT NULL DEFAULT 0 COMMENT '扫码人数',
  `tenant_id` int(11) NULL DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) NULL DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '无限拉群-基本信息表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_quality`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `rooms` json NOT NULL COMMENT '群聊',
  `quality_type` tinyint(1) NOT NULL COMMENT '质检时间（1：全天检测，2：自定义质检时间）',
  `work_cycle` json NOT NULL COMMENT '工作周期',
  `rule` json NOT NULL COMMENT '规则',
  `white_list_status` tinyint(1) NOT NULL COMMENT '白名单状态（0：已关闭，1：开启）',
  `keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '关键词',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态（0：关闭，1：开启）',
  `tenant_id` int(11) NULL DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) NULL DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '群聊质检-基本信息表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_quality_record`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `quality_id` int(11) NOT NULL COMMENT '质检id',
  `message_id` int(11) NOT NULL COMMENT '消息id',
  `room_id` int(11) NOT NULL COMMENT '群聊',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '内容',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '群聊质检-提醒记录表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_remind`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `rooms` json NOT NULL COMMENT '群聊',
  `is_qrcode` tinyint(1) NOT NULL COMMENT '发送带二维码图片（0：不提醒，1：提醒）',
  `is_link` tinyint(1) NOT NULL COMMENT '发送链接分享（0：不提醒，1：提醒）',
  `is_miniprogram` tinyint(1) NOT NULL COMMENT '发送小程序（0：不提醒，1：提醒）',
  `is_card` tinyint(1) NOT NULL COMMENT '发送名片（0：不提醒，1：提醒）',
  `is_keyword` tinyint(1) NOT NULL COMMENT '发送关键词（0：不提醒，1：提醒）',
  `keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '关键词',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态（0：关闭，1：开启）',
  `tenant_id` int(11) NULL DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) NULL DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '客户群提醒-基本信息表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_remind_record`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `remind_id` int(11) NOT NULL COMMENT '提醒id',
  `message_id` int(11) NOT NULL COMMENT '消息id',
  `room_id` int(11) NOT NULL COMMENT '群聊',
  `type` tinyint(1) NOT NULL COMMENT '类型（1：二维码，2：链接，3：小程序，4：名片，5：关键词）',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '内容',
  `keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '关键词',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '客户群提醒-提醒记录表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_tag_pull`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '任务名称',
  `employees` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '群发员工',
  `choose_contact` json NULL COMMENT '筛选客户条件',
  `guide` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '入群引导语',
  `rooms` json NULL COMMENT '群聊',
  `filter_contact` tinyint(1) NULL DEFAULT 1 COMMENT '过滤客户（0：否，1：是）',
  `contact_num` int(11) NULL DEFAULT NULL COMMENT '客户数量',
  `wx_tid` json NULL COMMENT '企业群发消息的id',
  `tenant_id` int(11) NULL DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) NULL DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '标签建群-基本信息表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_tag_pull_contact`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_tag_pull_id` int(11) NOT NULL COMMENT '标签建群id(mc_room_tag_pull.id)',
  `contact_id` int(11) NOT NULL COMMENT '客户id',
  `wx_external_userid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '客户wx_external_userid',
  `contact_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '客户名称',
  `employee_id` int(11) NOT NULL COMMENT '员工id',
  `wx_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '员工wx_user_id',
  `send_status` tinyint(1) NULL DEFAULT 0 COMMENT '发送状态：0-未发送 1-已发送 2-因客户不是好友导致发送失败 3-因客户已经收到其他群发消息导致发送失',
  `is_join_room` tinyint(1) NULL DEFAULT 0 COMMENT '是否入群（0：否，1：是）',
  `room_id` int(11) NOT NULL COMMENT '客户群id(mc_work_room.id)',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '标签建群-客户表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_shop_code`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '名称',
  `type` tinyint(1) NOT NULL COMMENT '类型（1：扫码添加店主。2：扫码加入门店群。3：扫码加入城市群）',
  `employee` json NULL COMMENT '店主',
  `employee_qrcode` json NULL COMMENT '店主二维码',
  `qw_code` json NULL COMMENT '拉群活码（mc_work_room_auto_pull）',
  `search_keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '搜索关键词',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '地址',
  `country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '国家',
  `province` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '省',
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '城市',
  `district` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '地区',
  `lat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '地址纬度',
  `lng` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '地址经度',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态（0：关闭，1：开启）',
  `tenant_id` int(11) NULL DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) NULL DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '门店活码-基本信息表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_shop_code_page`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL COMMENT '类型（1：扫码添加店主。2：扫码加入门店群。3：扫码加入城市群）',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '页面标题',
  `show_type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '扫码页面展示（1：默认样式，2：自定义海报）',
  `default` json NOT NULL COMMENT '默认样式（企业介绍，企业logo，扫码引导语，门店地址）',
  `poster` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '自定义海报',
  `autoPass` tinyint(1) NOT NULL DEFAULT 0 COMMENT '好友直接入群（0：关闭，1：开启）',
  `tenant_id` int(11) NULL DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) NULL DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '门店活码-页面设置表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_shop_code_record`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL COMMENT '类型（1：扫码添加店主。2：扫码加入门店群。3：扫码加入城市群）',
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `shop_id` int(11) NULL DEFAULT NULL COMMENT '门店id',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '门店活码-页面点击记录表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_work_fission`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `corp_id` int(11) NOT NULL DEFAULT 0 COMMENT '企业表ID（mc_crop.id）',
  `active_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '活动名称',
  `service_employees` json NULL COMMENT '客服成员',
  `auto_pass` tinyint(1) NULL DEFAULT NULL COMMENT '自动通过好友申请',
  `auto_add_tag` tinyint(1) NULL DEFAULT NULL COMMENT '自动添加客户标签',
  `contact_tags` json NULL COMMENT '标签组',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '活动结束时间',
  `qr_code_invalid` int(11) NULL DEFAULT NULL COMMENT '二维码有效期（天）为空则是立即失效',
  `tasks` json NULL COMMENT '裂变任务',
  `new_friend` tinyint(1) NULL DEFAULT NULL COMMENT '必须新好友才能助力',
  `delete_invalid` tinyint(1) NULL DEFAULT NULL COMMENT '删除员工后助力失效',
  `receive_prize` tinyint(1) NULL DEFAULT NULL COMMENT '领奖方式：0联系客服，1兑换链接',
  `receive_prize_employees` json NULL COMMENT '领奖-员工',
  `receive_links` json NULL COMMENT '领奖-兑换链接',
  `receive_qrcode` json NULL COMMENT '领奖-员工二维码',
  `create_user_id` int(11) NOT NULL DEFAULT 0 COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '裂变-基础信息主表' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_work_fission_contact`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fission_id` int(11) NOT NULL DEFAULT 0 COMMENT '活动ID',
  `union_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '客户微信id',
  `nickname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '客户微信昵称',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '客户微信头像',
  `contact_superior_user_parent` int(11) NULL DEFAULT 0 COMMENT '上级（被谁邀请来的）',
  `level` tinyint(1) NULL DEFAULT 0 COMMENT '裂变等级',
  `employee` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '添加的员工',
  `invite_count` int(11) NOT NULL DEFAULT 0 COMMENT '邀请数量',
  `loss` tinyint(1) NULL DEFAULT 0 COMMENT '是否已流失（被删除好友）',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '完成状态。（0：未完成，1：已完成）',
  `receive_level` tinyint(1) NULL DEFAULT 0 COMMENT '已领取奖励阶段',
  `is_new` tinyint(1) NOT NULL DEFAULT 0 COMMENT '新客户（0：老，1：新）',
  `external_user_id` varchar(55) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '外部联系人external_userid',
  `qrcode_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '二维码ID',
  `qrcode_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '二维码图片链接',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '裂变-客户参与' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_work_fission_invite`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fission_id` int(11) NOT NULL DEFAULT 0 COMMENT '活动ID',
  `type` tinyint(1) NULL DEFAULT 2 COMMENT '类型（1：邀请，2：暂不邀请）',
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '邀请文案',
  `link_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '邀请链接标题',
  `link_desc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '邀请链接描述',
  `link_pic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '邀请链接封面图',
  `wx_link_pic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '微信图片地址',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '裂变-邀请客户参与' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_work_fission_poster`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fission_id` int(11) NOT NULL DEFAULT 0 COMMENT '活动ID',
  `poster_type` tinyint(1) NULL DEFAULT NULL COMMENT '裂变海报：0海报,1个人名片',
  `cover_pic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '海报背景图片',
  `wx_cover_pic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '背景图片微信地址',
  `foward_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '海报转发话术',
  `avatar_show` tinyint(1) NULL DEFAULT NULL COMMENT '头像是否显示。0：不显示，1：显示',
  `nickname_show` tinyint(1) NULL DEFAULT NULL COMMENT '昵称是否显示。0：不显示，1：显示',
  `nickname_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '颜色',
  `card_corp_image_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '个人名片企业形象名称',
  `card_corp_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '个人名片企业名称',
  `card_corp_logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '个人名片企业logo',
  `qrcode_w` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '二维码宽度',
  `qrcode_h` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '二维码高度',
  `qrcode_x` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '二维码X值',
  `qrcode_y` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '二维码Y值',
  `qrcode_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '二维码ID',
  `qrcode_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '二维码图片链接',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '裂变-海报' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_work_fission_push`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fission_id` int(11) NOT NULL DEFAULT 0 COMMENT '活动ID',
  `push_employee` tinyint(255) NULL DEFAULT NULL COMMENT '员工推送',
  `push_contact` tinyint(255) NULL DEFAULT NULL COMMENT '客户推送',
  `msg_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '消息1（文字）',
  `msg_complex` json NULL COMMENT '消息2（图片、链接、小程序）',
  `msg_complex_type` varchar(55) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '消息2类型（image|link|applets）',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '裂变-推送' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_work_fission_welcome`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fission_id` int(11) NOT NULL DEFAULT 0 COMMENT '活动ID',
  `msg_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '文字欢迎语',
  `link_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '链接标题',
  `link_desc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '链接描述',
  `link_cover_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '链接封面地址',
  `link_wx_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '微信图片地址',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '裂变-欢迎语' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_work_message_id`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `corp_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '企业表ID （mc_corp.id）',
  `type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '类型（1：群消息提醒）',
  `last_id` int(11) NOT NULL DEFAULT 0 COMMENT '最后一次查询最大id',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会话内容查询记录' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_contact_sop`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `creator_id` int(11) NULL DEFAULT NULL COMMENT '创建人id',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '规则名称',
  `setting` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '推送内容（json）',
  `employee_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '客服成员id（json）',
  `state` tinyint(1) NULL DEFAULT NULL COMMENT '开关：0关 1开',
  `contact_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '触发的客户id（json）',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '个人SOP记录表' ;

CREATE TABLE IF NOT EXISTS `mc_contact_sop_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `corp_id` int(11) NULL DEFAULT NULL COMMENT 'work_corp.id',
  `contact_sop_id` int(11) NULL DEFAULT NULL COMMENT 'work_sop_personal.id',
  `employee` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '员工wxid',
  `contact` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '客户wxid',
  `task` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '触发的规则json',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_sop`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id',
  `creator_id` int(11) NULL DEFAULT NULL COMMENT '创建人id',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '规则名称',
  `setting` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '推送内容（json）',
  `room_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '群聊id（json）',
  `state` tinyint(1) NULL DEFAULT NULL COMMENT '开关：0关 1开',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_room_sop_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `corp_id` int(11) NULL DEFAULT NULL COMMENT 'work_corp.id',
  `room_sop_id` int(11) NULL DEFAULT NULL COMMENT 'work_sop_room.id',
  `room_id` int(11) NULL DEFAULT NULL COMMENT 'work_room.id',
  `state` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已完成：0否，1是',
  `employee` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '客服wxid',
  `contact` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '客户wxid',
  `task` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '触发的规则json',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `mc_work_transfer_log`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `corp_id` int(11) NULL DEFAULT NULL COMMENT '企业id (corp.id)',
  `status` tinyint(1) NULL DEFAULT NULL COMMENT '客服类型：1离职分配 2在职分配',
  `type` tinyint(1) NULL DEFAULT NULL COMMENT '分配类型：1客户转接 2群聊转接',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '客户/群聊 名称',
  `contact_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '客户/群聊 WxId',
  `handover_employee_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '原客服的WxId',
  `takeover_employee_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '接替的客服的WxId',
  `state` tinyint(1) NULL DEFAULT NULL COMMENT '转接状态：1接替完毕 2等待接替 3客户拒绝 4接替成员客户达到上限 5无接替记录',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '客户/群聊 分配记录表' ;

CREATE TABLE IF NOT EXISTS `mc_room_welcome_template` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `corp_id` varchar(11) NOT NULL DEFAULT '0' COMMENT '企业表ID（mc_crop.id）',
  `msg_text` varchar(255) DEFAULT NULL COMMENT '欢迎语1（文字）',
  `complex_type` varchar(50) DEFAULT NULL COMMENT '欢迎语2类型',
  `msg_complex` json DEFAULT NULL COMMENT '欢迎语2（图片、链接、小程序）',
  `complex_template_id` varchar(55) NOT NULL COMMENT '欢迎语2素材id',
  `create_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci  COMMENT='入群欢迎语表';

CREATE TABLE IF NOT EXISTS `mc_work_unassigned` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `corp_id` int(11) NOT NULL COMMENT '企业id(corp.id)',
  `handover_userid` varchar(100) NOT NULL COMMENT '离职成员的userid',
  `external_userid` varchar(255) NOT NULL COMMENT '外部联系人userid',
  `dimission_time` int(10) NOT NULL COMMENT '成员离职时间',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE = utf8mb4_unicode_ci  ROW_FORMAT=DYNAMIC COMMENT='离职成员-客户存储表';

ALTER TABLE `mc_work_contact`
MODIFY COLUMN `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人姓名' AFTER `wx_external_userid`;

ALTER TABLE `mc_work_contact_employee`
MODIFY COLUMN `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '员工对此外部联系人的备注' AFTER `contact_id`;

ALTER TABLE `mc_work_employee`
ADD COLUMN `audit_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '存档状态（0：未开通，1：已开通）' AFTER `contact_auth`;


ALTER TABLE `mc_work_message_1`
ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '关键词打标签查询状态（0：未查询，1：已查询）' AFTER `room_id`;

ALTER TABLE `mc_work_message_2`
ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '关键词打标签查询状态（0：未查询，1：已查询）' AFTER `room_id`;

ALTER TABLE `mc_work_message_3`
ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '关键词打标签查询状态（0：未查询，1：已查询）' AFTER `room_id`;

ALTER TABLE `mc_work_message_4`
ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '关键词打标签查询状态（0：未查询，1：已查询）' AFTER `room_id`;

ALTER TABLE `mc_work_message_5`
ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '关键词打标签查询状态（0：未查询，1：已查询）' AFTER `room_id`;

ALTER TABLE `mc_work_message_6`
ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '关键词打标签查询状态（0：未查询，1：已查询）' AFTER `room_id`;

ALTER TABLE `mc_work_message_7`
ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '关键词打标签查询状态（0：未查询，1：已查询）' AFTER `room_id`;

ALTER TABLE `mc_work_message_8`
ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '关键词打标签查询状态（0：未查询，1：已查询）' AFTER `room_id`;

ALTER TABLE `mc_work_message_9`
ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '关键词打标签查询状态（0：未查询，1：已查询）' AFTER `room_id`;

ALTER TABLE `mc_work_message_10`
ADD COLUMN `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '关键词打标签查询状态（0：未查询，1：已查询）' AFTER `room_id`;

TRUNCATE TABLE `mc_rbac_menu`;

INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1','0','企微管理','1','#1#','','1','1','1','/dashboard_baseSysManager','2','0','系统','99','2020-12-31 19:22:04','2021-08-09 17:00:02',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('2','1','引流获客','2','#1#-#2#','line-chart','1','1','1','/dashboard_baseShunt','2','0','系统','2','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('3','2','渠道活码','3','#1#-#2#-#3#','','1','1','1','/dashboard/channelCode/index','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('4','3','新建渠道码','4','#1#-#2#-#3#-#4#','','1','1','1','/dashboard/channelCode/store','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('5','4','确定(按钮)','5','#1#-#2#-#3#-#4#-#5#','','1','1','1','/dashboard/channelCode/store@confirm','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('6','3','统计(按钮)','4','#1#-#2#-#3#-#6#','','1','1','1','/dashboard/channelCode/statistics','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('7','3','查询(按钮)','4','#1#-#2#-#3#-#7#','','1','1','1','/dashboard/channelCode/index@search','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('8','3','修改分组(按钮)','4','#1#-#2#-#3#-#8#','','1','1','1','/dashboard/channelCode/index@editGroup','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('9','3','新建分组(按钮)','4','#1#-#2#-#3#-#9#','','1','1','1','/dashboard/channelCode/index@add','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('10','3','编辑(按钮)','4','#1#-#2#-#3#-#10#','','1','1','1','/dashboard/channelCode/index@edit','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('11','3','客户(按钮)','4','#1#-#2#-#3#-#11#','','1','1','1','/dashboard/channelCode/index@customer','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('12','3','下载(按钮)','4','#1#-#2#-#3#-#12#','','1','1','1','/dashboard/channelCode/index@download','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('13','3','移动(按钮)','4','#1#-#2#-#3#-#13#','','1','1','1','/dashboard/channelCode/index@move','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('14','1','客户转化','2','#1#-#14#','pie-chart','1','1','1','/dashboard/contact/transfer','2','0','系统','4','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('15','14','好友欢迎语','3','#1#-#14#-#15#','','1','1','1','/dashboard/greeting/index','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('16','15','新建欢迎语','4','#1#-#14#-#15#-#16#','','1','1','1','/dashboard/greeting/store','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('17','16','创建欢迎语(按钮)','5','#1#-#14#-#15#-#16#-#17#','','1','1','1','/dashboard/greeting/store@add','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('18','15','编辑(按钮)','4','#1#-#14#-#15#-#18#','','1','1','1','/dashboard/greeting/index@edit','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('19','15','删除(按钮)','4','#1#-#14#-#15#-#19#','','1','1','1','/dashboard/greeting/index@delete','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('20','14','素材库','3','#1#-#14#-#20#','','1','1','1','/dashboard/mediumGroup/index','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('21','20','修改分组(按钮)','4','#1#-#14#-#20#-#21#','','1','1','1','/dashboard/mediumGroup/index@editGroup','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('22','20','新增分组(按钮)','4','#1#-#14#-#20#-#22#','','1','1','1','/dashboard/mediumGroup/index@addGroup','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('23','20','编辑(按钮)','4','#1#-#14#-#20#-#23#','','1','1','1','/dashboard/mediumGroup/index@edit','2','0','系统','99','2020-12-31 19:22:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('24','20','移动(按钮)','4','#1#-#14#-#20#-#24#','','1','1','1','/dashboard/mediumGroup/index@move','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('25','20','删除(按钮)','4','#1#-#14#-#20#-#25#','','1','1','1','/dashboard/mediumGroup/index@delete','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('26','20','添加(按钮)','4','#1#-#14#-#20#-#26#','','1','1','1','/dashboard/mediumGroup/index@add','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('27','1','客户管理','2','#1#-#27#','solution','1','1','1','/dashboard_baseContactManage','2','0','系统','5','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('28','27','客户列表','3','#1#-#27#-#28#','','1','1','1','/dashboard/workContact/index','2','0','系统','1','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('29','28','客户详情','4','#1#-#27#-#28#-#29#','','1','1','1','/dashboard/workContact/contactFieldPivot','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('30','29','互动轨迹(按钮)','5','#1#-#27#-#28#-#29#-#30#','','1','1','1','/dashboard/workContact/contactFieldPivot@track','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('31','29','用户画像(按钮)','5','#1#-#27#-#28#-#29#-#31#','','1','1','1','/dashboard/workContact/contactFieldPivot@detail','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('32','29','编辑(按钮)','5','#1#-#27#-#28#-#29#-#32#','','1','1','1','/dashboard/workContact/contactFieldPivot@edit','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('33','29','编辑基础信息(按钮)','5','#1#-#27#-#28#-#29#-#33#','','1','1','1','/dashboard/workContact/contactFieldPivot@update','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('34','28','查询(按钮)','4','#1#-#27#-#28#-#34#','','1','1','1','/dashboard/workContact/index@search','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('35','28','同步客户(按钮)','4','#1#-#27#-#28#-#35#','','1','1','1','/dashboard/workContact/index@sync','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('36','27','客户资料卡','3','#1#-#27#-#36#','','1','1','1','/dashboard/contactField/index','2','0','系统','3','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('37','36','高级属性(按钮)','4','#1#-#27#-#36#-#37#','','1','1','1','/dashboard/contactField/index@advanced','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('38','36','全部状态(按钮)','4','#1#-#27#-#36#-#38#','','1','1','1','/dashboard/contactField/index@all','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('39','36','新增属性(按钮)','4','#1#-#27#-#36#-#39#','','1','1','1','/dashboard/contactField/index@add','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('40','36','批量修改(按钮)','4','#1#-#27#-#36#-#40#','','1','1','1','/dashboard/contactField/index@batch','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('41','36','编辑(按钮)','4','#1#-#27#-#36#-#41#','','1','1','1','/dashboard/contactField/index@edit','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('42','36','关闭(按钮)','4','#1#-#27#-#36#-#42#','','1','1','1','/dashboard/contactField/index@close','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('43','27','流失提醒','3','#1#-#27#-#43#','','1','1','1','/dashboard/lossContact/index','2','0','系统','4','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('44','43','选择部门成员(按钮)','4','#1#-#27#-#43#-#44#','','1','1','1','/dashboard/lossContact/index@choose','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('45','27','客户标签','3','#1#-#27#-#45#','','1','1','1','/dashboard/workContactTag/index','2','0','系统','2','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('46','45','同步企业微信通讯录(按钮)','4','#1#-#27#-#45#-#46#','','1','1','1','/dashboard/workContactTag/index@sync','2','0','系统','99','2020-12-31 19:22:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('47','45','修改分组(按钮)','4','#1#-#27#-#45#-#47#','','1','1','1','/dashboard/workContactTag/index@editGroup','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('48','45','新增分组(按钮)','4','#1#-#27#-#45#-#48#','','1','1','1','/dashboard/workContactTag/index@addGroup','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('49','45','删除标签(按钮)','4','#1#-#27#-#45#-#49#','','1','1','1','/dashboard/workContactTag/index@deleteTag','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('50','45','新建标签(按钮)','4','#1#-#27#-#45#-#50#','','1','1','1','/dashboard/workContactTag/index@add','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('51','45','编辑(按钮)','4','#1#-#27#-#45#-#51#','','1','1','1','/dashboard/workContactTag/index@edit','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('52','45','删除(按钮)','4','#1#-#27#-#45#-#52#','','1','1','1','/dashboard/workContactTag/index@delete','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('53','45','移动标签(按钮)','4','#1#-#27#-#45#-#53#','','1','1','1','/dashboard/workContactTag/index@move','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('54','1','客户群运营','2','#1#-#54#','team','1','1','1','/dashboard_baseContactRoomManage','2','0','系统','7','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('55','54','客户群列表','3','#1#-#54#-#55#','','1','1','1','/dashboard/workRoom/index','2','0','系统','1','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('56','55','群统计','4','#1#-#54#-#55#-#56#','','1','1','1','/dashboard/workRoom/statistics','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('57','55','增加分组(按钮)','4','#1#-#54#-#55#-#57#','','1','1','1','/dashboard/workRoom/index@add','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('58','55','编辑分组(按钮)','4','#1#-#54#-#55#-#58#','','1','1','1','/dashboard/workRoom/index@edit','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('59','55','查找(按钮)','4','#1#-#54#-#55#-#59#','','1','1','1','/dashboard/workRoom/index@search','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('60','55','批量修改分组(按钮)','4','#1#-#54#-#55#-#60#','','1','1','1','/dashboard/workRoom/index@batch','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('61','55','同步群(按钮)','4','#1#-#54#-#55#-#61#','','1','1','1','/dashboard/workRoom/index@sync','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('62','55','群成员(按钮)','4','#1#-#54#-#55#-#62#','','1','1','1','/dashboard/workRoom/index@member','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('63','55','移动分组(按钮)','4','#1#-#54#-#55#-#63#','','1','1','1','/dashboard/workRoom/index@move','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('64','54','自动拉群','3','#1#-#54#-#64#','','1','1','1','/dashboard/workRoomAutoPull/index','2','0','系统','5','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('65','64','新建拉群','4','#1#-#54#-#64#-#65#','','1','1','1','/dashboard/workRoomAutoPull/store','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('66','65','提交(按钮)','5','#1#-#54#-#64#-#65#-#66#','','1','1','1','/dashboard/workRoomAutoPull/store@submit','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('67','64','查找(按钮)','4','#1#-#54#-#64#-#67#','','1','1','1','/dashboard/workRoomAutoPull/index@search','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('68','64','详情(按钮)','4','#1#-#54#-#64#-#68#','','1','1','1','/dashboard/workRoomAutoPull/index@detail','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('69','64','编辑(按钮)','4','#1#-#54#-#64#-#69#','','1','1','1','/dashboard/workRoomAutoPull/index@edit','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('70','64','下载(按钮)','4','#1#-#54#-#64#-#70#','','1','1','1','/dashboard/workRoomAutoPull/index@download','2','0','系统','99','2020-12-31 19:22:07','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('71','1','聊天侧边栏','2','#1#-#71#','message','1','1','1','/dashboard_chatTool','2','0','系统','8','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('72','71','聊天侧边栏(废弃)','3','#1#-#71#-#72#','','1','1','1','/dashboard/chatTool/config','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('73','72','上传文件(按钮)(废弃)','4','#1#-#71#-#72#-#73#','','1','1','1','/dashboard/chatTool/config@upload','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('74','1','企业风控','2','#1#-#74#','radar-chart','1','1','1','/dashboard_baseRiskControl','2','0','系统','9','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('75','74','消息存档','3','#1#-#74#-#75#','','1','1','1','/dashboard/workMessage/index','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('76','75','聊天记录查看','4','#1#-#74#-#75#-#76#','','1','1','1','/dashboard/workMessage/toUsers','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('77','74','敏感词词库','3','#1#-#74#-#77#','','1','1','1','/dashboard/sensitiveWords/index','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('78','77','查询(按钮)','4','#1#-#74#-#77#-#78#','','1','1','1','/dashboard/sensitiveWords/index@search','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('79','77','修改分组(按钮)','4','#1#-#74#-#77#-#79#','','1','1','1','/dashboard/sensitiveWords/index@edit','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('80','77','新建分组(按钮)','4','#1#-#74#-#77#-#80#','','1','1','1','/dashboard/sensitiveWords/index@add','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('81','77','新建敏感词(按钮)','4','#1#-#74#-#77#-#81#','','1','1','1','/dashboard/sensitiveWords/index@addWord','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('82','77','关闭(按钮)','4','#1#-#74#-#77#-#82#','','1','1','1','/dashboard/sensitiveWords/index@close','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('83','77','删除(按钮)','4','#1#-#74#-#77#-#83#','','1','1','1','/dashboard/sensitiveWords/index@delete','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('84','77','移动(按钮)','4','#1#-#74#-#77#-#84#','','1','1','1','/dashboard/sensitiveWords/index@move','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('85','74','敏感词监控','3','#1#-#74#-#85#','','1','1','1','/dashboard/sensitiveWordsMonitor/index','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('86','85','查询(按钮)','4','#1#-#74#-#85#-#86#','','1','1','1','/dashboard/sensitiveWordsMonitor/index@search','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('87','85','对话详情(按钮)','4','#1#-#74#-#85#-#87#','','1','1','1','/dashboard/sensitiveWordsMonitor/index@detail','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('88','74','消息存档配置','3','#1#-#74#-#88#','','1','1','1','/dashboard/workMessageConfig/corpShow','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('89','88','查找(按钮)','4','#1#-#74#-#88#-#89#','','1','1','1','/dashboard/workMessageConfig/corpShow@search','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('90','88','查看(按钮)','4','#1#-#74#-#88#-#90#','','1','1','1','/dashboard/workMessageConfig/corpShow@check','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('91','88','列表操作','4','#1#-#74#-#88#-#91#','','1','1','1','/dashboard/workMessageConfig/corpIndex#get','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('92','88','查看操作','4','#1#-#74#-#88#-#92#','','1','1','1','/dashboard/workMessageConfig/corpShow#get','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('93','88','保存操作','4','#1#-#74#-#88#-#93#','','1','1','1','/dashboard/workMessageConfig/corpStore#post','2','0','系统','99','2020-12-31 19:22:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('94','0','系统设置','1','#94#','','1','1','1','/dashboard_baseSysIndex','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('95','94','企业管理','2','#94#-#95#','cluster','1','1','1','/dashboard_baseSysConfig','2','0','系统','1','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('96','95','成员管理','3','#94#-#95#-#96#','','1','1','1','/dashboard/workEmployee/index','2','0','系统','1','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('97','96','条件筛选(按钮)','4','#94#-#95#-#96#-#97#','','1','1','1','/dashboard/workEmployee/index@search','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('98','96','同步企业微信通讯录(按钮)','4','#94#-#95#-#96#-#98#','','1','1','1','/dashboard/workEmployee/index@sync','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('99','95','账号管理','3','#94#-#95#-#99#','','1','1','1','/dashboard/user/index','2','0','系统','2','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('100','99','查询(按钮)','4','#94#-#95#-#99#-#100#','','1','1','1','/dashboard/user/index@search','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('101','99','添加(按钮)','4','#94#-#95#-#99#-#101#','','1','1','1','/dashboard/user/index@add','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('102','99','修改(按钮)','4','#94#-#95#-#99#-#102#','','1','1','1','/dashboard/user/index@edit','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('103','95','修改密码','3','#94#-#95#-#103#','','1','1','1','/dashboard/passwordUpdate/index','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('104','103','保存(按钮)','4','#94#-#95#-#103#-#104#','','1','1','1','/dashboard/passwordUpdate/index@save','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('105','95','授权管理','3','#94#-#95#-#105#','','1','1','1','/dashboard/corp/index','2','0','系统','3','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('106','105','查找(按钮)','4','#94#-#95#-#105#-#106#','','1','1','1','/dashboard/corp/index@search','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('107','105','添加企业微信号(按钮)','4','#94#-#95#-#105#-#107#','','1','1','1','/dashboard/corp/index@addwx','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('108','105','查看(按钮)','4','#94#-#95#-#105#-#108#','','1','1','1','/dashboard/corp/index@check','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('109','105','修改(按钮)','4','#94#-#95#-#105#-#109#','','1','1','1','/dashboard/corp/index@edit','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('110','95','角色管理','3','#94#-#95#-#110#','','1','1','1','/dashboard/role/index','2','0','系统','4','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('111','110','权限设置','4','#94#-#95#-#110#-#111#','','1','1','1','/dashboard/role/permissionShow','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('112','111','保存权限(按钮)','5','#94#-#95#-#110#-#111#-#112#','','1','1','1','/dashboard/role/permissionShow@save','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('113','110','查询(按钮)','4','#94#-#95#-#110#-#113#','','1','1','1','/dashboard/role/index@search','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('114','110','添加(按钮)','4','#94#-#95#-#110#-#114#','','1','1','1','/dashboard/role/index@add','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('115','110','编辑(按钮)','4','#94#-#95#-#110#-#115#','','1','1','1','/dashboard/role/index@edit','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('116','110','复制权限(按钮)','4','#94#-#95#-#110#-#116#','','1','1','1','/dashboard/role/index@copy','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('117','110','删除(按钮)','4','#94#-#95#-#110#-#117#','','1','1','1','/dashboard/role/index@delete','2','0','系统','99','2020-12-31 19:22:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('118','110','查看角色人员(按钮)','4','#94#-#95#-#110#-#118#','','1','1','1','/dashboard/role/index@checkMember','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('119','110','启动(按钮)','4','#94#-#95#-#110#-#119#','','1','1','1','/dashboard/role/index@use','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('120','95','菜单管理','3','#94#-#95#-#120#','','1','1','1','/dashboard/menu/index','2','0','系统','5','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('121','120','查询(按钮)','4','#94#-#95#-#120#-#121#','','1','1','1','/dashboard/menu/index@search','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('122','120','添加(按钮)','4','#94#-#95#-#120#-#122#','','1','1','1','/dashboard/menu/index@add','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('123','120','编辑(按钮)','4','#94#-#95#-#120#-#123#','','1','1','1','/dashboard/menu/index@edit','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('124','95','组织架构','3','#94#-#95#-#124#','','1','1','1','/dashboard/department/index','2','0','系统','6','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('125','124','查询(按钮)','4','#94#-#95#-#124#-#125#','','1','1','1','/dashboard/department/index@search','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('126','124','查看成员(按钮)','4','#94#-#95#-#124#-#126#','','1','1','1','/dashboard/department/index@check','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('127','124','同步企业微信通讯录(按钮)','4','#94#-#95#-#124#-#127#','','1','1','1','/dashboard/department/index@sync','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('128','20','素材列表操作','4','#1#-#14#-#20#-#128#','','1','1','2','/dashboard/medium/index#get','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('129','20','素材详情操作','4','#1#-#14#-#20#-#129#','','1','1','2','/dashboard/medium/show#get','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('130','20','素材添加操作','4','#1#-#14#-#20#-#130#','','1','1','2','/dashboard/medium/store#post','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('131','20','素材修改操作','4','#1#-#14#-#20#-#131#','','1','1','2','/dashboard/medium/update#put','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('132','20','素材删除操作','4','#1#-#14#-#20#-#132#','','1','1','2','/dashboard/medium/destroy#delete','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('133','20','素材分组详情操作','4','#1#-#14#-#20#-#133#','','1','1','2','/dashboard/mediumGroup/show#get','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('134','20','素材分组列表操作','4','#1#-#14#-#20#-#134#','','1','1','2','/dashboard/mediumGroup/index#get','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('135','20','素材分组删除操作','4','#1#-#14#-#20#-#135#','','1','1','2','/dashboard/mediumGroup/destroy#delete','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('136','20','素材分组添加操作','4','#1#-#14#-#20#-#136#','','1','1','2','/dashboard/mediumGroup/store#post','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('137','20','素材移动分组操作','4','#1#-#14#-#20#-#137#','','1','1','2','/dashboard/medium/groupUpdate#put','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('138','20','素材分组修改操作','4','#1#-#14#-#20#-#138#','','1','1','2','/dashboard/mediumGroup/update#put','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('139','76','聊天内容列表操作','5','#1#-#74#-#75#-#76#-#139#','','1','1','2','/dashboard/workMessage/toUsers#get','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('140','76','聊天内容详情操作','5','#1#-#74#-#75#-#76#-#140#','','1','1','2','/dashboard/workMessage/index#get','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('141','75','聊天配置详情操作','4','#1#-#74#-#75#-#141#','','1','1','2','/dashboard/workMessageConfig/stepCreate#get','2','0','系统','99','2020-12-31 19:22:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('142','75','聊天配置编辑操作','4','#1#-#74#-#75#-#142#','','1','1','2','/dashboard/workMessageConfig/stepUpdate#put','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('143','105','企业授信列表操作','4','#94#-#95#-#105#-#143#','','1','1','2','/dashboard/corp/index#get','1','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('144','105','新建企业微信授信操作','4','#94#-#95#-#105#-#144#','','1','1','2','/dashboard/corp/store#post','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('145','105','查看企业授信操作','4','#94#-#95#-#105#-#145#','','1','1','2','/dashboard/corp/show#get','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('146','105','更新企业微信授信操作','4','#94#-#95#-#105#-#146#','','1','1','2','/dashboard/corp/update#put','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('147','99','子账户列表操作','4','#94#-#95#-#99#-#147#','','1','1','2','/dashboard/user/index#get','1','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('148','99','子账户详情操作','4','#94#-#95#-#99#-#148#','','1','1','2','/dashboard/user/show#get','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('149','99','新建子账户操作','4','#94#-#95#-#99#-#149#','','1','1','2','/dashboard/user/store#post','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('150','99','更新子账户操作','4','#94#-#95#-#99#-#150#','','1','1','2','/dashboard/user/update#put','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('151','56','统计分页操作','5','#1#-#54#-#55#-#56#-#151#','','1','1','2','/dashboard/workRoom/statisticsIndex#get','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('152','56','统计折线图操作','5','#1#-#54#-#55#-#56#-#152#','','1','1','2','/dashboard/workRoom/statistics#get','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('153','55','客户群列表操作','4','#1#-#54#-#55#-#153#','','1','1','2','/dashboard/workRoom/index#get','1','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('154','55','同步群操作','4','#1#-#54#-#55#-#154#','','1','1','2','/dashboard/workRoom/syn#put','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('155','55','群成员操作','4','#1#-#54#-#55#-#155#','','1','1','2','/dashboard/workContactRoom/index#get','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('156','55','批量修改分组操作','4','#1#-#54#-#55#-#156#','','1','1','2','/dashboard/workRoom/batchUpdate#put','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('157','65','新建自动拉群操作','5','#1#-#54#-#64#-#65#-#157#','','1','1','2','/dashboard/workRoomAutoPull/store#post','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('158','64','自动拉群列表操作','4','#1#-#54#-#64#-#158#','','1','1','2','/dashboard/workRoomAutoPull/index#get','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('159','64','自动拉群详情操作','4','#1#-#54#-#64#-#159#','','1','1','2','/dashboard/workRoomAutoPull/show#get','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('160','64','更新自动拉群操作','4','#1#-#54#-#64#-#160#','','1','1','2','/dashboard/workRoomAutoPull/update#put','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('161','85','敏感词监控列表操作','4','#1#-#74#-#85#-#161#','','1','1','2','/dashboard/sensitiveWordsMonitor/index#get','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('162','85','敏感词监控详情操作','4','#1#-#74#-#85#-#162#','','1','1','2','/dashboard/sensitiveWordsMonitor/show#get','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('163','15','欢迎语列表操作','4','#1#-#14#-#15#-#163#','','1','1','2','/dashboard/greeting/index#get','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('164','15','欢迎语详情操作','4','#1#-#14#-#15#-#164#','','1','1','2','/dashboard/greeting/show#get','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('165','15','更新欢迎语操作','4','#1#-#14#-#15#-#165#','','1','1','2','/dashboard/greeting/update#put','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('166','15','删除欢迎语操作','4','#1#-#14#-#15#-#166#','','1','1','2','/dashboard/greeting/destroy#delete','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('167','16','创建欢迎语操作','5','#1#-#14#-#15#-#16#-#167#','','1','1','2','/dashboard/greeting/store#post','2','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('168','28','客户列表操作','4','#1#-#27#-#28#-#168#','','1','1','2','/dashboard/workContact/index#get','1','0','系统','99','2020-12-31 19:22:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('169','28','同步客户操作','4','#1#-#27#-#28#-#169#','','1','1','2','/dashboard/workContact/synContact#put','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('170','29','查看详情操作','5','#1#-#27#-#28#-#29#-#170#','','1','1','2','/dashboard/workContact/show#get','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('171','29','编辑基础信息操作','5','#1#-#27#-#28#-#29#-#171#','','1','1','2','/dashboard/workContact/update#put','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('172','29','互动轨迹操作','5','#1#-#27#-#28#-#29#-#172#','','1','1','2','/dashboard/workContact/track#get','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('173','29','用户画像操作','5','#1#-#27#-#28#-#29#-#173#','','1','1','2','/dashboard/contactFieldPivot/index#get','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('174','29','编辑用户画像操作','5','#1#-#27#-#28#-#29#-#174#','','1','1','2','/dashboard/contactFieldPivot/update#put','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('175','36','高级属性列表操作','4','#1#-#27#-#36#-#175#','','1','1','2','/dashboard/contactField/index#get','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('176','36','新增属性操作','4','#1#-#27#-#36#-#176#','','1','1','2','/dashboard/contactField/store#post','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('177','36','批量修改操作','4','#1#-#27#-#36#-#177#','','1','1','2','/dashboard/contactField/batchUpdate#put','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('178','36','编辑操作','4','#1#-#27#-#36#-#178#','','1','1','2','/dashboard/contactField/update#put','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('179','36','删除操作','4','#1#-#27#-#36#-#179#','','1','1','2','/dashboard/contactField/destroy#delete','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('180','36','关闭操作','4','#1#-#27#-#36#-#180#','','1','1','2','/dashboard/contactField/statusUpdate#put','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('181','43','流失客户操作','4','#1#-#27#-#43#-#181#','','1','1','2','/dashboard/workContact/lossContact#get','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('182','45','同步企业微信标签操作','4','#1#-#27#-#45#-#182#','','1','1','2','/dashboard/workContactTag/synContactTag#put','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('183','45','标签操作','4','#1#-#27#-#45#-#183#','','1','1','2','/dashboard/workContactTag/destroy#delete','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('184','3','渠道码列表操作','4','#1#-#2#-#3#-#184#','','1','1','2','/dashboard/channelCode/index#get','1','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('185','3','渠道码详情操作','4','#1#-#2#-#3#-#185#','','1','1','2','/dashboard/channelCode/show#get','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('186','3','编辑渠道码操作','4','#1#-#2#-#3#-#186#','','1','1','2','/dashboard/channelCode/update#put','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('187','3','客户操作','4','#1#-#2#-#3#-#187#','','1','1','2','/dashboard/channelCode/contact#get','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('188','4','新建渠道码操作','5','#1#-#2#-#3#-#4#-#188#','','1','1','2','/dashboard/channelCode/store#post','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('189','6','统计分页数据操作','5','#1#-#2#-#3#-#6#-#189#','','1','1','2','/dashboard/channelCode/statisticsIndex#get','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('190','6','统计折线图操作','5','#1#-#2#-#3#-#6#-#190#','','1','1','2','/dashboard/channelCode/statistics#get','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('191','124','部门列表操作','4','#94#-#95#-#124#-#191#','','1','1','1','/dashboard/workDepartment/pageIndex#get','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('192','124','查看成员操作','4','#94#-#95#-#124#-#192#','','1','1','1','/dashboard/workDepartment/showEmployee#get','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('193','110','查看人员操作','4','#94#-#95#-#110#-#193#','','1','1','1','/dashboard/role/showEmployee#get','2','0','系统','99','2020-12-31 19:22:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('194','110','角色状态修改操作','4','#94#-#95#-#110#-#194#','','1','1','1','/dashboard/role/statusUpdate#put','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('195','110','角色列表操作','4','#94#-#95#-#110#-#195#','','1','1','1','/dashboard/role/index#get','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('196','110','角色添加操作','4','#94#-#95#-#110#-#196#','','1','1','1','/dashboard/role/store#post','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('197','110','角色编辑操作','4','#94#-#95#-#110#-#197#','','1','1','1','/dashboard/role/update#put','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('198','110','角色详情操作','4','#94#-#95#-#110#-#198#','','1','1','1','/dashboard/role/show#get','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('199','110','角色删除操作','4','#94#-#95#-#110#-#199#','','1','1','1','/dashboard/role/destroy#delete','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('200','111','个人菜单列表操作','5','#94#-#95#-#110#-#111#-#200#','','1','1','1','/dashboard/role/permissionByUser#get','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('201','111','权限查看操作','5','#94#-#95#-#110#-#111#-#201#','','1','1','1','/dashboard/role/permissionShow#get','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('202','111','权限保存操作','5','#94#-#95#-#110#-#111#-#202#','','1','1','1','/dashboard/role/permissionStore#post','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('203','120','菜单列表操作','4','#94#-#95#-#120#-#203#','','1','1','1','/dashboard/menu/index#get','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('204','120','菜单添加操作','4','#94#-#95#-#120#-#204#','','1','1','1','/dashboard/menu/store#post','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('205','120','菜单修改操作','4','#94#-#95#-#120#-#205#','','1','1','1','/dashboard/menu/update#put','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('206','120','菜单详情操作','4','#94#-#95#-#120#-#206#','','1','1','1','/dashboard/menu/show#get','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('207','120','菜单移除操作','4','#94#-#95#-#120#-#207#','','1','1','1','/dashboard/menu/destroy#delete','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('208','120','菜单状态操作操作','4','#94#-#95#-#120#-#208#','','1','1','1','/dashboard/menu/statusUpdate#put','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('209','77','敏感词词库列表操作','4','#1#-#74#-#77#-#209#','','1','1','1','/dashboard/sensitiveWord/index#get','1','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('210','77','敏感词添加操作','4','#1#-#74#-#77#-#210#','','1','1','1','/dashboard/sensitiveWord/store#post','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('211','77','敏感词删除操作','4','#1#-#74#-#77#-#211#','','1','1','1','/dashboard/sensitiveWord/destroy#delete','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('212','77','敏感词移动操作','4','#1#-#74#-#77#-#212#','','1','1','1','/dashboard/sensitiveWord/move#put','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('213','77','敏感词关闭操作','4','#1#-#74#-#77#-#213#','','1','1','1','/dashboard/sensitiveWord/statusUpdate#put','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('214','77','敏感词分组添加操作','4','#1#-#74#-#77#-#214#','','1','1','1','/dashboard/sensitiveWordGroup/store#post','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('215','77','敏感词分组修改操作','4','#1#-#74#-#77#-#215#','','1','1','1','/dashboard/sensitiveWordGroup/update#put','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('216','96','通讯录列表操作','4','#94#-#95#-#96#-#216#','','1','1','1','/dashboard/workEmployee/index#get','2','0','系统','99','2020-12-31 19:22:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('217','96','通讯录同步操作','4','#94#-#95#-#96#-#217#','','1','1','1','/dashboard/workEmployee/synEmployee#put','2','0','系统','99','2020-12-31 19:22:14','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('218','1','系统首页','2','#1#-#218#','home','1','1','1','/dashboardpath/1610968617','1','0','系统','1','2020-12-31 19:22:04','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('219','218','系统首页','3','#1#-#218#-#219#','','1','1','1','/dashboard/corpData/index','2','0','系统','98','2020-12-31 19:22:04','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('220','71','用户画像','3','#1#-#71#-#220#','','1','1','1','/dashboard/chatTool/customer','2','0','系统','99','2021-02-05 11:35:55','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('221','71','聊天增强','3','#1#-#71#-#221#','','1','1','1','/dashboard/chatTool/enhance','2','0','系统','99','2021-02-05 11:36:44','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('222','2','用户搜索添加','3','#1#-#2#-#222#','','1','1','1','/dashboard/greeting/userSearch','2','0','系统','99','2021-02-05 11:38:10','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('234','54','入群欢迎语','3','#1#-#54#-#234#','','1','1','1','/dashboard/roomWelcome/index','2','0','系统','4','2021-04-19 10:26:34','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('239','3','数据统计员工详情','4','#1#-#2#-#3#-#239#','','1','1','1','/dashboard/contactBatchAdd/employeeDataShow','2','0','系统','99','2021-04-19 15:17:16','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('241','234','新增/编辑','4','#1#-#54#-#234#-#241#','','1','1','1','/dashboard/roomWelcome/create','2','0','系统','99','2021-04-20 02:55:49','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('242','234','修改','4','#1#-#54#-#311#-#242#','','1','1','1','/dashboard/roomWelcome/update','2','0','系统','99','2021-04-20 02:56:48','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('243','1','授权管理','2','#1#-#243#','star','1','1','1','/dashboardpath/1618917677','1','0','系统','99','2021-04-20 11:21:17','2021-08-09 17:01:36','2021-06-28 21:47:06');
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('247','330','群裂变','3','#1#-#330#-#247#','','1','1','1','/dashboard/roomFission/index','2','0','系统','99','2021-04-21 08:48:37','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('248','247','创建','4','#1#-#330#-#247#-#248#','','1','1','1','/dashboard/roomFission/create','2','0','系统','99','2021-04-21 08:50:02','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('249','247','邀请','4','#1#-#330#-#247#-#249#','','1','1','1','/dashboard/roomFission/invite','2','0','系统','99','2021-04-21 09:03:37','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('250','247','修改','4','#1#-#330#-#247#-#250#','','1','1','1','/dashboard/roomFission/update','2','0','系统','99','2021-04-21 09:03:53','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('254','330','企微任务宝','3','#1#-#330#-#254#','','1','1','1','/dashboard/workFission/taskpage','2','0','系统','99','2021-04-26 13:49:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('255','254','数据详情','3','#1#-#330#-#254#-#255#','','1','1','1','/dashboard/workFission/dataShow','2','0','系统','99','2021-04-26 13:50:22','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('256','254','创建','4','#1#-#330#-#254#-#256#','','1','1','1','/dashboard/workFission/create','2','0','系统','99','2021-04-26 13:51:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('257','254','邀请','4','#1#-#330#-#254#-#257#','','1','1','1','/dashboard/workFission/invite','2','0','系统','99','2021-04-26 13:51:33','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('258','254','修改','4','#1#-#330#-#254#-#258#','','1','1','1','/dashboard/workFission/edit','2','0','系统','99','2021-04-26 13:51:57','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('260','14','个人SOP','3','#1#-#14#-#260#','','1','1','1','/dashboard/contactSop/index','2','0','系统','99','2021-04-26 16:37:31','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('261','260','创建','4','#1#-#14#-#260#-#261#','','1','1','1','/dashboard/contactSop/create','2','0','系统','99','2021-04-26 16:37:47','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('262','260','编辑','4','#1#-#14#-#260#-#262#','','1','1','1','/dashboard/contactSop/edit','2','0','系统','99','2021-04-26 16:38:03','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('264','54','群SOP','3','#1#-#54#-#264#','','1','1','1','/dashboard/roomSop/index','2','0','系统','2','2021-04-26 16:38:34','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('265','264','创建','4','#1#-#54#-#264#-#265#','','1','1','1','/dashboard/roomSop/create','2','0','系统','99','2021-04-26 16:38:49','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('266','264','编辑','4','#1#-#54#-#264#-#266#','','1','1','1','/dashboard/roomSop/edit','2','0','系统','99','2021-04-26 16:39:08','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('268','27','离职继承','3','#1#-#27#-#268#','','1','1','1','/dashboard/contactTransfer/resignIndex','2','0','系统','5','2021-04-26 16:40:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('269','268','分配记录','4','#1#-#27#-#268#-#269#','','1','1','1','/dashboard/contactTransfer/resignAllotRecord','2','0','系统','99','2021-04-26 16:40:26','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('270','27','在职转接','3','#1#-#27#-#270#','','1','1','1','/dashboard/contactTransfer/workIndex','2','0','系统','6','2021-04-26 16:40:42','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('271','270','分配记录','4','#1#-#27#-#270#-#271#','','1','1','1','/dashboard/contactTransfer/workAllotRecord','2','0','系统','99','2021-04-26 16:41:01','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('272','260','详情','4','#1#-#259#-#260#-#272#','','1','1','1','/dashboard/contactSop/detail','2','0','系统','99','2021-05-04 09:35:17','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('273','264','详情','4','#1#-#263#-#264#-#273#','','1','1','1','/dashboard/roomSop/detail','2','0','系统','99','2021-05-04 09:35:58','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('276','330','群打卡','3','#1#-#330#-#276#','','1','1','1','/dashboard/roomClockIn/index','2','0','系统','99','2021-06-03 09:43:41','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('277','276','创建群打卡','4','#1#-#330#-#276#-#277#','','1','1','1','/dashboard/roomClockIn/create','2','0','系统','99','2021-06-03 09:47:42','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('279','2','门店活码','3','#2#-#279#','','1','1','1','/dashboard/shopCode/employeeIndex','2','0','系统','99','2021-06-07 09:09:54','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('283','330','抽奖活动','3','#1#-#330#-#283#','','1','1','1','/dashboard/lottery/index','2','0','系统','99','2021-06-08 10:04:48','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('284','283','创建抽奖活动','4','#1#-#330#-#283#-#284#','','1','1','1','/dashboard/lottery/create','2','0','系统','99','2021-06-08 11:50:00','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('285','1','运营管理中心','2','#1#-#285#','stock','1','1','1','/dashboardpath/1623146912','1','0','系统','6','2021-06-08 18:08:32','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('286','285','客户统计','3','#1#-#285#-#286#','','1','1','1','/dashboard/statistics/contact','2','0','系统','99','2021-06-08 18:08:51','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('287','285','成员统计','3','#1#-#285#-#287#','','1','1','1','/dashboard/statistics/employee','2','0','系统','99','2021-06-08 18:09:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('288','54','标签建群','3','#1#-#54#-#288#','','1','1','1','/dashboard/roomTagPull/index','2','0','系统','6','2021-06-09 14:06:32','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('290','288','创建邀请','4','#1#-#54#-#288#-#290#','','1','1','1','/dashboard/roomTagPull/create','2','0','系统','99','2021-06-09 16:56:30','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('292','54','群日历','3','#1#-#54#-#292#','','1','1','1','/dashboard/roomCalendar/index','2','0','系统','3','2021-06-09 17:11:00','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('293','292','创建日历','4','#1#-#54#-#292#-#293#','','1','1','1','/dashboard/roomCalendar/create','2','0','系统','99','2021-06-09 18:01:52','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('294','283','详情','4','#1#-#282#-#283#-#294#','','1','1','1','/dashboard/lottery/show','2','0','系统','99','2021-06-10 10:18:57','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('295','276','修改','4','#1#-#275#-#276#-#295#','','1','1','1','/dashboard/roomClockIn/edit','2','0','系统','99','2021-06-10 10:31:22','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('296','54','客户群提醒','3','#1#-#54#-#296#','','1','1','1','/dashboard/roomRemind/index','2','0','系统','8','2021-06-10 13:51:50','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('297','276','详情','4','#1#-#275#-#276#-#297#','','1','1','1','/dashboard/roomClockIn/show','2','0','系统','99','2021-06-10 16:09:49','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('299','14','关键词打标签','3','#1#-#14#-#299#','','1','1','1','/dashboard/autoTag/keywordIndex','2','0','系统','99','2021-06-10 17:56:55','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('300','54','群聊质检','3','#1#-#54#-#300#','','1','1','1','/dashboard/roomQuality/index','2','0','系统','9','2021-06-10 19:33:06','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('301','300','新建质检规则','4','#1#-#54#-#300#-#301#','','1','1','1','/dashboard/roomQuality/newRule','2','0','系统','99','2021-06-11 08:41:05','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('302','299','创建项目','4','#1#-#14#-#299#-#303#','','2','1','1','/dashboard/autoTag/keywordCreate','2','0','系统','99','2021-06-11 08:56:15','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('303','299','详情','4','#1#-#298#-#299#-#303#','','2','1','1','/dashboard/autoTag/keywordShow','2','0','系统','99','2021-06-11 10:25:38','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('304','14','客户入群行为打标签','3','#1#-#14#-#304#','','1','1','1','/dashboard/autoTag/joinRoomIndex','2','0','系统','99','2021-06-11 14:36:59','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('305','304','添加规则','4','#1#-#14#-#304#-#305#','','2','1','1','/dashboard/autoTag/joinRoomCreate','2','0','系统','99','2021-06-11 14:37:27','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('306','304','规则详情','4','#1#-#14#-#304#-#306#','','2','1','1','/dashboard/autoTag/joinRoomShow','2','0','系统','99','2021-06-11 15:47:58','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('307','14','分时段打标签','3','#1#-#14#-#307#','','1','1','1','/dashboard/autoTag/dayPartIndex','2','0','系统','99','2021-06-11 16:25:39','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('308','307','分时段打标签添加规则','4','#1#-#14#-#307#-#308#','','2','1','1','/dashboard/autoTag/dayPartCreate','2','0','系统','99','2021-06-11 16:50:12','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('309','307','分时段打标签详情','4','#1#-#14#-#307#-#309#','','2','1','1','/dashboard/autoTag/dayPartShow','2','0','系统','99','2021-06-11 17:20:35','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('311','54','无限拉群','3','#1#-#54#-#311#','','1','1','1','/dashboard/roomInfinitePull/index','2','0','系统','7','2021-06-11 18:02:14','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('312','311','新建拉群','4','#1#-#54#-#311#-#312#','','1','1','1','/dashboard/roomInfinitePull/create','2','0','系统','99','2021-06-11 18:34:51','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('313','311','详情','4','#1#-#54#-#311#-#313#','','1','1','1','/dashboard/roomInfinitePull/show','2','0','系统','99','2021-06-12 00:30:46','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('314','288','列表详情','4','#1#-#54#-#288#-#314#','','1','1','1','/dashboard/roomTagPull/detail','2','0','系统','99','2021-06-12 09:46:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('317','292','详情','4','#1#-#54#-#292#-#317#','','1','1','1','/dashboard/roomCalendar/show','2','0','系统','99','2021-06-16 11:30:34','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('318','283','修改','4','#1#-#282#-#283#-#318#','','1','1','1','/dashboard/lottery/modify','2','0','系统','99','2021-06-17 20:15:35','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('319','300','规则详情','4','#1#-#54#-#300#-#319#','','1','1','1','/dashboard/roomQuality/detail','2','0','系统','99','2021-06-21 20:07:04','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('321','14','互动雷达','3','#1#-#14#-#321#','','1','1','1','/dashboard/radar/index','2','0','系统','99','2021-06-21 23:35:35','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('323','321','创建连接','4','#1#-#14#-#321#-#323#','','1','1','1','/dashboard/radar/createLink','2','0','系统','99','2021-06-21 23:36:31','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('324','321','创建PDF','4','#1#-#14#-#321#-#324#','','1','1','1','/dashboard/radar/createPdf','2','0','系统','99','2021-06-21 23:36:48','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('325','321','修改','4','#1#-#14#-#321#-#325#','','1','1','1','/dashboard/radar/edit','2','0','系统','99','2021-06-21 23:37:39','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('326','321','详情','4','#1#-#14#-#321#-#326#','','1','1','1','/dashboard/radar/detail','2','0','系统','99','2021-06-21 23:37:56','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('327','300','规则编辑','4','#1#-#54#-#300#-#327#','','1','1','1','/dashboard/roomQuality/edit','2','0','系统','99','2021-06-22 18:27:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('328','311','编辑','4','#1#-#310#-#311#-#328#','','1','1','1','/dashboard/radar/show','2','0','系统','99','2021-06-23 16:15:42','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('329','321','修改PDF','4','#1#-#14#-#321#-#329#','','1','1','1','/dashboard/radar/editPdf','2','0','系统','99','2021-06-24 23:01:09','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('330','1','营销中心','2','#1#-#330#','contacts','1','1','1','/dashboardpath/1624886566','1','0','系统','3','2021-06-28 21:22:46','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('331','321','pdf详情','4','#1#-#14#-#321#-#331#','','1','1','1','/dashboard/radar/pdfDetail','2','0','系统','99','2021-06-29 15:37:43','2021-08-09 17:22:00',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('332','247','数据详情','4','#1#-#330#-#247#-#332#','','1','1','1','/dashboard/roomFission/dataShow','2','0','系统','99','2021-07-07 10:18:56','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('335','95','公众号授权','3','#1#-#95#-#335#','','1','1','1','/dashboard/officialAccount/index','2','0','系统','7','2021-07-12 10:51:13','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('336','335','添加公众号','4','#1#-#95#-#335#-#336#','','1','1','1','/dashboard/officialAccount/create','2','0','系统','99','2021-07-12 10:54:38','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('337','14','自动打标签','3','#1#-#14#-#337#','','1','1','1','/dashboard/autoTag/ruleTagging','2','0','系统','99','2021-07-17 15:40:48','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('338','321','创建文章','4','#1#-#14#-#321#-#338#','','1','1','1','/dashboard/radar/createArticle','2','0','系统','99','2021-07-20 11:19:11','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('339','321','修改文章','4','#1#-#14#-#321#-#339#','','1','1','1','/dashboard/radar/editArticle','2','0','系统','99','2021-07-20 17:33:30','2021-08-09 17:01:36',null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('340','321','文章详情','4','#1#-#14#-#321#-#340#','','1','1','1','/dashboard/radar/articleDetail','2','0','系统','99','2021-07-20 19:00:30','2021-08-09 17:00:37',null);

