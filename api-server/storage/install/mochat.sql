-- ----------------------------
-- Table structure for mc_business_log
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_business_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(11) NOT NULL DEFAULT '0' COMMENT '相应业务id',
  `params` json DEFAULT NULL COMMENT '参数',
  `event` smallint(6) NOT NULL DEFAULT '4' COMMENT '事件',
  `operation_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作人id（mc_work_employee.id）',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='业务日志表';

-- ----------------------------
-- Table structure for mc_channel_code
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_channel_code` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` int(11) NOT NULL DEFAULT '0' COMMENT '企业id',
  `group_id` int(11) NOT NULL DEFAULT '0' COMMENT '渠道码分组id（mc_channel_code_group.id）',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '活码名称',
  `qrcode_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '二维码地址',
  `wx_config_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '二维码凭证',
  `auto_add_friend` tinyint(4) NOT NULL DEFAULT '0' COMMENT '自动添加好友（1.开启，2.关闭）',
  `tags` json NOT NULL COMMENT '客户标签',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '类型（1.单人，2.多人）',
  `drainage_employee` json NOT NULL COMMENT '引流成员设置',
  `welcome_message` json NOT NULL COMMENT '欢迎语设置',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='渠道码表';

-- ----------------------------
-- Table structure for mc_channel_code_group
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_channel_code_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` int(11) NOT NULL COMMENT '企业id',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分组名称',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='渠道码-分组表';

-- ----------------------------
-- Table structure for mc_chat_tool
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_chat_tool` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '侧边栏页面名称',
  `page_flag` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '侧边栏页面标识',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) DEFAULT '1' COMMENT '状态 0否 1是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='企业侧边工具栏';

-- ----------------------------
-- Table structure for mc_contact_employee_process
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_contact_employee_process` (
  `id` int(10) unsigned NOT NULL,
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业表ID（corp.id）',
  `employee_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '员工ID（mc_work_employee.id）',
  `contact_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '外部联系人ID（mc_work_contact.id）',
  `contact_process_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '跟进流程ID',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '跟进内容',
  `file_url` json DEFAULT NULL COMMENT '附件地址',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='通讯录-客户-跟进记录(中间表) ';

-- ----------------------------
-- Table structure for mc_contact_employee_track
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_contact_employee_track` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '通讯录ID(mc_work_employee.id)',
  `contact_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '外部联系人ID work_contact.id',
  `event` tinyint(4) NOT NULL DEFAULT '0' COMMENT '事件',
  `content` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '内容',
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业表ID corp.id',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='通讯录 - 客户 - 轨迹互动';

-- ----------------------------
-- Table structure for mc_contact_field
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_contact_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字段标识 input-name',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '字段名称 input-label',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '字段类型 input-type 0text 1radio 2 checkbox 3select 4file 5date 6dateTime 7number 8rate',
  `options` json DEFAULT NULL COMMENT '字段可选值 input-options',
  `order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态 0不展示 1展示',
  `is_sys` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为系统字段 0否1是',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户高级属性';

-- ----------------------------
-- Table structure for mc_contact_field_pivot
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_contact_field_pivot` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户表ID（work_contact.id）',
  `contact_field_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '高级属性表ID(contact_field.id）',
  `value` text COLLATE utf8mb4_unicode_ci COMMENT '高级属性值',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='(客户-高级属性-中间表)用户画像';

-- ----------------------------
-- Table structure for mc_contact_message_batch_send
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_contact_message_batch_send` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业表ID （mc_corp.id）',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID【mc_user.id】',
  `user_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称【mc_user.name】',
  `employee_ids` json NOT NULL COMMENT '员工ids',
  `filter_params` json DEFAULT NULL COMMENT '筛选客户参数',
  `filter_params_detail` json DEFAULT NULL COMMENT '筛选客户参数显示详情',
  `content` json NOT NULL COMMENT '群发消息内容',
  `send_way` tinyint(4) NOT NULL DEFAULT '1' COMMENT '发送方式（1-立即发送，2-定时发送）',
  `definite_time` timestamp NULL DEFAULT NULL COMMENT '定时发送时间',
  `send_time` timestamp NULL DEFAULT NULL COMMENT '发送时间',
  `send_employee_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送成员数量',
  `send_contact_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送客户数量',
  `send_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已发送数量',
  `not_send_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '未发送数量',
  `received_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已送达数量',
  `not_received_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '未送达数量',
  `receive_limit_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户接收已达上限',
  `not_friend_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '因不是好友发送失败',
  `send_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态（0-未发送，1-已发送）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户消息群发表';

-- ----------------------------
-- Table structure for mc_contact_message_batch_send_employee
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_contact_message_batch_send_employee` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户消息群发id （mc_contact_message_batch_send.id)',
  `employee_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '员工id （mc_work_employee.id)',
  `wx_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信userId （mc_work_employee.wx_user_id)',
  `send_contact_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送客户数量',
  `err_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '返回码',
  `err_msg` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '对返回码的文本描述内容',
  `msg_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '企业群发消息的id，可用于获取群发消息发送结果',
  `send_time` timestamp NULL DEFAULT NULL COMMENT '发送时间',
  `last_sync_time` timestamp NULL DEFAULT NULL COMMENT '最后一次同步结果时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态（0-未发送，1-已发送, 2-发送失败）',
  `receive_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '接收状态(0-未接收，1-已接收，2-接收失败)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户消息群发成员表';

-- ----------------------------
-- Table structure for mc_contact_message_batch_send_result
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_contact_message_batch_send_result` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户消息群发id （mc_contact_message_batch_send.id)',
  `employee_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '员工id （mc_work_employee.id)',
  `contact_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户表id（work_contact.id）',
  `external_user_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人userid',
  `user_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '企业服务人员的userid',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '发送状态 0-未发送 1-已发送 2-因客户不是好友导致发送失败 3-因客户已经收到其他群发消息导致发送失败',
  `send_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送时间，发送状态为1时返回',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户消息群发结果表';

-- ----------------------------
-- Table structure for mc_contact_process
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_contact_process` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'corp表id',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '描述',
  `order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户跟进状态';

-- ----------------------------
-- Table structure for mc_corp
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_corp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '企业名称',
  `wx_corpid` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '企业微信ID',
  `social_code` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '企业代码(企业统一社会信用代码)',
  `employee_secret` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '企业通讯录secret',
  `event_callback` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '事件回调地址',
  `contact_secret` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '企业外部联系人secret',
  `token` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '回调token',
  `encoding_aes_key` char(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '回调消息加密串',
  `tenant_id` int(11) DEFAULT '0' COMMENT '租户ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='企业';

-- ----------------------------
-- Table structure for mc_corp_day_data
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_corp_day_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` int(11) NOT NULL DEFAULT '0' COMMENT '企业id',
  `add_contact_num` int(11) NOT NULL DEFAULT '0' COMMENT '新增客户数',
  `add_room_num` int(11) NOT NULL DEFAULT '0' COMMENT '新增社群数',
  `add_into_room_num` int(11) NOT NULL DEFAULT '0' COMMENT '新增入群数',
  `loss_contact_num` int(11) NOT NULL DEFAULT '0' COMMENT '流失客户数',
  `quit_room_num` int(11) NOT NULL DEFAULT '0' COMMENT '退群数',
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '日期',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='企业日数据';

-- ----------------------------
-- Table structure for mc_greeting
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_greeting` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `corp_id` int(11) NOT NULL DEFAULT '0' COMMENT '企业ID',
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '欢迎语类型',
  `words` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '欢迎语文本',
  `medium_id` int(11) NOT NULL DEFAULT '0' COMMENT '欢迎语素材',
  `range_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '适用成员类型【1-全部成员(默认)】',
  `employees` json NOT NULL COMMENT '适用成员',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='欢迎语';

-- ----------------------------
-- Table structure for mc_medium
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_medium` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `media_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '素材媒体标识[有效期3天]',
  `last_upload_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上一次微信素材上传的时间戳',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '类型 1文本、2图片、3音频、4视频、5小程序、6文件素材',
  `is_sync` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否同步素材库(1-同步2-不同步，默认:1)',
  `content` json NOT NULL COMMENT '具体内容:',
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业表ID(mc_corp.id)',
  `medium_group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '素材分组ID medium_group.id',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '上传者ID',
  `user_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '上传者名称',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='素材库 ';

-- ----------------------------
-- Table structure for mc_medium_group
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_medium_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业表ID',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='素材库-分组';

-- ----------------------------
-- Table structure for mc_official_account
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_official_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `appid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '第三方平台 appid',
  `authorized_status` tinyint(1) DEFAULT NULL COMMENT '授权状态（1：授权成功，2：更新授权，3：取消授权）',
  `authorizer_appid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '公众号或小程序的 appid',
  `authorization_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '授权码，可用于获取授权信息',
  `pre_auth_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '预授权码\r\n预授权码',
  `head_img` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '头像',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '头像服务器地址',
  `business_info` json DEFAULT NULL COMMENT '{"open_pay": 0, "open_shake": 0, "open_scan": 0, "open_card": 0, "open_store": 0}',
  `modules` json DEFAULT NULL COMMENT '["contact_way_region", "raffle_activity", "check_in", "radar"]',
  `news_offset` tinyint(1) DEFAULT NULL COMMENT '（0：已关闭，1：开启）',
  `nickname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '企业昵称',
  `service_type_info` tinyint(1) DEFAULT '0' COMMENT '公众号类型（0：订阅号，1由历史老帐号升级后的订阅号：，2：服务号）',
  `verify_type_info` tinyint(1) DEFAULT '0' COMMENT '服务号\r\n公众号认证类型(-1:未认证，0：微信认证，:1：新浪微博认证，2：腾讯微博认证，3：已资质认证通过但还未通过名称认证，4：已资质认证通过、还未通过名称认证，但通过了新浪微博认证',
  `original_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `func_info` json DEFAULT NULL COMMENT '[\r\n      {\r\n        "funcscope_category": {\r\n          "id": 1\r\n        }\r\n      },\r\n      {\r\n        "funcscope_category": {\r\n          "id": 2\r\n        }\r\n      }\r\n    ]',
  `principal_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alias` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '公众号所设置的微信号，可能为空',
  `qrcode_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '二维码图片的 UR',
  `local_qrcode_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '二维码图片的 UR(服务器地址）',
  `callback_suffix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `callback_verified` tinyint(1) DEFAULT NULL,
  `user_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '原始id',
  `encoding_aes_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '第三方平台消息加解密  Key',
  `notify_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '第三方平台appserect',
  `token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '第三方平台token',
  `create_time` int(11) DEFAULT NULL COMMENT '授权时间',
  `tenant_id` int(11) DEFAULT '0' COMMENT '租户id',
  `corp_id` int(11) DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='公众号授权表';

-- ----------------------------
-- Table structure for mc_official_account_set
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_official_account_set` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `official_account_id` tinyint(1) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL COMMENT '授权模块（1：群打卡，2：抽奖活动，3：门店活码，4：互动雷达）',
  `tenant_id` int(11) DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='公众号设置表';

-- ----------------------------
-- Table structure for mc_plugin
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_plugin` (
  `id` int(11) NOT NULL,
  `corp_id` int(11) NOT NULL DEFAULT '0' COMMENT '企业id',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '插件名称',
  `version` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '版本号',
  `content` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '简介',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态（1-启用，2-禁用）',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='插件表';

-- ----------------------------
-- Table structure for mc_rbac_menu
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_rbac_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `parent_id` int(11) NOT NULL COMMENT '父级ID',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `level` tinyint(4) NOT NULL DEFAULT '1' COMMENT '菜单等级【1-一级菜单2-二级菜单···】',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'ID路径【id-id-id】',
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '图标标识',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态【1-启动(默认)2-禁用】',
  `link_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '链接类型【1-内部链接(默认)2-外部链接】',
  `is_page_menu` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否为页面菜单 1-是 2-否',
  `link_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '链接地址【pathinfo#method】',
  `data_permission` tinyint(1) NOT NULL DEFAULT '1' COMMENT '数据权限 【1-启用 2不启用（查看企业下数据）】',
  `operate_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作人ID【mc_user.id】',
  `operate_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '操作人姓名【mc_user.name】',
  `sort` int(11) DEFAULT '99' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='菜单表';

-- ----------------------------
-- Table structure for mc_rbac_role
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_rbac_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `tenant_id` int(11) NOT NULL COMMENT '租户ID【mc_tenant.id】',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '角色名称',
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '角色描述',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态【1-启动(默认)2-禁用】',
  `operate_id` int(11) NOT NULL COMMENT '操作人ID【mc_user.id】',
  `operate_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '操作人ID【mc_user.name】',
  `data_permission` json DEFAULT NULL COMMENT '企业部门数据权限，例子[{`corpId`: `1`, `permissionType`: 1}] // 1-是(所选择企业)本用户部门 2-否 （本用户）\r\n',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色表';

-- ----------------------------
-- Table structure for mc_rbac_role_menu
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_rbac_role_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `role_id` int(11) NOT NULL DEFAULT '0' COMMENT '角色ID【mc_rbac_role.id】',
  `menu_id` int(11) NOT NULL DEFAULT '0' COMMENT '菜单ID【mc_rbac_menu.id】',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色-权限对应表';

-- ----------------------------
-- Table structure for mc_rbac_user_role
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_rbac_user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID【mc_user.id】',
  `role_id` int(11) NOT NULL DEFAULT '0' COMMENT '角色ID【mc_rbac_role.id】',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户角色关联表';

-- ----------------------------
-- Table structure for mc_room_message_batch_send
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_room_message_batch_send` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业表ID （mc_corp.id）',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID【mc_user.id】',
  `user_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名称【mc_user.name】',
  `employee_ids` json NOT NULL COMMENT '员工ids',
  `batch_title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '群发名称',
  `content` json NOT NULL COMMENT '群发消息内容',
  `send_way` tinyint(4) NOT NULL DEFAULT '1' COMMENT '发送方式（1-立即发送，2-定时发送）',
  `definite_time` timestamp NULL DEFAULT NULL COMMENT '定时发送时间',
  `send_time` timestamp NULL DEFAULT NULL COMMENT '发送时间',
  `send_room_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送成员数量',
  `send_employee_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送客户数量',
  `send_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已发送数量',
  `not_send_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '未发送数量',
  `received_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已送达数量',
  `not_received_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '未送达数量',
  `send_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态（0-未发送，1-已发送）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户群消息群发表';

-- ----------------------------
-- Table structure for mc_room_message_batch_send_employee
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_room_message_batch_send_employee` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户群消息群发id （mc_contact_message_batch_send.id)',
  `employee_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '员工id （mc_work_employee.id)',
  `wx_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信userId （mc_work_employee.wx_user_id)',
  `send_room_total` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送群数量',
  `err_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '返回码',
  `err_msg` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '对返回码的文本描述内容',
  `msg_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '企业群发消息的id，可用于获取群发消息发送结果',
  `send_time` timestamp NULL DEFAULT NULL COMMENT '发送时间',
  `last_sync_time` timestamp NULL DEFAULT NULL COMMENT '最后一次同步结果时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态（0-未发送，1-已发送, 2-发送失败）',
  `receive_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '接收状态(0-未接收，1-已接收，2-接收失败)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户群消息群发成员表';

-- ----------------------------
-- Table structure for mc_room_message_batch_send_result
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_room_message_batch_send_result` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户群消息群发id （mc_contact_message_batch_send.id)',
  `employee_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '员工id （mc_work_employee.id)',
  `room_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户群id（work_room.id）',
  `room_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '客户群名称（work_room.name）',
  `room_employee_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户群成员数量',
  `room_create_time` timestamp NULL DEFAULT NULL COMMENT '群聊创建时间',
  `chat_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部客户群id，群发消息到客户不吐出该字段',
  `user_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '企业服务人员的userid',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '发送状态 0-未发送 1-已发送 2-因客户不是好友导致发送失败 3-因客户已经收到其他群发消息导致发送失败',
  `send_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送时间，发送状态为1时返回',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户群消息群发结果表';

-- ----------------------------
-- Table structure for mc_room_tag_pull
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_room_tag_pull` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '任务名称',
  `employees` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '群发员工',
  `choose_contact` json DEFAULT NULL COMMENT '筛选客户条件',
  `guide` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '入群引导语',
  `rooms` json DEFAULT NULL COMMENT '群聊',
  `filter_contact` tinyint(1) DEFAULT '1' COMMENT '过滤客户（0：否，1：是）',
  `contact_num` int(11) DEFAULT NULL COMMENT '客户数量',
  `wx_tid` json DEFAULT NULL COMMENT '企业群发消息的id',
  `tenant_id` int(11) DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) DEFAULT NULL COMMENT '企业id',
  `create_user_id` int(11) DEFAULT NULL COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='标签建群-基本信息表';

-- ----------------------------
-- Table structure for mc_room_tag_pull_contact
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_room_tag_pull_contact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `room_tag_pull_id` int(11) NOT NULL COMMENT '标签建群id(mc_room_tag_pull.id)',
  `contact_id` int(11) NOT NULL COMMENT '客户id',
  `wx_external_userid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '客户wx_external_userid',
  `contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '客户名称',
  `employee_id` int(11) NOT NULL COMMENT '员工id',
  `wx_user_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '员工wx_user_id',
  `send_status` tinyint(1) DEFAULT '0' COMMENT '发送状态：0-未发送 1-已发送 2-因客户不是好友导致发送失败 3-因客户已经收到其他群发消息导致发送失',
  `is_join_room` tinyint(1) DEFAULT '0' COMMENT '是否入群（0：否，1：是）',
  `room_id` int(11) NOT NULL COMMENT '客户群id(mc_work_room.id)',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='标签建群-客户表';

-- ----------------------------
-- Table structure for mc_room_welcome_template
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_room_welcome_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT '企业表ID（mc_crop.id）',
  `msg_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '欢迎语1（文字）',
  `complex_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '欢迎语2类型',
  `msg_complex` json DEFAULT NULL COMMENT '欢迎语2（图片、链接、小程序）',
  `complex_template_id` varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '欢迎语2素材id',
  `create_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='入群欢迎语表';

-- ----------------------------
-- Table structure for mc_sys_log
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_sys_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `url_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '请求链接【/user/create】',
  `method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '请求方法【get|post|put】',
  `query` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'GET参数',
  `body` json NOT NULL COMMENT 'body参数',
  `menu_id` int(11) NOT NULL COMMENT '菜单ID【mc_rbac_menu.id】',
  `menu_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '菜单名称【mc_rbac_menu.name】',
  `operate_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作人ID【mc_user.id】',
  `operate_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '操作人姓名【mc_user.name】',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统日志';

-- ----------------------------
-- Table structure for mc_system_config
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_system_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `tenant_id` int(11) DEFAULT NULL COMMENT '租户id',
  `corp_id` int(11) DEFAULT NULL COMMENT '企业id',
  `creator_id` int(11) DEFAULT NULL COMMENT '创建人ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uni_name` (`name`) USING BTREE COMMENT '唯一索引-名称'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='数据字典表';

-- ----------------------------
-- Table structure for mc_system_config_value
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_system_config_value` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类别 （1-系统 2-代理 3-租户）',
  `target_id` int(11) NOT NULL DEFAULT '0' COMMENT '外部id(tenant_id/user_id/agent_id等)',
  `config_id` int(11) NOT NULL DEFAULT '0' COMMENT '配置id',
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '配置值',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '描述',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='数据字典值表';

-- ----------------------------
-- Table structure for mc_tenant
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_tenant` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '租户名称',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '租户状态[1-正常2-停用]',
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '租户Logo地址',
  `login_background` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '登录页背景图地址',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '网站地址',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '删除时间',
  `copyright` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '租户版权',
  `server_ips` json DEFAULT NULL COMMENT '服务器IPs',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='租户表';

-- ----------------------------
-- Table structure for mc_user
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `phone` char(11) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '密码',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '姓名',
  `gender` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '性别 0未定义 1男 2女',
  `department` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '部门',
  `position` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '职务',
  `login_time` timestamp NULL DEFAULT NULL COMMENT '上一次登陆时间',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态 0未启用 1正常 2禁用',
  `tenant_id` int(11) NOT NULL DEFAULT '1' COMMENT '租户ID(mc_tenant.id)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `isSuperAdmin` tinyint(1) DEFAULT '0' COMMENT '是否为超级管理员 - 0否1是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='(子账户)系统管理员';

-- ----------------------------
-- Table structure for mc_work_agent
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_agent` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` int(11) NOT NULL COMMENT '企业ID',
  `wx_agent_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信应用ID',
  `wx_secret` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信应用secret',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '应用名称',
  `square_logo_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '应用方形头像',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '应用详情',
  `close` tinyint(4) NOT NULL DEFAULT '0' COMMENT '应用是否被停用 0否1是',
  `redirect_domain` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '应用可信域名',
  `report_location_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '应用是否打开地理位置上报 0：不上报；1：进入会话上报；',
  `is_reportenter` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否上报用户进入应用事件。0：不接收；1：接收',
  `home_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '应用主页url',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='企业应用表';

-- ----------------------------
-- Table structure for mc_work_contact
-- ----------------------------

CREATE TABLE IF NOT EXISTS `mc_work_contact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业表ID（mc_crop.id）',
  `wx_external_userid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人external_userid',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人姓名',
  `nick_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人昵称',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人的头像',
  `follow_up_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '跟进状态（1.未跟进 2.跟进中 3.已拒绝 4.已成交 5.已复购）',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '外部联系人的类型，1表示该外部联系人是微信用户，2表示该外部联系人是企业微信用户',
  `gender` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '外部联系人性别 0-未知 1-男性 2-女性',
  `unionid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人在微信开放平台的唯一身份标识（微信unionid）',
  `position` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人的职位，如果外部企业或用户选择隐藏职位，则不返回，仅当联系人类型是企业微信用户时有此字段\r\n',
  `corp_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人所在企业的简称，仅当联系人类型是企业微信用户时有此字段\r\n',
  `corp_full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人所在企业的主体名称',
  `external_profile` json DEFAULT NULL COMMENT '外部联系人的自定义展示信息',
  `business_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人编号',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='联系人表（客户列表）';

-- ----------------------------
-- Table structure for mc_work_contact_employee
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_contact_employee` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '通讯录表ID（work_employee.id）',
  `contact_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户表ID（work_contact.id）',
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '员工对此外部联系人的备注',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '员工对此外部联系人的描述',
  `remark_corp_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '员工对此客户备注的企业名称',
  `remark_mobiles` json DEFAULT NULL COMMENT '员工对此客户备注的手机号码',
  `add_way` int(10) unsigned NOT NULL COMMENT '表示添加客户的来源\r\n0\r\n未知来源\r\n1\r\n扫描二维码\r\n2\r\n搜索手机号\r\n3\r\n名片分享\r\n4\r\n群聊\r\n5\r\n手机通讯录\r\n6\r\n微信联系人\r\n7\r\n来自微信的添加好友申请\r\n8\r\n安装第三方应用时自动添加的客服人员\r\n9\r\n搜索邮箱\r\n201\r\n内部成员共享\r\n202\r\n管理员/负责人分配\r\n',
  `oper_userid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '发起添加的userid，如果成员主动添加，为成员的userid；如果是客户主动添加，则为客户的外部联系人userid；如果是内部成员共享/管理员分配，则为对应的成员/管理员userid\r\n',
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '企业自定义的state参数，用于区分客户具体是通过哪个「联系我」添加，由企业通过创建「联系我」方式指定\r\n',
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业表ID（corp.id）',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1.正常 2.删除 3.拉黑',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '员工添加此外部联系人的时间',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='通讯录 - 客户 中间表';

-- ----------------------------
-- Table structure for mc_work_contact_room
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_contact_room` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wx_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `contact_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户表id（work_contact.id）',
  `employee_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '员工ID (work_employee.id)',
  `unionid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '仅当群成员类型是微信用户（包括企业成员未添加好友），且企业或第三方服务商绑定了微信开发者ID有此字段',
  `room_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户群表id（work_room.id）',
  `join_scene` tinyint(3) unsigned DEFAULT '3' COMMENT '入群方式1 - 由成员邀请入群（直接邀请入群）2 - 由成员邀请入群（通过邀请链接入群）3 - 通过扫描群二维码入群\r\n1 - 由成员邀请入群（直接邀请入群）\r\n2 - 由成员邀请入群（通过邀请链接入群）\r\n3 - 通过扫描群二维码入群',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '成员类型（1 - 企业成员 2 - 外部联系人）\r\n1 - 企业成员\r\n2 - 外部联系人',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '成员状态。1 - 正常2 -退群',
  `join_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '入群时间',
  `out_time` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '退群时间',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户 - 客户群 关联表';

-- ----------------------------
-- Table structure for mc_work_contact_tag
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_contact_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '企业标签ID',
  `wx_contact_tag_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信企业标签ID',
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业表ID （mc_corp.id）',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标签名称',
  `order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `contact_tag_group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '客户标签分组ID（mc_work_contract_tag_group.id）',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户标签';

-- ----------------------------
-- Table structure for mc_work_contact_tag_group
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_contact_tag_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wx_group_id` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信企业标签分组ID',
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业表ID （mc_corp.id）',
  `group_name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '客户标签分组名称',
  `order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户标签 - 分组';

-- ----------------------------
-- Table structure for mc_work_contact_tag_pivot
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_contact_tag_pivot` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contact_id` int(10) unsigned NOT NULL COMMENT '客户表ID（work_contact.id）',
  `employee_id` int(11) NOT NULL DEFAULT '0' COMMENT '员工表id（work_employee.id）',
  `contact_tag_id` int(10) unsigned NOT NULL COMMENT '客户标签表ID（work_contact_tag.id）',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '该成员添加此外部联系人所打标签类型, 1-企业设置, 2-用户自定义',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户-标签关联表';

-- ----------------------------
-- Table structure for mc_work_department
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_department` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '部门ID',
  `wx_department_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '微信部门自增ID',
  `corp_id` int(10) unsigned NOT NULL COMMENT '企业表ID（mc_corp.id）',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '部门名称',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父部门ID',
  `wx_parentid` int(10) unsigned NOT NULL COMMENT '微信父部门ID',
  `order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `level` tinyint(4) NOT NULL DEFAULT '0' COMMENT '部门级别',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ' ' COMMENT '父ID路径【#id#-#id#】',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='(通讯录)部门管理';

-- ----------------------------
-- Table structure for mc_work_employee
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_employee` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wx_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'wx.userId',
  `corp_id` int(11) NOT NULL DEFAULT '0' COMMENT '所属企业corpid（mc_corp.id）',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `mobile` char(11) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `position` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '职位信息',
  `gender` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '性别。0表示未定义，1表示男性，2表示女性',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '头像url',
  `thumb_avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '头像缩略图',
  `telephone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '座机',
  `alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '别名',
  `extattr` json DEFAULT NULL COMMENT '扩展属性',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '激活状态: 1=已激活，2=已禁用，4=未激活，5=退出企业',
  `qr_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '员工二维码',
  `external_profile` json DEFAULT NULL COMMENT '员工对外属性',
  `external_position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT '员工对外职位',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '地址',
  `open_user_id` char(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '全局唯一id',
  `wx_main_department_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '微信端主部门ID',
  `main_department_id` int(11) NOT NULL DEFAULT '0' COMMENT '主部门id(mc_work_department.id)',
  `log_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '子账户ID(mc_user.id)',
  `contact_auth` tinyint(1) NOT NULL DEFAULT '2' COMMENT '是否配置外部联系人权限（1.是 2.否）',
  `audit_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '存档状态（0：未开通，1：已开通）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='企业通讯录';

-- ----------------------------
-- Table structure for mc_work_employee_department
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_employee_department` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '通讯录员工(mc_work_department.id)',
  `department_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '通讯录部门ID (mc_work_department.id)',
  `is_leader_in_dept` tinyint(4) NOT NULL DEFAULT '0' COMMENT '所在的部门内是否为上级',
  `order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='(通讯录 - 通讯录部门)中间表';

-- ----------------------------
-- Table structure for mc_work_employee_statistic
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_employee_statistic` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` int(11) DEFAULT NULL,
  `employee_id` int(11) NOT NULL COMMENT '成员id (mc_work_employee.id)',
  `new_apply_cnt` int(11) NOT NULL COMMENT '发起申请数成员通过「搜索手机号」、「扫一扫」、「从微信好友中添加」、「从群聊中添加」、「添加共享、分配给我的客户」、「添加单向、双向删除好友关系的好友」、「从新的联系人推荐中添加」等渠道主动向客户发起的好友申请数量',
  `new_contact_cnt` int(11) NOT NULL COMMENT '新增客户数',
  `chat_cnt` int(11) NOT NULL COMMENT '聊天总数',
  `message_cnt` int(11) NOT NULL COMMENT '发送消息数',
  `reply_percentage` int(11) NOT NULL COMMENT '已回复聊天占比',
  `avg_reply_time` int(11) NOT NULL COMMENT '平均首次回复时长',
  `negative_feedback_cnt` int(11) NOT NULL COMMENT '删除/拉黑成员的客户数',
  `syn_time` timestamp NULL DEFAULT NULL COMMENT '同步时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='成员统计表';

-- ----------------------------
-- Table structure for mc_work_employee_tag
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_employee_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `wx_tagid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '微信通许录标签 id',
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业表ID（mc_corp.id）',
  `tag_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标签名称',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='(通讯录)标签';

-- ----------------------------
-- Table structure for mc_work_employee_tag_pivot
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_employee_tag_pivot` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL COMMENT '通讯录员工ID',
  `tag_id` int(10) unsigned NOT NULL COMMENT 'wx标签ID',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='(通讯录 - 标签)中间表';

-- ----------------------------
-- Table structure for mc_work_fission
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_fission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` int(11) NOT NULL DEFAULT '0' COMMENT '企业表ID（mc_crop.id）',
  `active_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '活动名称',
  `service_employees` json DEFAULT NULL COMMENT '客服成员',
  `auto_pass` tinyint(1) DEFAULT NULL COMMENT '自动通过好友申请',
  `auto_add_tag` tinyint(1) DEFAULT NULL COMMENT '自动添加客户标签',
  `contact_tags` json DEFAULT NULL COMMENT '标签组',
  `end_time` timestamp NULL DEFAULT NULL COMMENT '活动结束时间',
  `qr_code_invalid` int(11) DEFAULT NULL COMMENT '二维码有效期（天）为空则是立即失效',
  `tasks` json DEFAULT NULL COMMENT '裂变任务',
  `new_friend` tinyint(1) DEFAULT NULL COMMENT '必须新好友才能助力',
  `delete_invalid` tinyint(1) DEFAULT NULL COMMENT '删除员工后助力失效',
  `receive_prize` tinyint(1) DEFAULT NULL COMMENT '领奖方式：0联系客服，1兑换链接',
  `receive_prize_employees` json DEFAULT NULL COMMENT '领奖-员工',
  `receive_links` json DEFAULT NULL COMMENT '领奖-兑换链接',
  `receive_qrcode` json DEFAULT NULL COMMENT '领奖-员工二维码',
  `create_user_id` int(11) NOT NULL DEFAULT '0' COMMENT '创建人ID',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='裂变-基础信息主表';

-- ----------------------------
-- Table structure for mc_work_fission_contact
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_fission_contact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fission_id` int(11) NOT NULL DEFAULT '0' COMMENT '活动ID',
  `union_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '客户微信id',
  `nickname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '客户微信昵称',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '客户微信头像',
  `contact_superior_user_parent` int(11) DEFAULT '0' COMMENT '上级（被谁邀请来的）',
  `level` tinyint(1) DEFAULT '0' COMMENT '裂变等级',
  `employee` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '添加的员工',
  `invite_count` int(11) NOT NULL DEFAULT '0' COMMENT '邀请数量',
  `loss` tinyint(1) DEFAULT '0' COMMENT '是否已流失（被删除好友）',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '完成状态。（0：未完成，1：已完成）',
  `receive_level` tinyint(1) DEFAULT '0' COMMENT '已领取奖励阶段',
  `is_new` tinyint(1) NOT NULL DEFAULT '0' COMMENT '新客户（0：老，1：新）',
  `external_user_id` varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '外部联系人external_userid',
  `qrcode_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '二维码ID',
  `qrcode_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '二维码图片链接',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='裂变-客户参与';

-- ----------------------------
-- Table structure for mc_work_fission_invite
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_fission_invite` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fission_id` int(11) NOT NULL DEFAULT '0' COMMENT '活动ID',
  `type` tinyint(1) DEFAULT '2' COMMENT '类型（1：邀请，2：暂不邀请）',
  `text` text COLLATE utf8mb4_unicode_ci COMMENT '邀请文案',
  `link_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邀请链接标题',
  `link_desc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邀请链接描述',
  `link_pic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邀请链接封面图',
  `wx_link_pic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '微信图片地址',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='裂变-邀请客户参与';

-- ----------------------------
-- Table structure for mc_work_fission_poster
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_fission_poster` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fission_id` int(11) NOT NULL DEFAULT '0' COMMENT '活动ID',
  `poster_type` tinyint(1) DEFAULT NULL COMMENT '裂变海报：0海报,1个人名片',
  `cover_pic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '海报背景图片',
  `wx_cover_pic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '背景图片微信地址',
  `foward_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '海报转发话术',
  `avatar_show` tinyint(1) DEFAULT NULL COMMENT '头像是否显示。0：不显示，1：显示',
  `nickname_show` tinyint(1) DEFAULT NULL COMMENT '昵称是否显示。0：不显示，1：显示',
  `nickname_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '颜色',
  `card_corp_image_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '个人名片企业形象名称',
  `card_corp_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '个人名片企业名称',
  `card_corp_logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '个人名片企业logo',
  `qrcode_w` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '二维码宽度',
  `qrcode_h` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '二维码高度',
  `qrcode_x` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '二维码X值',
  `qrcode_y` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '二维码Y值',
  `qrcode_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '二维码ID',
  `qrcode_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '二维码图片链接',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='裂变-海报';

-- ----------------------------
-- Table structure for mc_work_fission_push
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_fission_push` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fission_id` int(11) NOT NULL DEFAULT '0' COMMENT '活动ID',
  `push_employee` tinyint(4) DEFAULT NULL COMMENT '员工推送',
  `push_contact` tinyint(4) DEFAULT NULL COMMENT '客户推送',
  `msg_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '消息1（文字）',
  `msg_complex` json DEFAULT NULL COMMENT '消息2（图片、链接、小程序）',
  `msg_complex_type` varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '消息2类型（image|link|applets）',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='裂变-推送';

-- ----------------------------
-- Table structure for mc_work_fission_welcome
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_fission_welcome` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fission_id` int(11) NOT NULL DEFAULT '0' COMMENT '活动ID',
  `msg_text` text COLLATE utf8mb4_unicode_ci COMMENT '文字欢迎语',
  `link_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '链接标题',
  `link_desc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '链接描述',
  `link_cover_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '链接封面地址',
  `link_wx_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '微信图片地址',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='裂变-欢迎语';

-- ----------------------------
-- Table structure for mc_work_room
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_room` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业表ID（mc_corp.id）',
  `wx_chat_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '客户群ID',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '客户群名称',
  `owner_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '群主ID（work_employee.id）',
  `notice` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '群公告',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '客户群状态（0 - 正常 1 - 跟进人离职 2 - 离职继承中 3 - 离职继承完成）',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '群创建时间',
  `room_max` int(11) NOT NULL DEFAULT '0' COMMENT '群成员上限',
  `room_group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分组id（work_room_group.id）',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户群表';

-- ----------------------------
-- Table structure for mc_work_room_auto_pull
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_room_auto_pull` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` int(10) unsigned NOT NULL COMMENT '企业表ID(mc_corp.id)',
  `qrcode_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '二维码名称',
  `qrcode_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '二维码地址',
  `wx_config_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '二维码凭证',
  `is_verified` tinyint(3) unsigned NOT NULL DEFAULT '2' COMMENT '添加验证 （1:需验证 2:直接通过）',
  `leading_words` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '入群引导语',
  `tags` json NOT NULL COMMENT '群标签 [{`tag_id`: `1`,`type`: 1,`tag_name`:`标签`,group_id:`1` ,group_name`:分组名称}]',
  `employees` json NOT NULL COMMENT '使用成员[{`id`: `1`,name`:`成员`}]',
  `rooms` json NOT NULL COMMENT '群[{`id`: `1`,`type`: 1,`name`:`成员`,room_max:''群上限''}]',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='自动拉群表';

-- ----------------------------
-- Table structure for mc_work_room_group
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_room_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` int(10) unsigned NOT NULL COMMENT '企业表ID（mc_corp.id）',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分组名称',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户群分组管理表';

-- ----------------------------
-- Table structure for mc_work_transfer_log
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_transfer_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` int(11) DEFAULT NULL COMMENT '企业id (corp.id)',
  `status` tinyint(1) DEFAULT NULL COMMENT '客服类型：1离职分配 2在职分配',
  `type` tinyint(1) DEFAULT NULL COMMENT '分配类型：1客户转接 2群聊转接',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '客户/群聊 名称',
  `contact_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '客户/群聊 WxId',
  `handover_employee_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '原客服的WxId',
  `takeover_employee_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '接替的客服的WxId',
  `state` tinyint(1) DEFAULT NULL COMMENT '转接状态：1接替完毕 2等待接替 3客户拒绝 4接替成员客户达到上限 5无接替记录',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户/群聊 分配记录表';

-- ----------------------------
-- Table structure for mc_work_unassigned
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_unassigned` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` int(11) NOT NULL COMMENT '企业id(corp.id)',
  `handover_userid` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '离职成员的userid',
  `external_userid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '外部联系人userid',
  `dimission_time` int(11) NOT NULL COMMENT '成员离职时间',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='离职成员-客户存储表';

-- ----------------------------
-- Table structure for mc_work_unionid_external_userid_mapping_0
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_unionid_external_userid_mapping_0` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业id',
  `unionid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人在微信开放平台的唯一身份标识（微信unionid）',
  `openid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信公众号的openid',
  `external_userid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人external_userid',
  `pending_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subject_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '主体类型：0表示主体是企业, 1表示主体是服务商',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuo-userid` (`corp_id`,`unionid`,`openid`,`external_userid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='联系人unionid-external_userid映射表';

-- ----------------------------
-- Table structure for mc_work_unionid_external_userid_mapping_1
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_unionid_external_userid_mapping_1` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业id',
  `unionid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人在微信开放平台的唯一身份标识（微信unionid）',
  `openid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信公众号的openid',
  `external_userid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人external_userid',
  `pending_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subject_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '主体类型：0表示主体是企业, 1表示主体是服务商',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuo-userid` (`corp_id`,`unionid`,`openid`,`external_userid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='联系人unionid-external_userid映射表';

-- ----------------------------
-- Table structure for mc_work_unionid_external_userid_mapping_2
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_unionid_external_userid_mapping_2` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业id',
  `unionid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人在微信开放平台的唯一身份标识（微信unionid）',
  `openid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信公众号的openid',
  `external_userid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人external_userid',
  `pending_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subject_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '主体类型：0表示主体是企业, 1表示主体是服务商',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuo-userid` (`corp_id`,`unionid`,`openid`,`external_userid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='联系人unionid-external_userid映射表';

-- ----------------------------
-- Table structure for mc_work_unionid_external_userid_mapping_3
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_unionid_external_userid_mapping_3` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业id',
  `unionid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人在微信开放平台的唯一身份标识（微信unionid）',
  `openid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信公众号的openid',
  `external_userid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人external_userid',
  `pending_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subject_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '主体类型：0表示主体是企业, 1表示主体是服务商',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuo-userid` (`corp_id`,`unionid`,`openid`,`external_userid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='联系人unionid-external_userid映射表';

-- ----------------------------
-- Table structure for mc_work_unionid_external_userid_mapping_4
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_unionid_external_userid_mapping_4` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业id',
  `unionid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人在微信开放平台的唯一身份标识（微信unionid）',
  `openid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信公众号的openid',
  `external_userid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人external_userid',
  `pending_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subject_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '主体类型：0表示主体是企业, 1表示主体是服务商',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuo-userid` (`corp_id`,`unionid`,`openid`,`external_userid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='联系人unionid-external_userid映射表';

-- ----------------------------
-- Table structure for mc_work_unionid_external_userid_mapping_5
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_unionid_external_userid_mapping_5` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业id',
  `unionid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人在微信开放平台的唯一身份标识（微信unionid）',
  `openid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信公众号的openid',
  `external_userid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人external_userid',
  `pending_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subject_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '主体类型：0表示主体是企业, 1表示主体是服务商',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuo-userid` (`corp_id`,`unionid`,`openid`,`external_userid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='联系人unionid-external_userid映射表';

-- ----------------------------
-- Table structure for mc_work_unionid_external_userid_mapping_6
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_unionid_external_userid_mapping_6` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业id',
  `unionid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人在微信开放平台的唯一身份标识（微信unionid）',
  `openid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信公众号的openid',
  `external_userid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人external_userid',
  `pending_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subject_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '主体类型：0表示主体是企业, 1表示主体是服务商',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuo-userid` (`corp_id`,`unionid`,`openid`,`external_userid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='联系人unionid-external_userid映射表';

-- ----------------------------
-- Table structure for mc_work_unionid_external_userid_mapping_7
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_unionid_external_userid_mapping_7` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业id',
  `unionid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人在微信开放平台的唯一身份标识（微信unionid）',
  `openid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信公众号的openid',
  `external_userid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人external_userid',
  `pending_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subject_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '主体类型：0表示主体是企业, 1表示主体是服务商',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuo-userid` (`corp_id`,`unionid`,`openid`,`external_userid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='联系人unionid-external_userid映射表';

-- ----------------------------
-- Table structure for mc_work_unionid_external_userid_mapping_8
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_unionid_external_userid_mapping_8` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业id',
  `unionid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人在微信开放平台的唯一身份标识（微信unionid）',
  `openid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信公众号的openid',
  `external_userid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人external_userid',
  `pending_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subject_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '主体类型：0表示主体是企业, 1表示主体是服务商',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuo-userid` (`corp_id`,`unionid`,`openid`,`external_userid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='联系人unionid-external_userid映射表';

-- ----------------------------
-- Table structure for mc_work_unionid_external_userid_mapping_9
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_unionid_external_userid_mapping_9` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `corp_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '企业id',
  `unionid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人在微信开放平台的唯一身份标识（微信unionid）',
  `openid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '微信公众号的openid',
  `external_userid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '外部联系人external_userid',
  `pending_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subject_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '主体类型：0表示主体是企业, 1表示主体是服务商',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cuo-userid` (`corp_id`,`unionid`,`openid`,`external_userid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='联系人unionid-external_userid映射表';

-- ----------------------------
-- Table structure for mc_work_update_time
-- ----------------------------
CREATE TABLE IF NOT EXISTS `mc_work_update_time` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corp_id` int(11) NOT NULL DEFAULT '0' COMMENT '企业表ID（mc_crop.id）',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '类型（1.通讯录，2.客户，3.标签，4.部门 5.会放内容存档 6.企业数据）',
  `last_update_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '最后一次同步时间',
  `error_msg` json DEFAULT NULL COMMENT '错误信息',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='同步时间表';


-- ----------------------------
-- 高级属性
-- ----------------------------
INSERT INTO `mc_contact_field` (`id`, `name`, `label`, `type`, `options`, `order`, `status`, `is_sys`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,'phone','手机号',9,'[]',0,1,1,'2020-12-29 11:39:45',NULL,NULL);
INSERT INTO `mc_contact_field` (`id`, `name`, `label`, `type`, `options`, `order`, `status`, `is_sys`, `created_at`, `updated_at`, `deleted_at`) VALUES (2,'name','姓名',0,'[]',0,1,1,'2020-12-29 11:39:45',NULL,NULL);
INSERT INTO `mc_contact_field` (`id`, `name`, `label`, `type`, `options`, `order`, `status`, `is_sys`, `created_at`, `updated_at`, `deleted_at`) VALUES (3,'gender','性别',1,'[\"男\", \"女\", \"未知\"]',0,1,1,'2020-12-29 11:39:45',NULL,NULL);
INSERT INTO `mc_contact_field` (`id`, `name`, `label`, `type`, `options`, `order`, `status`, `is_sys`, `created_at`, `updated_at`, `deleted_at`) VALUES (4,'birthday','生日',6,'[]',0,1,1,'2020-12-29 11:39:45',NULL,NULL);
INSERT INTO `mc_contact_field` (`id`, `name`, `label`, `type`, `options`, `order`, `status`, `is_sys`, `created_at`, `updated_at`, `deleted_at`) VALUES (5,'age','年龄',8,'[]',0,1,1,'2020-12-29 11:39:45',NULL,NULL);
INSERT INTO `mc_contact_field` (`id`, `name`, `label`, `type`, `options`, `order`, `status`, `is_sys`, `created_at`, `updated_at`, `deleted_at`) VALUES (6,'QQ','QQ',0,'[]',0,1,1,'2020-12-29 11:39:45',NULL,NULL);
INSERT INTO `mc_contact_field` (`id`, `name`, `label`, `type`, `options`, `order`, `status`, `is_sys`, `created_at`, `updated_at`, `deleted_at`) VALUES (7,'email','邮箱',10,'[]',0,1,1,'2020-12-29 11:39:45',NULL,NULL);
INSERT INTO `mc_contact_field` (`id`, `name`, `label`, `type`, `options`, `order`, `status`, `is_sys`, `created_at`, `updated_at`, `deleted_at`) VALUES (8,'hobby','爱好',2,'[\"游戏\", \"阅读\", \"音乐\", \"运动\", \"动漫\", \"旅行\", \"家居\", \"曲艺\", \"宠物\", \"美食\", \"娱乐\", \"电影\", \"电视剧\", \"健康养生\", \"数码\", \"其他\"]',0,1,1,'2020-12-29 11:39:45',NULL,NULL);
INSERT INTO `mc_contact_field` (`id`, `name`, `label`, `type`, `options`, `order`, `status`, `is_sys`, `created_at`, `updated_at`, `deleted_at`) VALUES (9,'education','学历',3,'[\"博士\", \"硕士\", \"大学\", \"大专\", \"高中\", \"初中\", \"小学\", \"其他\"]',0,1,1,'2020-12-29 11:39:45',NULL,NULL);
INSERT INTO `mc_contact_field` (`id`, `name`, `label`, `type`, `options`, `order`, `status`, `is_sys`, `created_at`, `updated_at`, `deleted_at`) VALUES (10,'annualIncome','年收入',3,'[\"5万以下\", \"5万-15万\", \"15万-30万\", \"30万以上\", \"50-100万\", \"100万-200万\", \"200万-500万\", \"500万-1000万\", \"1000万-5000万\"]',0,1,1,'2020-12-29 11:39:45',NULL,NULL);
INSERT INTO `mc_contact_field` (`id`, `name`, `label`, `type`, `options`, `order`, `status`, `is_sys`, `created_at`, `updated_at`, `deleted_at`) VALUES (11,'zhu4zhi3','住址',5,'\"\"',0,1,0,'2022-11-14 10:43:41',NULL,NULL);
INSERT INTO `mc_contact_field` (`id`, `name`, `label`, `type`, `options`, `order`, `status`, `is_sys`, `created_at`, `updated_at`, `deleted_at`) VALUES (12,'123','123',0,'\"\"',0,1,0,'2022-12-14 07:37:55',NULL,NULL);

-- ----------------------------
-- 工具栏
-- ----------------------------
INSERT INTO `mc_chat_tool` (`id`, `page_name`, `page_flag`, `created_at`, `updated_at`, `deleted_at`, `status`) VALUES (1,'客户画像','contact','2020-12-30 09:12:02',NULL,NULL,1);
INSERT INTO `mc_chat_tool` (`id`, `page_name`, `page_flag`, `created_at`, `updated_at`, `deleted_at`, `status`) VALUES (2,'素材库','medium','2020-12-30 09:12:02',NULL,NULL,1);

-- ----------------------------
-- 菜单
-- ----------------------------
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1', '0', '企微管理', '1', '#1#', '', '1', '1', '1', '/dashboard_baseSysManager', '2', '0', '系统', '99', '2020-12-31 19:22:04', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('2', '1', '引流获客', '2', '#1#-#2#', 'line-chart', '1', '1', '1', '/dashboard_baseShunt', '2', '0', '系统', '2', '2020-12-31 19:22:05', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('3', '2', '渠道活码', '3', '#1#-#2#-#3#', '', '1', '1', '1', '/dashboard/channelCode/index', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('4', '3', '新建渠道码', '4', '#1#-#2#-#3#-#4#', '', '1', '1', '1', '/dashboard/channelCode/store', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:29', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('5', '4', '确定(按钮)', '5', '#1#-#2#-#3#-#4#-#5#', '', '1', '1', '1', '/dashboard/channelCode/store@confirm', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:29', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('6', '3', '统计(按钮)', '4', '#1#-#2#-#3#-#6#', '', '1', '1', '1', '/dashboard/channelCode/statistics', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:29', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('7', '3', '查询(按钮)', '4', '#1#-#2#-#3#-#7#', '', '1', '1', '1', '/dashboard/channelCode/index@search', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:29', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('8', '3', '修改分组(按钮)', '4', '#1#-#2#-#3#-#8#', '', '1', '1', '1', '/dashboard/channelCode/index@editGroup', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:29', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('9', '3', '新建分组(按钮)', '4', '#1#-#2#-#3#-#9#', '', '1', '1', '1', '/dashboard/channelCode/index@add', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:29', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('10', '3', '编辑(按钮)', '4', '#1#-#2#-#3#-#10#', '', '1', '1', '1', '/dashboard/channelCode/index@edit', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:29', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('11', '3', '客户(按钮)', '4', '#1#-#2#-#3#-#11#', '', '1', '1', '1', '/dashboard/channelCode/index@customer', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:29', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('12', '3', '下载(按钮)', '4', '#1#-#2#-#3#-#12#', '', '1', '1', '1', '/dashboard/channelCode/index@download', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:29', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('13', '3', '移动(按钮)', '4', '#1#-#2#-#3#-#13#', '', '1', '1', '1', '/dashboard/channelCode/index@move', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:29', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('14', '1', '客户转化', '2', '#1#-#14#', 'pie-chart', '1', '1', '1', '/dashboard/contact/transfer', '2', '0', '系统', '4', '2020-12-31 19:22:05', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('15', '14', '好友欢迎语', '3', '#1#-#14#-#15#', '', '1', '1', '1', '/dashboard/greeting/index', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('16', '15', '新建欢迎语', '4', '#1#-#14#-#15#-#16#', '', '1', '1', '1', '/dashboard/greeting/store', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('17', '16', '创建欢迎语(按钮)', '5', '#1#-#14#-#15#-#16#-#17#', '', '1', '1', '1', '/dashboard/greeting/store@add', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('18', '15', '编辑(按钮)', '4', '#1#-#14#-#15#-#18#', '', '1', '1', '1', '/dashboard/greeting/index@edit', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('19', '15', '删除(按钮)', '4', '#1#-#14#-#15#-#19#', '', '1', '1', '1', '/dashboard/greeting/index@delete', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('20', '14', '素材库', '3', '#1#-#14#-#20#', '', '1', '1', '1', '/dashboard/mediumGroup/index', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('21', '20', '修改分组(按钮)', '4', '#1#-#14#-#20#-#21#', '', '1', '1', '1', '/dashboard/mediumGroup/index@editGroup', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('22', '20', '新增分组(按钮)', '4', '#1#-#14#-#20#-#22#', '', '1', '1', '1', '/dashboard/mediumGroup/index@addGroup', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('23', '20', '编辑(按钮)', '4', '#1#-#14#-#20#-#23#', '', '1', '1', '1', '/dashboard/mediumGroup/index@edit', '2', '0', '系统', '99', '2020-12-31 19:22:05', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('24', '20', '移动(按钮)', '4', '#1#-#14#-#20#-#24#', '', '1', '1', '1', '/dashboard/mediumGroup/index@move', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('25', '20', '删除(按钮)', '4', '#1#-#14#-#20#-#25#', '', '1', '1', '1', '/dashboard/mediumGroup/index@delete', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('26', '20', '添加(按钮)', '4', '#1#-#14#-#20#-#26#', '', '1', '1', '1', '/dashboard/mediumGroup/index@add', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('27', '1', '客户管理', '2', '#1#-#27#', 'solution', '1', '1', '1', '/dashboard_baseContactManage', '2', '0', '系统', '5', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('28', '27', '客户列表', '3', '#1#-#27#-#28#', '', '1', '1', '1', '/dashboard/workContact/index', '2', '0', '系统', '1', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('29', '28', '客户详情', '4', '#1#-#27#-#28#-#29#', '', '1', '1', '1', '/dashboard/workContact/contactFieldPivot', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('30', '29', '互动轨迹(按钮)', '5', '#1#-#27#-#28#-#29#-#30#', '', '1', '1', '1', '/dashboard/workContact/contactFieldPivot@track', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('31', '29', '用户画像(按钮)', '5', '#1#-#27#-#28#-#29#-#31#', '', '1', '1', '1', '/dashboard/workContact/contactFieldPivot@detail', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('32', '29', '编辑(按钮)', '5', '#1#-#27#-#28#-#29#-#32#', '', '1', '1', '1', '/dashboard/workContact/contactFieldPivot@edit', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('33', '29', '编辑基础信息(按钮)', '5', '#1#-#27#-#28#-#29#-#33#', '', '1', '1', '1', '/dashboard/workContact/contactFieldPivot@update', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('34', '28', '查询(按钮)', '4', '#1#-#27#-#28#-#34#', '', '1', '1', '1', '/dashboard/workContact/index@search', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('35', '28', '同步客户(按钮)', '4', '#1#-#27#-#28#-#35#', '', '1', '1', '1', '/dashboard/workContact/index@sync', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('36', '27', '客户资料卡', '3', '#1#-#27#-#36#', '', '1', '1', '1', '/dashboard/contactField/index', '2', '0', '系统', '3', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('37', '36', '高级属性(按钮)', '4', '#1#-#27#-#36#-#37#', '', '1', '1', '1', '/dashboard/contactField/index@advanced', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('38', '36', '全部状态(按钮)', '4', '#1#-#27#-#36#-#38#', '', '1', '1', '1', '/dashboard/contactField/index@all', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('39', '36', '新增属性(按钮)', '4', '#1#-#27#-#36#-#39#', '', '1', '1', '1', '/dashboard/contactField/index@add', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('40', '36', '批量修改(按钮)', '4', '#1#-#27#-#36#-#40#', '', '1', '1', '1', '/dashboard/contactField/index@batch', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('41', '36', '编辑(按钮)', '4', '#1#-#27#-#36#-#41#', '', '1', '1', '1', '/dashboard/contactField/index@edit', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('42', '36', '关闭(按钮)', '4', '#1#-#27#-#36#-#42#', '', '1', '1', '1', '/dashboard/contactField/index@close', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('43', '27', '流失提醒', '3', '#1#-#27#-#43#', '', '1', '1', '1', '/dashboard/lossContact/index', '2', '0', '系统', '4', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('44', '43', '选择部门成员(按钮)', '4', '#1#-#27#-#43#-#44#', '', '1', '1', '1', '/dashboard/lossContact/index@choose', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('45', '27', '客户标签', '3', '#1#-#27#-#45#', '', '1', '1', '1', '/dashboard/workContactTag/index', '2', '0', '系统', '2', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('46', '45', '同步企业微信通讯录(按钮)', '4', '#1#-#27#-#45#-#46#', '', '1', '1', '1', '/dashboard/workContactTag/index@sync', '2', '0', '系统', '99', '2020-12-31 19:22:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('47', '45', '修改分组(按钮)', '4', '#1#-#27#-#45#-#47#', '', '1', '1', '1', '/dashboard/workContactTag/index@editGroup', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('48', '45', '新增分组(按钮)', '4', '#1#-#27#-#45#-#48#', '', '1', '1', '1', '/dashboard/workContactTag/index@addGroup', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('49', '45', '删除标签(按钮)', '4', '#1#-#27#-#45#-#49#', '', '1', '1', '1', '/dashboard/workContactTag/index@deleteTag', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('50', '45', '新建标签(按钮)', '4', '#1#-#27#-#45#-#50#', '', '1', '1', '1', '/dashboard/workContactTag/index@add', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('51', '45', '编辑(按钮)', '4', '#1#-#27#-#45#-#51#', '', '1', '1', '1', '/dashboard/workContactTag/index@edit', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('52', '45', '删除(按钮)', '4', '#1#-#27#-#45#-#52#', '', '1', '1', '1', '/dashboard/workContactTag/index@delete', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('53', '45', '移动标签(按钮)', '4', '#1#-#27#-#45#-#53#', '', '1', '1', '1', '/dashboard/workContactTag/index@move', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('54', '1', '客户群运营', '2', '#1#-#54#', 'team', '1', '1', '1', '/dashboard_baseContactRoomManage', '2', '0', '系统', '7', '2020-12-31 19:22:07', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('55', '54', '客户群列表', '3', '#1#-#54#-#55#', '', '1', '1', '1', '/dashboard/workRoom/index', '2', '0', '系统', '1', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('56', '55', '群统计', '4', '#1#-#54#-#55#-#56#', '', '1', '1', '1', '/dashboard/workRoom/statistics', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('57', '55', '增加分组(按钮)', '4', '#1#-#54#-#55#-#57#', '', '1', '1', '1', '/dashboard/workRoom/index@add', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('58', '55', '编辑分组(按钮)', '4', '#1#-#54#-#55#-#58#', '', '1', '1', '1', '/dashboard/workRoom/index@edit', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('59', '55', '查找(按钮)', '4', '#1#-#54#-#55#-#59#', '', '1', '1', '1', '/dashboard/workRoom/index@search', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('60', '55', '批量修改分组(按钮)', '4', '#1#-#54#-#55#-#60#', '', '1', '1', '1', '/dashboard/workRoom/index@batch', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('61', '55', '同步群(按钮)', '4', '#1#-#54#-#55#-#61#', '', '1', '1', '1', '/dashboard/workRoom/index@sync', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('62', '55', '群成员(按钮)', '4', '#1#-#54#-#55#-#62#', '', '1', '1', '1', '/dashboard/workRoom/index@member', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('63', '55', '移动分组(按钮)', '4', '#1#-#54#-#55#-#63#', '', '1', '1', '1', '/dashboard/workRoom/index@move', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('64', '54', '自动拉群', '3', '#1#-#54#-#64#', '', '1', '1', '1', '/dashboard/workRoomAutoPull/index', '2', '0', '系统', '5', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('65', '64', '新建拉群', '4', '#1#-#54#-#64#-#65#', '', '1', '1', '1', '/dashboard/workRoomAutoPull/store', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('66', '65', '提交(按钮)', '5', '#1#-#54#-#64#-#65#-#66#', '', '1', '1', '1', '/dashboard/workRoomAutoPull/store@submit', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('67', '64', '查找(按钮)', '4', '#1#-#54#-#64#-#67#', '', '1', '1', '1', '/dashboard/workRoomAutoPull/index@search', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('68', '64', '详情(按钮)', '4', '#1#-#54#-#64#-#68#', '', '1', '1', '1', '/dashboard/workRoomAutoPull/index@detail', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('69', '64', '编辑(按钮)', '4', '#1#-#54#-#64#-#69#', '', '1', '1', '1', '/dashboard/workRoomAutoPull/index@edit', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('70', '64', '下载(按钮)', '4', '#1#-#54#-#64#-#70#', '', '1', '1', '1', '/dashboard/workRoomAutoPull/index@download', '2', '0', '系统', '99', '2020-12-31 19:22:07', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('71', '1', '聊天侧边栏', '2', '#1#-#71#', 'message', '1', '1', '1', '/dashboard_chatTool', '2', '0', '系统', '8', '2020-12-31 19:22:08', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('94', '0', '系统设置', '1', '#94#', '', '1', '1', '1', '/dashboard_baseSysIndex', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('95', '94', '企业管理', '2', '#94#-#95#', 'cluster', '1', '1', '1', '/dashboard_baseSysConfig', '2', '0', '系统', '1', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('96', '95', '成员管理', '3', '#94#-#95#-#96#', '', '1', '1', '1', '/dashboard/workEmployee/index', '2', '0', '系统', '1', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('97', '96', '条件筛选(按钮)', '4', '#94#-#95#-#96#-#97#', '', '1', '1', '1', '/dashboard/workEmployee/index@search', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('98', '96', '同步企业微信通讯录(按钮)', '4', '#94#-#95#-#96#-#98#', '', '1', '1', '1', '/dashboard/workEmployee/index@sync', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('99', '95', '账号管理', '3', '#94#-#95#-#99#', '', '1', '1', '1', '/dashboard/user/index', '2', '0', '系统', '2', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('100', '99', '查询(按钮)', '4', '#94#-#95#-#99#-#100#', '', '1', '1', '1', '/dashboard/user/index@search', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('101', '99', '添加(按钮)', '4', '#94#-#95#-#99#-#101#', '', '1', '1', '1', '/dashboard/user/index@add', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('102', '99', '修改(按钮)', '4', '#94#-#95#-#99#-#102#', '', '1', '1', '1', '/dashboard/user/index@edit', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('103', '95', '修改密码', '3', '#94#-#95#-#103#', '', '1', '1', '1', '/dashboard/passwordUpdate/index', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('104', '103', '保存(按钮)', '4', '#94#-#95#-#103#-#104#', '', '1', '1', '1', '/dashboard/passwordUpdate/index@save', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('105', '95', '授权管理', '3', '#94#-#95#-#105#', '', '1', '1', '1', '/dashboard/corp/index', '2', '0', '系统', '3', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('106', '105', '查找(按钮)', '4', '#94#-#95#-#105#-#106#', '', '1', '1', '1', '/dashboard/corp/index@search', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('107', '105', '添加企业微信号(按钮)', '4', '#94#-#95#-#105#-#107#', '', '1', '1', '1', '/dashboard/corp/index@addwx', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('108', '105', '查看(按钮)', '4', '#94#-#95#-#105#-#108#', '', '1', '1', '1', '/dashboard/corp/index@check', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('109', '105', '修改(按钮)', '4', '#94#-#95#-#105#-#109#', '', '1', '1', '1', '/dashboard/corp/index@edit', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('110', '95', '角色管理', '3', '#94#-#95#-#110#', '', '1', '1', '1', '/dashboard/role/index', '2', '0', '系统', '4', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('111', '110', '权限设置', '4', '#94#-#95#-#110#-#111#', '', '1', '1', '1', '/dashboard/role/permissionShow', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('112', '111', '保存权限(按钮)', '5', '#94#-#95#-#110#-#111#-#112#', '', '1', '1', '1', '/dashboard/role/permissionShow@save', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('113', '110', '查询(按钮)', '4', '#94#-#95#-#110#-#113#', '', '1', '1', '1', '/dashboard/role/index@search', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('114', '110', '添加(按钮)', '4', '#94#-#95#-#110#-#114#', '', '1', '1', '1', '/dashboard/role/index@add', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('115', '110', '编辑(按钮)', '4', '#94#-#95#-#110#-#115#', '', '1', '1', '1', '/dashboard/role/index@edit', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('116', '110', '复制权限(按钮)', '4', '#94#-#95#-#110#-#116#', '', '1', '1', '1', '/dashboard/role/index@copy', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('117', '110', '删除(按钮)', '4', '#94#-#95#-#110#-#117#', '', '1', '1', '1', '/dashboard/role/index@delete', '2', '0', '系统', '99', '2020-12-31 19:22:09', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('118', '110', '查看角色人员(按钮)', '4', '#94#-#95#-#110#-#118#', '', '1', '1', '1', '/dashboard/role/index@checkMember', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('119', '110', '启动(按钮)', '4', '#94#-#95#-#110#-#119#', '', '1', '1', '1', '/dashboard/role/index@use', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('120', '95', '菜单管理', '3', '#94#-#95#-#120#', '', '1', '1', '1', '/dashboard/menu/index', '2', '0', '系统', '5', '2020-12-31 19:22:10', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('121', '120', '查询(按钮)', '4', '#94#-#95#-#120#-#121#', '', '1', '1', '1', '/dashboard/menu/index@search', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('122', '120', '添加(按钮)', '4', '#94#-#95#-#120#-#122#', '', '1', '1', '1', '/dashboard/menu/index@add', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('123', '120', '编辑(按钮)', '4', '#94#-#95#-#120#-#123#', '', '1', '1', '1', '/dashboard/menu/index@edit', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('124', '95', '组织架构', '3', '#94#-#95#-#124#', '', '1', '1', '1', '/dashboard/department/index', '2', '0', '系统', '6', '2020-12-31 19:22:10', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('125', '124', '查询(按钮)', '4', '#94#-#95#-#124#-#125#', '', '1', '1', '1', '/dashboard/department/index@search', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('126', '124', '查看成员(按钮)', '4', '#94#-#95#-#124#-#126#', '', '1', '1', '1', '/dashboard/department/index@check', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('127', '124', '同步企业微信通讯录(按钮)', '4', '#94#-#95#-#124#-#127#', '', '1', '1', '1', '/dashboard/department/index@sync', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('128', '20', '素材列表操作', '4', '#1#-#14#-#20#-#128#', '', '1', '1', '2', '/dashboard/medium/index#get', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('129', '20', '素材详情操作', '4', '#1#-#14#-#20#-#129#', '', '1', '1', '2', '/dashboard/medium/show#get', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('130', '20', '素材添加操作', '4', '#1#-#14#-#20#-#130#', '', '1', '1', '2', '/dashboard/medium/store#post', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('131', '20', '素材修改操作', '4', '#1#-#14#-#20#-#131#', '', '1', '1', '2', '/dashboard/medium/update#put', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('132', '20', '素材删除操作', '4', '#1#-#14#-#20#-#132#', '', '1', '1', '2', '/dashboard/medium/destroy#delete', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('133', '20', '素材分组详情操作', '4', '#1#-#14#-#20#-#133#', '', '1', '1', '2', '/dashboard/mediumGroup/show#get', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('134', '20', '素材分组列表操作', '4', '#1#-#14#-#20#-#134#', '', '1', '1', '2', '/dashboard/mediumGroup/index#get', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('135', '20', '素材分组删除操作', '4', '#1#-#14#-#20#-#135#', '', '1', '1', '2', '/dashboard/mediumGroup/destroy#delete', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('136', '20', '素材分组添加操作', '4', '#1#-#14#-#20#-#136#', '', '1', '1', '2', '/dashboard/mediumGroup/store#post', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('137', '20', '素材移动分组操作', '4', '#1#-#14#-#20#-#137#', '', '1', '1', '2', '/dashboard/medium/groupUpdate#put', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('138', '20', '素材分组修改操作', '4', '#1#-#14#-#20#-#138#', '', '1', '1', '2', '/dashboard/mediumGroup/update#put', '2', '0', '系统', '99', '2020-12-31 19:22:10', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('143', '105', '企业授信列表操作', '4', '#94#-#95#-#105#-#143#', '', '1', '1', '2', '/dashboard/corp/index#get', '1', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('144', '105', '新建企业微信授信操作', '4', '#94#-#95#-#105#-#144#', '', '1', '1', '2', '/dashboard/corp/store#post', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('145', '105', '查看企业授信操作', '4', '#94#-#95#-#105#-#145#', '', '1', '1', '2', '/dashboard/corp/show#get', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('146', '105', '更新企业微信授信操作', '4', '#94#-#95#-#105#-#146#', '', '1', '1', '2', '/dashboard/corp/update#put', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('147', '99', '子账户列表操作', '4', '#94#-#95#-#99#-#147#', '', '1', '1', '2', '/dashboard/user/index#get', '1', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('148', '99', '子账户详情操作', '4', '#94#-#95#-#99#-#148#', '', '1', '1', '2', '/dashboard/user/show#get', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('149', '99', '新建子账户操作', '4', '#94#-#95#-#99#-#149#', '', '1', '1', '2', '/dashboard/user/store#post', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('150', '99', '更新子账户操作', '4', '#94#-#95#-#99#-#150#', '', '1', '1', '2', '/dashboard/user/update#put', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('151', '56', '统计分页操作', '5', '#1#-#54#-#55#-#56#-#151#', '', '1', '1', '2', '/dashboard/workRoom/statisticsIndex#get', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('152', '56', '统计折线图操作', '5', '#1#-#54#-#55#-#56#-#152#', '', '1', '1', '2', '/dashboard/workRoom/statistics#get', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('153', '55', '客户群列表操作', '4', '#1#-#54#-#55#-#153#', '', '1', '1', '2', '/dashboard/workRoom/index#get', '1', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('154', '55', '同步群操作', '4', '#1#-#54#-#55#-#154#', '', '1', '1', '2', '/dashboard/workRoom/syn#put', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('155', '55', '群成员操作', '4', '#1#-#54#-#55#-#155#', '', '1', '1', '2', '/dashboard/workContactRoom/index#get', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('156', '55', '批量修改分组操作', '4', '#1#-#54#-#55#-#156#', '', '1', '1', '2', '/dashboard/workRoom/batchUpdate#put', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('157', '65', '新建自动拉群操作', '5', '#1#-#54#-#64#-#65#-#157#', '', '1', '1', '2', '/dashboard/workRoomAutoPull/store#post', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('158', '64', '自动拉群列表操作', '4', '#1#-#54#-#64#-#158#', '', '1', '1', '2', '/dashboard/workRoomAutoPull/index#get', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('159', '64', '自动拉群详情操作', '4', '#1#-#54#-#64#-#159#', '', '1', '1', '2', '/dashboard/workRoomAutoPull/show#get', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('160', '64', '更新自动拉群操作', '4', '#1#-#54#-#64#-#160#', '', '1', '1', '2', '/dashboard/workRoomAutoPull/update#put', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('163', '15', '欢迎语列表操作', '4', '#1#-#14#-#15#-#163#', '', '1', '1', '2', '/dashboard/greeting/index#get', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('164', '15', '欢迎语详情操作', '4', '#1#-#14#-#15#-#164#', '', '1', '1', '2', '/dashboard/greeting/show#get', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('165', '15', '更新欢迎语操作', '4', '#1#-#14#-#15#-#165#', '', '1', '1', '2', '/dashboard/greeting/update#put', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('166', '15', '删除欢迎语操作', '4', '#1#-#14#-#15#-#166#', '', '1', '1', '2', '/dashboard/greeting/destroy#delete', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('167', '16', '创建欢迎语操作', '5', '#1#-#14#-#15#-#16#-#167#', '', '1', '1', '2', '/dashboard/greeting/store#post', '2', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('168', '28', '客户列表操作', '4', '#1#-#27#-#28#-#168#', '', '1', '1', '2', '/dashboard/workContact/index#get', '1', '0', '系统', '99', '2020-12-31 19:22:11', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('169', '28', '同步客户操作', '4', '#1#-#27#-#28#-#169#', '', '1', '1', '2', '/dashboard/workContact/synContact#put', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('170', '29', '查看详情操作', '5', '#1#-#27#-#28#-#29#-#170#', '', '1', '1', '2', '/dashboard/workContact/show#get', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('171', '29', '编辑基础信息操作', '5', '#1#-#27#-#28#-#29#-#171#', '', '1', '1', '2', '/dashboard/workContact/update#put', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('172', '29', '互动轨迹操作', '5', '#1#-#27#-#28#-#29#-#172#', '', '1', '1', '2', '/dashboard/workContact/track#get', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('173', '29', '用户画像操作', '5', '#1#-#27#-#28#-#29#-#173#', '', '1', '1', '2', '/dashboard/contactFieldPivot/index#get', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('174', '29', '编辑用户画像操作', '5', '#1#-#27#-#28#-#29#-#174#', '', '1', '1', '2', '/dashboard/contactFieldPivot/update#put', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('175', '36', '高级属性列表操作', '4', '#1#-#27#-#36#-#175#', '', '1', '1', '2', '/dashboard/contactField/index#get', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('176', '36', '新增属性操作', '4', '#1#-#27#-#36#-#176#', '', '1', '1', '2', '/dashboard/contactField/store#post', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('177', '36', '批量修改操作', '4', '#1#-#27#-#36#-#177#', '', '1', '1', '2', '/dashboard/contactField/batchUpdate#put', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('178', '36', '编辑操作', '4', '#1#-#27#-#36#-#178#', '', '1', '1', '2', '/dashboard/contactField/update#put', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('179', '36', '删除操作', '4', '#1#-#27#-#36#-#179#', '', '1', '1', '2', '/dashboard/contactField/destroy#delete', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('180', '36', '关闭操作', '4', '#1#-#27#-#36#-#180#', '', '1', '1', '2', '/dashboard/contactField/statusUpdate#put', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('181', '43', '流失客户操作', '4', '#1#-#27#-#43#-#181#', '', '1', '1', '2', '/dashboard/workContact/lossContact#get', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('182', '45', '同步企业微信标签操作', '4', '#1#-#27#-#45#-#182#', '', '1', '1', '2', '/dashboard/workContactTag/synContactTag#put', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('183', '45', '标签操作', '4', '#1#-#27#-#45#-#183#', '', '1', '1', '2', '/dashboard/workContactTag/destroy#delete', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('184', '3', '渠道码列表操作', '4', '#1#-#2#-#3#-#184#', '', '1', '1', '2', '/dashboard/channelCode/index#get', '1', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:29', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('185', '3', '渠道码详情操作', '4', '#1#-#2#-#3#-#185#', '', '1', '1', '2', '/dashboard/channelCode/show#get', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:29', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('186', '3', '编辑渠道码操作', '4', '#1#-#2#-#3#-#186#', '', '1', '1', '2', '/dashboard/channelCode/update#put', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:29', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('187', '3', '客户操作', '4', '#1#-#2#-#3#-#187#', '', '1', '1', '2', '/dashboard/channelCode/contact#get', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('188', '4', '新建渠道码操作', '5', '#1#-#2#-#3#-#4#-#188#', '', '1', '1', '2', '/dashboard/channelCode/store#post', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:29', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('189', '6', '统计分页数据操作', '5', '#1#-#2#-#3#-#6#-#189#', '', '1', '1', '2', '/dashboard/channelCode/statisticsIndex#get', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:29', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('190', '6', '统计折线图操作', '5', '#1#-#2#-#3#-#6#-#190#', '', '1', '1', '2', '/dashboard/channelCode/statistics#get', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:29', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('191', '124', '部门列表操作', '4', '#94#-#95#-#124#-#191#', '', '1', '1', '1', '/dashboard/workDepartment/pageIndex#get', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('192', '124', '查看成员操作', '4', '#94#-#95#-#124#-#192#', '', '1', '1', '1', '/dashboard/workDepartment/showEmployee#get', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('193', '110', '查看人员操作', '4', '#94#-#95#-#110#-#193#', '', '1', '1', '1', '/dashboard/role/showEmployee#get', '2', '0', '系统', '99', '2020-12-31 19:22:12', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('194', '110', '角色状态修改操作', '4', '#94#-#95#-#110#-#194#', '', '1', '1', '1', '/dashboard/role/statusUpdate#put', '2', '0', '系统', '99', '2020-12-31 19:22:13', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('195', '110', '角色列表操作', '4', '#94#-#95#-#110#-#195#', '', '1', '1', '1', '/dashboard/role/index#get', '2', '0', '系统', '99', '2020-12-31 19:22:13', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('196', '110', '角色添加操作', '4', '#94#-#95#-#110#-#196#', '', '1', '1', '1', '/dashboard/role/store#post', '2', '0', '系统', '99', '2020-12-31 19:22:13', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('197', '110', '角色编辑操作', '4', '#94#-#95#-#110#-#197#', '', '1', '1', '1', '/dashboard/role/update#put', '2', '0', '系统', '99', '2020-12-31 19:22:13', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('198', '110', '角色详情操作', '4', '#94#-#95#-#110#-#198#', '', '1', '1', '1', '/dashboard/role/show#get', '2', '0', '系统', '99', '2020-12-31 19:22:13', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('199', '110', '角色删除操作', '4', '#94#-#95#-#110#-#199#', '', '1', '1', '1', '/dashboard/role/destroy#delete', '2', '0', '系统', '99', '2020-12-31 19:22:13', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('200', '111', '个人菜单列表操作', '5', '#94#-#95#-#110#-#111#-#200#', '', '1', '1', '1', '/dashboard/role/permissionByUser#get', '2', '0', '系统', '99', '2020-12-31 19:22:13', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('201', '111', '权限查看操作', '5', '#94#-#95#-#110#-#111#-#201#', '', '1', '1', '1', '/dashboard/role/permissionShow#get', '2', '0', '系统', '99', '2020-12-31 19:22:13', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('202', '111', '权限保存操作', '5', '#94#-#95#-#110#-#111#-#202#', '', '1', '1', '1', '/dashboard/role/permissionStore#post', '2', '0', '系统', '99', '2020-12-31 19:22:13', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('203', '120', '菜单列表操作', '4', '#94#-#95#-#120#-#203#', '', '1', '1', '1', '/dashboard/menu/index#get', '2', '0', '系统', '99', '2020-12-31 19:22:13', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('204', '120', '菜单添加操作', '4', '#94#-#95#-#120#-#204#', '', '1', '1', '1', '/dashboard/menu/store#post', '2', '0', '系统', '99', '2020-12-31 19:22:13', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('205', '120', '菜单修改操作', '4', '#94#-#95#-#120#-#205#', '', '1', '1', '1', '/dashboard/menu/update#put', '2', '0', '系统', '99', '2020-12-31 19:22:13', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('206', '120', '菜单详情操作', '4', '#94#-#95#-#120#-#206#', '', '1', '1', '1', '/dashboard/menu/show#get', '2', '0', '系统', '99', '2020-12-31 19:22:13', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('207', '120', '菜单移除操作', '4', '#94#-#95#-#120#-#207#', '', '1', '1', '1', '/dashboard/menu/destroy#delete', '2', '0', '系统', '99', '2020-12-31 19:22:13', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('208', '120', '菜单状态操作操作', '4', '#94#-#95#-#120#-#208#', '', '1', '1', '1', '/dashboard/menu/statusUpdate#put', '2', '0', '系统', '99', '2020-12-31 19:22:13', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('216', '96', '通讯录列表操作', '4', '#94#-#95#-#96#-#216#', '', '1', '1', '1', '/dashboard/workEmployee/index#get', '2', '0', '系统', '99', '2020-12-31 19:22:13', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('217', '96', '通讯录同步操作', '4', '#94#-#95#-#96#-#217#', '', '1', '1', '1', '/dashboard/workEmployee/synEmployee#put', '2', '0', '系统', '99', '2020-12-31 19:22:14', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('218', '1', '系统首页', '2', '#1#-#218#', 'home', '1', '1', '1', '/dashboardpath/1610968617', '1', '0', '系统', '1', '2020-12-31 19:22:04', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('219', '218', '系统首页', '3', '#1#-#218#-#219#', '', '1', '1', '1', '/dashboard/corpData/index', '2', '0', '系统', '98', '2020-12-31 19:22:04', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('220', '71', '用户画像', '3', '#1#-#71#-#220#', '', '1', '1', '1', '/dashboard/chatTool/customer', '2', '0', '系统', '99', '2021-02-05 11:35:55', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('221', '71', '聊天增强', '3', '#1#-#71#-#221#', '', '1', '1', '1', '/dashboard/chatTool/enhance', '2', '0', '系统', '99', '2021-02-05 11:36:44', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('234', '54', '入群欢迎语', '3', '#1#-#54#-#234#', '', '1', '1', '1', '/dashboard/roomWelcome/index', '2', '0', '系统', '4', '2021-04-19 10:26:34', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('239', '3', '数据统计员工详情', '4', '#1#-#2#-#3#-#239#', '', '1', '1', '1', '/dashboard/contactBatchAdd/employeeDataShow', '2', '0', '系统', '99', '2021-04-19 15:17:16', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('241', '234', '新增/编辑', '4', '#1#-#54#-#234#-#241#', '', '1', '1', '1', '/dashboard/roomWelcome/create', '2', '0', '系统', '99', '2021-04-20 02:55:49', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('242', '234', '修改', '4', '#1#-#54#-#234#-#242#', '', '1', '1', '1', '/dashboard/roomWelcome/update', '2', '0', '系统', '99', '2021-04-20 02:56:48', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('243', '1', '授权管理', '2', '#1#-#243#', 'star', '1', '1', '1', '/dashboardpath/1618917677', '1', '0', '系统', '99', '2021-04-20 11:21:17', '2023-03-22 22:46:31', '2021-06-28 21:47:06');
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('254', '330', '企微任务宝', '3', '#1#-#330#-#254#', '', '1', '1', '1', '/dashboard/workFission/taskpage', '2', '0', '系统', '99', '2021-04-26 13:49:13', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('255', '254', '数据详情', '4', '#1#-#330#-#254#-#255#', '', '1', '1', '1', '/dashboard/workFission/dataShow', '2', '0', '系统', '99', '2021-04-26 13:50:22', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('256', '254', '创建', '4', '#1#-#330#-#254#-#256#', '', '1', '1', '1', '/dashboard/workFission/create', '2', '0', '系统', '99', '2021-04-26 13:51:09', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('257', '254', '邀请', '4', '#1#-#330#-#254#-#257#', '', '1', '1', '1', '/dashboard/workFission/invite', '2', '0', '系统', '99', '2021-04-26 13:51:33', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('258', '254', '修改', '4', '#1#-#330#-#254#-#258#', '', '1', '1', '1', '/dashboard/workFission/edit', '2', '0', '系统', '99', '2021-04-26 13:51:57', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('268', '27', '离职继承', '3', '#1#-#27#-#268#', '', '1', '1', '1', '/dashboard/contactTransfer/resignIndex', '2', '0', '系统', '5', '2021-04-26 16:40:06', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('269', '268', '分配记录', '4', '#1#-#27#-#268#-#269#', '', '1', '1', '1', '/dashboard/contactTransfer/resignAllotRecord', '2', '0', '系统', '99', '2021-04-26 16:40:26', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('270', '27', '在职转接', '3', '#1#-#27#-#270#', '', '1', '1', '1', '/dashboard/contactTransfer/workIndex', '2', '0', '系统', '6', '2021-04-26 16:40:42', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('271', '270', '分配记录', '4', '#1#-#27#-#270#-#271#', '', '1', '1', '1', '/dashboard/contactTransfer/workAllotRecord', '2', '0', '系统', '99', '2021-04-26 16:41:01', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('285', '1', '运营管理中心', '2', '#1#-#285#', 'stock', '1', '1', '1', '/dashboardpath/1623146912', '1', '0', '系统', '6', '2021-06-08 18:08:32', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('286', '285', '客户统计', '3', '#1#-#285#-#286#', '', '1', '1', '1', '/dashboard/statistics/contact', '2', '0', '系统', '99', '2021-06-08 18:08:51', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('287', '285', '成员统计', '3', '#1#-#285#-#287#', '', '1', '1', '1', '/dashboard/statistics/employee', '2', '0', '系统', '99', '2021-06-08 18:09:05', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('288', '54', '标签建群', '3', '#1#-#54#-#288#', '', '1', '1', '1', '/dashboard/roomTagPull/index', '2', '0', '系统', '6', '2021-06-09 14:06:32', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('290', '288', '创建邀请', '4', '#1#-#54#-#288#-#290#', '', '1', '1', '1', '/dashboard/roomTagPull/create', '2', '0', '系统', '99', '2021-06-09 16:56:30', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('314', '288', '列表详情', '4', '#1#-#54#-#288#-#314#', '', '1', '1', '1', '/dashboard/roomTagPull/detail', '2', '0', '系统', '99', '2021-06-12 09:46:09', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('330', '1', '营销中心', '2', '#1#-#330#', 'contacts', '1', '1', '1', '/dashboardpath/1624886566', '1', '0', '系统', '3', '2021-06-28 21:22:46', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('335', '95', '公众号授权', '3', '#94#-#95#-#335#', '', '1', '1', '1', '/dashboard/officialAccount/index', '2', '0', '系统', '7', '2021-07-12 10:51:13', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('336', '335', '添加公众号', '4', '#94#-#95#-#335#-#336#', '', '1', '1', '1', '/dashboard/officialAccount/create', '2', '0', '系统', '99', '2021-07-12 10:54:38', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('425', '14', '客户群发', '3', '#1#-#14#-#425#', '', '1', '1', '1', '/dashboard/contactMessageBatchSend/index', '2', '0', '系统', '99', '2021-09-06 16:08:39', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('426', '425', '列表接口', '4', '#1#-#14#-#425#-#426#', '', '1', '1', '2', '/dashboard/contactMessageBatchSend/index#get', '1', '0', '系统', '99', '2021-09-06 16:13:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('427', '425', '提醒接口', '4', '#1#-#14#-#425#-#427#', '', '1', '1', '2', '/dashboard/contactMessageBatchSend/remind#post', '1', '0', '系统', '99', '2021-09-06 16:13:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('428', '425', '详情接口', '4', '#1#-#14#-#425#-#428#', '', '1', '1', '2', '/dashboard/contactMessageBatchSend/show#get', '1', '0', '系统', '99', '2021-09-06 16:13:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('429', '425', '群详情接口', '4', '#1#-#14#-#425#-#429#', '', '1', '1', '2', '/dashboard/contactMessageBatchSend/showRoom#get', '1', '0', '系统', '99', '2021-09-06 16:13:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('430', '425', '创建接口', '4', '#1#-#14#-#425#-#430#', '', '1', '1', '2', '/dashboard/contactMessageBatchSend/store#post', '1', '0', '系统', '99', '2021-09-06 16:13:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('431', '425', '员工发送列表接口', '4', '#1#-#14#-#425#-#431#', '', '1', '1', '2', '/dashboard/contactMessageBatchSend/employeeSendIndex#get', '1', '0', '系统', '99', '2021-09-06 16:13:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('432', '425', '客户接收列表接口', '4', '#1#-#14#-#425#-#432#', '', '1', '1', '2', '/dashboard/contactMessageBatchSend/contactReceiveIndex#get', '1', '0', '系统', '99', '2021-09-06 16:13:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('433', '425', '删除接口', '4', '#1#-#14#-#425#-#433#', '', '1', '1', '2', '/dashboard/contactMessageBatchSend/destroy#delete', '1', '0', '系统', '99', '2021-09-06 16:13:12', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('434', '425', '新建群发', '4', '#1#-#14#-#425#-#434#', '', '1', '1', '1', '/contactMessageBatchSend/store', '1', '0', '系统', '99', '2021-09-06 16:25:01', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('435', '425', '群发详情', '4', '#1#-#14#-#425#-#435#', '', '1', '1', '1', '/contactMessageBatchSend/show', '1', '0', '系统', '99', '2021-09-06 16:25:01', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('440', '54', '客户群群发', '3', '#1#-#54#-#440#', '', '1', '1', '1', '/dashboard/roomMessageBatchSend/index', '2', '0', '系统', '99', '2021-09-06 18:27:53', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('441', '440', '客户群-新建消息', '4', '#1#-#54#-#440#-#441#', '', '1', '1', '1', '/dashboard/roomMessageBatchSend/store', '1', '0', '系统', '99', '2021-09-06 18:40:10', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('442', '440', '群发详情', '4', '#1#-#54#-#440#-#442#', '', '1', '1', '1', '/dashboard/roomMessageBatchSend/show', '1', '0', '系统', '99', '2021-09-06 18:40:10', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('443', '440', '列表接口', '4', '#1#-#54#-#440#-#443#', '', '1', '1', '2', '/dashboard/roomMessageBatchSend/index#get', '1', '0', '系统', '99', '2021-09-06 18:40:10', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('444', '440', '提醒接口', '4', '#1#-#54#-#440#-#444#', '', '1', '1', '2', '/dashboard/roomMessageBatchSend/remind#get', '1', '0', '系统', '99', '2021-09-06 18:40:10', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('445', '440', '群发群主发送列表接口', '4', '#1#-#54#-#440#-#445#', '', '1', '1', '2', '/dashboard/roomMessageBatchSend/roomOwnerSendIndex#get', '1', '0', '系统', '99', '2021-09-06 18:40:10', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('446', '440', '群发接收列表接口', '4', '#1#-#54#-#440#-#446#', '', '1', '1', '2', '/dashboard/roomMessageBatchSend/roomReceiveIndex#get', '1', '0', '系统', '99', '2021-09-06 18:40:10', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('447', '440', '群发详情接口', '4', '#1#-#54#-#440#-#447#', '', '1', '1', '2', '/dashboard/roomMessageBatchSend/show#get', '1', '0', '系统', '99', '2021-09-06 18:40:10', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('448', '440', '新建群发接口', '4', '#1#-#54#-#440#-#448#', '', '1', '1', '2', '/dashboard/roomMessageBatchSend/store#post', '1', '0', '系统', '99', '2021-09-06 18:40:10', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('449', '440', '删除群发接口', '4', '#1#-#54#-#440#-#449#', '', '1', '1', '2', '/dashboard/roomMessageBatchSend/destroy#delete', '1', '0', '系统', '99', '2021-09-06 18:40:10', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('450', '55', '客户群详情', '4', '#1#-#54#-#55#-#450#', '', '1', '1', '1', '/dashboard/workRoom/detail', '1', '0', '系统', '99', '2021-09-06 18:41:37', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('539', '288', '标签拉群列表接口', '4', '#1#-#54#-#288#-#539#', '', '1', '1', '2', '/dashboard/roomTagPull/index#get', '1', '0', '系统', '99', '2021-09-07 00:47:59', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('540', '288', '标签拉群选择客户接口', '4', '#1#-#54#-#288#-#540#', '', '1', '1', '2', '/dashboard/roomTagPull/chooseContact#get', '1', '0', '系统', '99', '2021-09-07 00:47:59', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('541', '288', '标签拉群删除接口', '4', '#1#-#54#-#288#-#541#', '', '1', '1', '2', '/dashboard/roomTagPull/destroy#delete', '1', '0', '系统', '99', '2021-09-07 00:47:59', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('542', '288', '标签拉群筛选客户接口', '4', '#1#-#54#-#288#-#542#', '', '1', '1', '2', '/dashboard/roomTagPull/filterContact#post', '1', '0', '系统', '99', '2021-09-07 00:47:59', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('543', '288', '标签拉群提醒接口', '4', '#1#-#54#-#288#-#543#', '', '1', '1', '2', '/dashboard/roomTagPull/remindSend#get', '1', '0', '系统', '99', '2021-09-07 00:47:59', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('544', '288', '标签拉群群列表接口', '4', '#1#-#54#-#288#-#544#', '', '1', '1', '2', '/dashboard/roomTagPull/roomList#get', '1', '0', '系统', '99', '2021-09-07 00:47:59', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('545', '288', '标签拉群详情接口', '4', '#1#-#54#-#288#-#545#', '', '1', '1', '2', '/dashboard/roomTagPull/show#get', '1', '0', '系统', '99', '2021-09-07 00:47:59', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('546', '288', '标签拉群详情客户接口', '4', '#1#-#54#-#288#-#546#', '', '1', '1', '2', '/dashboard/roomTagPull/showContact#get', '1', '0', '系统', '99', '2021-09-07 00:47:59', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('547', '288', '标签拉群新建接口', '4', '#1#-#54#-#288#-#547#', '', '1', '1', '2', '/dashboard/roomTagPull/store#post', '1', '0', '系统', '99', '2021-09-07 00:47:59', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('548', '234', '群欢迎语列表接口', '4', '#1#-#54#-#234#-#548#', '', '1', '1', '2', '/dashboard/roomWelcome/index#get', '1', '0', '系统', '99', '2021-09-07 00:50:29', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('549', '234', '群欢迎语删除接口', '4', '#1#-#54#-#234#-#549#', '', '1', '1', '2', '/dashboard/roomWelcome/destroy#delete', '1', '0', '系统', '99', '2021-09-07 00:50:29', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('550', '234', '群欢迎语选择接口', '4', '#1#-#54#-#234#-#550#', '', '1', '1', '2', '/dashboard/roomWelcome/select#get', '1', '0', '系统', '99', '2021-09-07 00:50:29', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('551', '234', '群欢迎语详情接口', '4', '#1#-#54#-#234#-#551#', '', '1', '1', '2', '/dashboard/roomWelcome/show#get', '1', '0', '系统', '99', '2021-09-07 00:50:29', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('552', '234', '群欢迎语新建接口', '4', '#1#-#54#-#234#-#552#', '', '1', '1', '2', '/dashboard/roomWelcome/store#post', '1', '0', '系统', '99', '2021-09-07 00:50:29', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('553', '234', '群欢迎语更新接口', '4', '#1#-#54#-#234#-#553#', '', '1', '1', '2', '/dashboard/roomWelcome/update#put', '1', '0', '系统', '99', '2021-09-07 00:50:29', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('554', '270', '在职转接列表接口', '4', '#1#-#27#-#270#-#554#', '', '1', '1', '2', '/dashboard/contactTransfer/index#post', '1', '0', '系统', '99', '2021-09-07 01:00:35', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('555', '270', '在职转接详情接口', '4', '#1#-#27#-#270#-#555#', '', '1', '1', '2', '/dashboard/contactTransfer/info#get', '1', '0', '系统', '99', '2021-09-07 01:00:35', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('556', '270', '在职转接记录接口', '4', '#1#-#27#-#270#-#556#', '', '1', '1', '2', '/dashboard/contactTransfer/log#get', '1', '0', '系统', '99', '2021-09-07 01:00:35', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('557', '268', '离职继承待分配群聊接口', '4', '#1#-#27#-#268#-#557#', '', '1', '1', '2', '/dashboard/contactTransfer/room#get', '1', '0', '系统', '99', '2021-09-07 01:00:35', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('558', '268', '离职继承列表接口', '4', '#1#-#27#-#268#-#558#', '', '1', '1', '2', '/dashboard/contactTransfer/unassignedList#get', '1', '0', '系统', '99', '2021-09-07 01:00:35', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('559', '268', '离职继承保存接口', '4', '#1#-#27#-#268#-#559#', '', '1', '1', '2', '/dashboard/contactTransfer/saveUnassignedList#get', '1', '0', '系统', '99', '2021-09-07 01:00:35', '2023-03-22 22:46:30', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('560', '254', '企微裂变列表接口', '4', '#1#-#330#-#254#-#560#', '', '1', '1', '2', '/dashboard/workFission/index#get', '1', '0', '系统', '99', '2021-09-07 01:04:58', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('561', '254', '企微裂变选择客户接口', '4', '#1#-#330#-#254#-#561#', '', '1', '1', '2', '/dashboard/workFission/chooseContact#get', '1', '0', '系统', '99', '2021-09-07 01:04:58', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('562', '254', '企微裂变删除接口', '4', '#1#-#330#-#254#-#562#', '', '1', '1', '2', '/dashboard/workFission/destroy#delete', '1', '0', '系统', '99', '2021-09-07 01:04:58', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('563', '254', '企微裂变详情接口', '4', '#1#-#330#-#254#-#563#', '', '1', '1', '2', '/dashboard/workFission/info#get', '1', '0', '系统', '99', '2021-09-07 01:04:58', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('564', '254', '企微裂变邀请接口', '4', '#1#-#330#-#254#-#564#', '', '1', '1', '2', '/dashboard/workFission/invite#post', '1', '0', '系统', '99', '2021-09-07 01:04:58', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('565', '254', '企微裂变邀请数据接口', '4', '#1#-#330#-#254#-#565#', '', '1', '1', '2', '/dashboard/workFission/inviteData#get', '1', '0', '系统', '99', '2021-09-07 01:04:58', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('566', '254', '企微裂变邀请详情接口', '4', '#1#-#330#-#254#-#566#', '', '1', '1', '2', '/dashboard/workFission/inviteDetail#get', '1', '0', '系统', '99', '2021-09-07 01:04:58', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('567', '254', '企微裂变详情接口', '4', '#1#-#330#-#254#-#567#', '', '1', '1', '2', '/dashboard/workFission/show#get', '1', '0', '系统', '99', '2021-09-07 01:04:58', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('568', '254', '企微裂变统计分析接口', '4', '#1#-#330#-#254#-#568#', '', '1', '1', '2', '/dashboard/workFission/statistics#get', '1', '0', '系统', '99', '2021-09-07 01:04:58', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('569', '254', '企微裂变新建接口', '4', '#1#-#330#-#254#-#569#', '', '1', '1', '2', '/dashboard/workFission/store#get', '1', '0', '系统', '99', '2021-09-07 01:04:58', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('570', '254', '企微裂变统计更新接口', '4', '#1#-#330#-#254#-#570#', '', '1', '1', '2', '/dashboard/workFission/update#get', '1', '0', '系统', '99', '2021-09-07 01:04:58', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('571', '335', '公众号授权列表接口', '4', '#94#-#95#-#335#-#571#', '', '1', '1', '2', '/dashboard/officialAccount/index#get', '1', '0', '系统', '99', '2021-09-07 01:06:53', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('572', '335', '公众号授权预授权接口', '4', '#94#-#95#-#335#-#572#', '', '1', '1', '2', '/dashboard/officialAccount/getPreAuthUrl#get', '1', '0', '系统', '99', '2021-09-07 01:06:53', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('573', '335', '公众号授权设置接口', '4', '#94#-#95#-#335#-#573#', '', '1', '1', '2', '/dashboard/officialAccount/set#get', '1', '0', '系统', '99', '2021-09-07 01:06:53', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('576', '99', '用户重置密码', '4', '#94#-#95#-#99#-#576#', '', '1', '1', '2', '/dashboard/user/passwordReset#put', '1', '0', '系统', '99', '2021-09-07 01:57:56', '2023-03-22 22:46:32', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('579', '286', '统计列表接口', '4', '#1#-#285#-#286#-#579#', '', '1', '1', '2', '/dashboard/statistic/index#get', '1', '0', '系统', '99', '2021-09-10 15:45:09', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('580', '286', '客户数量前十排行榜接口', '4', '#1#-#285#-#286#-#580#', '', '1', '1', '2', '/dashboard/statistic/topList#get', '1', '0', '系统', '99', '2021-09-10 15:45:09', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('581', '287', '成员统计分页接口', '4', '#1#-#285#-#287#-#581#', '', '1', '1', '2', '/dashboard/statistic/employeeCounts#get', '1', '0', '系统', '99', '2021-09-10 15:45:09', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('582', '287', '成员统计列表接口', '4', '#1#-#285#-#287#-#582#', '', '1', '1', '2', '/dashboard/statistic/employees#get', '1', '0', '系统', '99', '2021-09-10 15:45:09', '2023-03-22 22:46:31', null);
INSERT INTO `mc_rbac_menu` (`id`, `parent_id`, `name`, `level`, `path`, `icon`, `status`, `link_type`, `is_page_menu`, `link_url`, `data_permission`, `operate_id`, `operate_name`, `sort`, `created_at`, `updated_at`, `deleted_at`) VALUES ('583', '287', '成员统计联系客户接口', '4', '#1#-#285#-#287#-#583#', '', '1', '1', '2', '/dashboard/statistic/employeesTrend#get', '1', '0', '系统', '99', '2021-09-10 15:45:09', '2023-03-22 22:46:31', null);

