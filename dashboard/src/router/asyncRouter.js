export const routeList = {
  '/corp/index': {
    path: '/corp/index',
    name: 'corpIndex',
    component: () => import(/* webpackChunkName: "corp" */ '@/views/corp/index'),
    meta: { title: '企业微信授权' }
  },
  '/user/index': {
    path: '/user/index',
    name: 'user',
    component: () => import(/* webpackChunkName: "user" */ '@/views/user/index'),
    meta: { title: '子账户管理' }
  },
  '/passwordUpdate/index': {
    path: '/passwordUpdate/index',
    name: 'passwordUpdate',
    hidden: true,
    component: () => import(/* webpackChunkName: "user" */ '@/views/passwordUpdate/index'),
    meta: { title: '修改密码' }
  },
  '/workEmployee/index': {
    path: '/workEmployee/index',
    name: 'workEmployeeIndex',
    component: () => import(/* webpackChunkName: "work-employee" */ '@/views/workEmployee/index'),
    meta: { title: '企业成员' }
  },
  '/workMessageConfig/corpShow': {
    path: '/workMessageConfig/corpShow',
    name: 'workMessageConfigCorpShow',
    component: () => import(/* webpackChunkName: "work-message" */ '@/views/workMessageConfig/corpShow'),
    meta: { title: '聊天记录申请' }
  },
  '/role/index': {
    path: '/role/index',
    name: 'roleIndex',
    component: () => import(/* webpackChunkName: "rbac" */ '@/views/role/index'),
    meta: { title: '角色管理' }
  },
  '/role/permissionShow': {
    path: '/role/permissionShow',
    name: 'rolePermissionShow',
    hidden: true,
    component: () => import(/* webpackChunkName: "rbac" */ '@/views/role/permissionShow'),
    meta: { title: '权限设置' }
  },
  '/chatTool/enhance': {
    path: '/chatTool/enhance',
    name: 'chatEnhance',
    component: () => import(/* webpackChunkName: "chat-tool" */ '@/views/chatTool/enhance'),
    meta: { title: '聊天增强' }
  },
  '/chatTool/customer': {
    path: '/chatTool/customer',
    name: 'customer',
    component: () => import(/* webpackChunkName: "chat-tool" */ '@/views/chatTool/customer'),
    meta: { title: '用户画像' }
  },
  '/menu/index': {
    path: '/menu/index',
    name: 'menuIndex',
    component: () => import(/* webpackChunkName: "rbac" */ '@/views/menu/index'),
    meta: { title: '菜单' }
  },
  '/department/index': {
    path: '/department/index',
    name: 'departmentIndex',
    component: () => import(/* webpackChunkName: "department" */ '@/views/department/index'),
    meta: { title: '组织架构' }
  },
  '/workContact/index': {
    path: '/workContact/index',
    name: 'workContactIndex',
    // hideChildrenInMenu: true,
    component: () => import(/* webpackChunkName: "work-contact" */ '@/views/workContact/index'),
    meta: { title: '客户列表' }
  },
  '/workContact/contactFieldPivot': {
    path: '/workContact/contactFieldPivot',
    name: 'contactFieldPivot',
    hidden: true,
    component: () => import(/* webpackChunkName: "work-contact" */ '@/views/workContact/contactFieldPivot'),
    meta: { title: '客户详情' }
  },
  '/contactField/index': {
    path: '/contactField/index',
    name: 'contactField',
    component: () => import(/* webpackChunkName: "work-contact" */ '@/views/contactField/index'),
    meta: { title: '客户资料卡' }
  },
  '/lossContact/index': {
    path: '/lossContact/index',
    name: 'lossContact',
    component: () => import(/* webpackChunkName: "work-contact" */ '@/views/lossContact/index'),
    meta: { title: '流失客户' }
  },
  '/workContactTag/index': {
    path: '/workContactTag/index',
    name: 'workContactTag',
    component: () => import(/* webpackChunkName: "work-contact" */ '@/views/workContactTag/index'),
    meta: { title: '标签管理' }
  },
  '/workRoom/index': {
    path: '/workRoom/index',
    name: 'workRoomIndex',
    component: () => import(/* webpackChunkName: "work-room" */ '@/views/workRoom/index'),
    meta: { title: '群列表' }
  },
  '/workRoom/statistics': {
    path: '/workRoom/statistics',
    name: 'statistics',
    hidden: true,
    component: () => import(/* webpackChunkName: "work-room" */ '@/views/workRoom/statistics'),
    meta: { title: '群统计' }
  },
  // 群详情
  '/workRoom/detail': {
    path: '/workRoom/detail',
    name: 'workRoomDetail',
    hidden: true,
    component: () => import(/* webpackChunkName: "work-room" */ '@/views/workRoom/detail'),
    meta: { title: '群详情' }
  },
  '/workRoomAutoPull/index': {
    path: '/workRoomAutoPull/index',
    name: 'workRoomAutoPull',
    component: () => import(/* webpackChunkName: "room-auto-pull" */ '@/views/workRoomAutoPull/index'),
    meta: { title: '自动拉群列表' }
  },
  '/workRoomAutoPull/store': {
    path: '/workRoomAutoPull/store',
    name: 'workRoomAutoPullStore',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-auto-pull" */ '@/views/workRoomAutoPull/store'),
    meta: { title: '新建拉群' }
  },
  '/roomTagPull/index': {
    path: '/roomTagPull/index',
    name: 'roomTagPull',
    component: () => import(/* webpackChunkName: "room-tag-pull" */ '@/views/roomTagPull/index'),
    meta: { title: '标签建群' }
  },
  '/roomTagPull/create': {
    path: '/roomTagPull/create',
    name: 'roomTagPullCreate',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-tag-pull" */ '@/views/roomTagPull/create'),
    meta: { title: '创建邀请' }
  },
  '/roomTagPull/detail': {
    path: '/roomTagPull/detail',
    name: 'roomTagPullDetail',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-tag-pull" */ '@/views/roomTagPull/detail'),
    meta: { title: '列表详情' }
  },
  '/roomTagPull/contactDetail': {
    path: '/roomTagPull/contactDetail',
    name: 'roomTagPullContactDetail',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-tag-pull" */ '@/views/roomTagPull/contactDetail'),
    meta: { title: '客户详情' }
  },
  '/roomRemind/index': {
    path: '/roomRemind/index',
    name: 'roomRemind',
    component: () => import(/* webpackChunkName: "room-remind" */ '@/views/roomRemind/index'),
    meta: { title: '客户群提醒' }
  },
  '/roomQuality/index': {
    path: '/roomQuality/index',
    name: 'roomQuality',
    component: () => import(/* webpackChunkName: "room-quality" */ '@/views/roomQuality/index'),
    meta: { title: '群聊质检' }
  },
  '/roomQuality/edit': {
    path: '/roomQuality/edit',
    name: 'roomQualityEdit',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-quality" */ '@/views/roomQuality/edit'),
    meta: { title: '规则编辑' }
  },
  '/roomQuality/newRule': {
    path: '/roomQuality/newRule',
    name: 'roomQualityRule',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-quality" */ '@/views/roomQuality/newRule'),
    meta: { title: '新建质检规则' }
  },
  '/roomQuality/detail': {
    path: '/roomQuality/detail',
    name: 'roomQualityDetail',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-quality" */ '@/views/roomQuality/detail'),
    meta: { title: '规则详情' }
  },
  '/mediumGroup/index': {
    path: '/mediumGroup/index',
    name: 'mediumGroupIndex',
    component: () => import(/* webpackChunkName: "medium" */ '@/views/mediumGroup/index'),
    meta: { title: '素材库' }
  },
  '/workMessage/index': {
    path: '/workMessage/index',
    name: 'workMessageIndex',
    component: () => import(/* webpackChunkName: "work-message" */ '@/views/workMessage/index'),
    meta: { title: '聊天记录' }
  },
  '/workMessage/toUsers': {
    path: '/workMessage/toUsers',
    name: 'workMessageToUsers',
    hidden: true,
    component: () => import(/* webpackChunkName: "work-message" */ '@/views/workMessage/toUsers'),
    meta: { title: '聊天记录查看' }
  },
  '/sensitiveWords/index': {
    path: '/sensitiveWords/index',
    name: 'sensitiveWordsIndex',
    component: () => import(/* webpackChunkName: "sensitive-word" */ '@/views/sensitiveWords/index'),
    meta: { title: '敏感词词库' }
  },
  '/sensitiveWordsMonitor/index': {
    path: '/sensitiveWordsMonitor/index',
    name: 'sensitiveWordsMonitorIndex',
    component: () => import(/* webpackChunkName: "sensitive-word" */ '@/views/sensitiveWordsMonitor/index'),
    meta: { title: '敏感词监控' }
  },
  '/greeting/index': {
    path: '/greeting/index',
    name: 'greetingIndex',
    component: () => import(/* webpackChunkName: "greeting" */ '@/views/greeting/index'),
    meta: { title: '欢迎语' }
  },
  '/greeting/store': {
    path: '/greeting/store',
    name: 'greetingStore',
    hidden: true,
    component: () => import(/* webpackChunkName: "greeting" */ '@/views/greeting/store'),
    meta: { title: '新建欢迎语' }
  },
  '/channelCode/index': {
    path: '/channelCode/index',
    name: 'channelCodeIndex',
    component: () => import(/* webpackChunkName: "channel-code" */ '@/views/channelCode/index'),
    meta: { title: '渠道码' }
  },
  '/channelCode/store': {
    path: '/channelCode/store',
    name: 'channelCodeStore',
    hidden: true,
    component: () => import(/* webpackChunkName: "channel-code" */ '@/views/channelCode/store'),
    meta: { title: '新建渠道码' }
  },
  '/channelCode/statistics': {
    path: '/channelCode/statistics',
    name: 'channelCodeStatistics',
    hidden: true,
    component: () => import(/* webpackChunkName: "channel-code" */ '@/views/channelCode/statistics'),
    meta: { title: '统计' }
  },
  '/corpData/index': {
    path: '/corpData/index',
    name: 'systemHomePage',
    component: () => import(/* webpackChunkName: "corp" */ '@/views/corpData/index'),
    meta: { title: '系统首页' }
  },
  '/greeting/userSearch': {
    path: '/greeting/userSearch',
    name: 'userSearch',
    component: () => import(/* webpackChunkName: "chat-tool" */ '@/views/greeting/userSearch'),
    meta: { title: '用户搜索添加' }
  },
  // 入群欢迎语
  '/roomWelcome/index': {
    path: '/roomWelcome/index',
    name: 'roomWelcomeIndex',
    component: () => import(/* webpackChunkName: "room-welcome" */ '@/views/roomWelcome/index'),
    meta: { title: '入群欢迎语列表' }
  },
  '/roomWelcome/create': {
    path: '/roomWelcome/create',
    name: 'roomWelcomeCreate',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-welcome" */ '@/views/roomWelcome/create'),
    meta: { title: '新增/修改入群欢迎语' }
  },
  // 授权管理
  '/officialAccount/index': {
    path: '/officialAccount/index',
    name: 'officialAccountIndex',
    component: () => import(/* webpackChunkName: "official-account" */ '@/views/officialAccount/index'),
    meta: { title: '授权管理' }
  },
  '/officialAccount/create': {
    path: '/officialAccount/create',
    name: 'officialAccountCreate',
    hidden: true,
    component: () => import(/* webpackChunkName: "official-account" */ '@/views/officialAccount/create'),
    meta: { title: '添加公众号' }
  },
  // 群裂变
  '/roomFission/index': {
    path: '/roomFission/index',
    name: 'roomFissionIndex',
    component: () => import(/* webpackChunkName: "room-fission" */ '@/views/roomFission/index'),
    meta: { title: '活动列表' }
  },
  '/roomFission/dataShow': {
    path: '/roomFission/dataShow',
    name: 'roomFissionDataShow',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-fission" */ '@/views/roomFission/dataShow'),
    meta: { title: '数据详情' }
  },
  '/roomFission/create': {
    path: '/roomFission/create',
    name: 'roomFissionCreate',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-fission" */ '@/views/roomFission/create'),
    meta: { title: '创建' }
  },
  '/roomFission/invite': {
    path: '/roomFission/invite',
    name: 'roomFissionInvite',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-fission" */ '@/views/roomFission/invite'),
    meta: { title: '邀请' }
  },
  '/roomFission/update': {
    path: '/roomFission/update',
    name: 'roomFissionUpdate',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-fission" */ '@/views/roomFission/update'),
    meta: { title: '修改' }
  },
  // 企微任务宝
  '/workFission/taskpage': {
    path: '/workFission/taskpage',
    name: 'workFissionTaskpage',
    component: () => import(/* webpackChunkName: "work-fission" */ '@/views/workFission/taskpage'),
    meta: { title: '任务列表' }
  },
  // 企微任务宝
  // '/workFission/index': {
  //   path: '/workFission/index',
  //   name: 'workFissionIndex',
  //   component: () => import(/* webpackChunkName: "department" */ '@/views/workFission/index'),
  //   meta: { title: '任务列表' }
  // },
  '/workFission/create': {
    path: '/workFission/create',
    name: 'workFissionCreate',
    hidden: true,
    component: () => import(/* webpackChunkName: "work-fission" */ '@/views/workFission/create'),
    meta: { title: '创建' }
  },
  '/workFission/invite': {
    path: '/workFission/invite',
    name: 'workFissionInvite',
    hidden: true,
    component: () => import(/* webpackChunkName: "work-fission" */ '@/views/workFission/invite'),
    meta: { title: '邀请' }
  },
  '/workFission/edit': {
    path: '/workFission/edit',
    name: 'workFissionEdit',
    hidden: true,
    component: () => import(/* webpackChunkName: "work-fission" */ '@/views/workFission/edit'),
    meta: { title: '修改' }
  },
  '/workFission/dataShow': {
    path: '/workFission/dataShow',
    name: 'workFissionDataShow',
    hidden: true,
    component: () => import(/* webpackChunkName: "work-fission" */ '@/views/workFission/dataShow'),
    meta: { title: '数据详情' }
  },
  // 客户群发
  '/contactMessageBatchSend/index': {
    path: '/contactMessageBatchSend/index',
    name: 'contactMessageBatchSendIndex',
    component: () => import(/* webpackChunkName: "contact-message-batch-send" */ '@/views/contactMessageBatchSend/index'),
    meta: { title: '客户群发' }
  },
  '/contactMessageBatchSend/store': {
    path: '/contactMessageBatchSend/store',
    name: 'contactMessageBatchSendStore',
    hidden: true,
    component: () => import(/* webpackChunkName: "contact-message-batch-send" */ '@/views/contactMessageBatchSend/store'),
    meta: { title: '新建群发' }
  },
  '/contactMessageBatchSend/show': {
    path: '/contactMessageBatchSend/show',
    name: 'contactMessageBatchSendShow',
    hidden: true,
    component: () => import(/* webpackChunkName: "contact-message-batch-send" */ '@/views/contactMessageBatchSend/show'),
    meta: { title: '群发详情' }
  },
  // 个人SOP
  '/contactSop/index': {
    path: '/contactSop/index',
    name: 'contactSopIndex',
    component: () => import(/* webpackChunkName: "contact-sop" */ '@/views/contactSop/index'),
    meta: { title: '个人SOP' }
  },
  '/contactSop/create': {
    path: '/contactSop/create',
    name: 'contactSopCreate',
    hidden: true,
    component: () => import(/* webpackChunkName: "contact-sop" */ '@/views/contactSop/create'),
    meta: { title: '创建' }
  },
  '/contactSop/edit': {
    path: '/contactSop/edit',
    name: 'contactSopEdit',
    hidden: true,
    component: () => import(/* webpackChunkName: "contact-sop" */ '@/views/contactSop/edit'),
    meta: { title: '编辑' }
  },
  '/contactSop/detail': {
    path: '/contactSop/detail',
    name: 'contactSopDetail',
    hidden: true,
    component: () => import(/* webpackChunkName: "contact-sop" */ '@/views/contactSop/detail'),
    meta: { title: '详情' }
  },
  // 群SOP
  '/roomSop/index': {
    path: '/roomSop/index',
    name: 'roomSopIndex',
    component: () => import(/* webpackChunkName: "room-sop" */ '@/views/roomSop/index'),
    meta: { title: '群SOP' }
  },
  '/roomSop/create': {
    path: '/roomSop/create',
    name: 'roomSopCreate',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-sop" */ '@/views/roomSop/create'),
    meta: { title: '群SOP创建' }
  },
  '/roomSop/edit': {
    path: '/roomSop/edit',
    name: 'roomSopEdit',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-sop" */ '@/views/roomSop/edit'),
    meta: { title: '编辑' }
  },
  '/roomSop/detail': {
    path: '/roomSop/detail',
    name: 'roomSopDetail',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-sop" */ '@/views/roomSop/detail'),
    meta: { title: '详情' }
  },
  // 客户迁移
  '/contactTransfer/resignIndex': {
    path: '/contactTransfer/resignIndex',
    name: 'contactTransferResignIndex',
    component: () => import(/* webpackChunkName: "contact-transfer" */ '@/views/contactTransfer/resignIndex'),
    meta: { title: '离职继承' }
  },
  '/contactTransfer/resignAllotRecord': {
    path: '/contactTransfer/resignAllotRecord',
    name: 'contactTransferResignAllotRecord',
    hidden: true,
    component: () => import(/* webpackChunkName: "contact-transfer" */ '@/views/contactTransfer/resignAllotRecord'),
    meta: { title: '分配记录' }
  },
  '/contactTransfer/workIndex': {
    path: '/contactTransfer/workIndex',
    name: 'contactTransferWorkIndex',
    component: () => import(/* webpackChunkName: "contact-transfer" */ '@/views/contactTransfer/workIndex'),
    meta: { title: '在职转接' }
  },
  '/contactTransfer/workAllotRecord': {
    path: '/contactTransfer/workAllotRecord',
    name: 'contactTransferWorkAllotRecord',
    hidden: true,
    component: () => import(/* webpackChunkName: "contact-transfer" */ '@/views/contactTransfer/workAllotRecord'),
    meta: { title: '分配记录' }
  },
  // 群打卡
  '/roomClockIn/index': {
    path: '/roomClockIn/index',
    name: '/roomClockInIndex',
    component: () => import(/* webpackChunkName: "room-clock-in" */ '@/views/roomClockIn/index'),
    meta: { title: '群打卡' }
  },
  '/roomClockIn/create': {
    path: '/roomClockIn/create',
    name: '/roomClockInCreate',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-clock-in" */ '@/views/roomClockIn/create'),
    meta: { title: '创建' }
  },
  '/roomClockIn/edit': {
    path: '/roomClockIn/edit',
    name: '/roomClockInEdit',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-clock-in" */ '@/views/roomClockIn/edit'),
    meta: { title: '修改' }
  },
  '/roomClockIn/show': {
    path: '/roomClockIn/show',
    name: '/roomClockInShow',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-clock-in" */ '@/views/roomClockIn/show'),
    meta: { title: '详情' }
  },
  // 门店活码
  '/shopCode/employeeIndex': {
    path: '/shopCode/employeeIndex',
    name: '/shopCodeEmployeeIndex',
    component: () => import(/* webpackChunkName: "shop-code" */ '@/views/shopCode/employeeIndex'),
    meta: { title: '扫码添加店主' }
  },
  '/shopCode/roomIndex': {
    path: '/shopCode/roomIndex',
    name: '/shopCodeRoomIndex',
    component: () => import(/* webpackChunkName: "shop-code" */ '@/views/shopCode/roomIndex'),
    meta: { title: '扫码添加店主' }
  },
  '/shopCode/cityIndex': {
    path: '/shopCode/cityIndex',
    name: '/shopCodeCityIndex',
    component: () => import(/* webpackChunkName: "shop-code" */ '@/views/shopCode/cityIndex'),
    meta: { title: '扫码添加店主' }
  },
  // 抽奖活动
  '/lottery/index': {
    path: '/lottery/index',
    name: '/lotteryIndex',
    component: () => import(/* webpackChunkName: "lottery" */ '@/views/lottery/index'),
    meta: { title: '抽奖活动' }
  },
  '/lottery/create': {
    path: '/lottery/create',
    name: '/lotteryCreate',
    hidden: true,
    component: () => import(/* webpackChunkName: "lottery" */ '@/views/lottery/create'),
    meta: { title: '创建' }
  },

  '/lottery/modify': {
    path: '/lottery/modify',
    name: '/lotteryModify',
    hidden: true,
    component: () => import(/* webpackChunkName: "lottery" */ '@/views/lottery/modify'),
    meta: { title: '修改' }
  },
  // 群日历
  '/roomCalendar/index': {
    path: '/roomCalendar/index',
    name: '/roomCalendarIndex',
    component: () => import(/* webpackChunkName: "room-calendar" */ '@/views/roomCalendar/index'),
    meta: { title: '列表' }
  },
  '/roomCalendar/create': {
    path: '/roomCalendar/create',
    name: '/roomCalendarCreate',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-calendar" */ '@/views/roomCalendar/create'),
    meta: { title: '详情' }
  },
  '/roomCalendar/show': {
    path: '/roomCalendar/show',
    name: '/roomCalendarShow',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-calendar" */ '@/views/roomCalendar/show'),
    meta: { title: '详情' }
  },
  '/autoTag/ruleTagging': {
    path: '/autoTag/ruleTagging',
    name: '/autoTagRuleTagging',
    component: () => import(/* webpackChunkName: "auto-tag" */ '@/views/autoTag/ruleTagging'),
    meta: { title: '规则打标签' }
  },
  // 自动打标签
  '/autoTag/keywordIndex': {
    path: '/autoTag/keywordIndex',
    name: '/autoTagKeyWordIndex',
    hidden: true,
    component: () => import(/* webpackChunkName: "auto-tag" */ '@/views/autoTag/keywordIndex'),
    meta: { title: '关键词打标签列表' }
  },
  '/autoTag/keywordCreate': {
    path: '/autoTag/keywordCreate',
    name: '/autoTagKeyWordCreate',
    hidden: true,
    component: () => import(/* webpackChunkName: "auto-tag" */ '@/views/autoTag/keywordCreate'),
    meta: { title: '关键词打标签添加规则' }
  },
  '/autoTag/keywordShow': {
    path: '/autoTag/keywordShow',
    name: '/autoTagKeyWordShow',
    hidden: true,
    component: () => import(/* webpackChunkName: "auto-tag" */ '@/views/autoTag/keywordShow'),
    meta: { title: '关键词打标签详情' }
  },
  '/autoTag/joinRoomIndex': {
    path: '/autoTag/joinRoomIndex',
    name: '/autoTagJoinRoomIndex',
    hidden: true,
    component: () => import(/* webpackChunkName: "auto-tag" */ '@/views/autoTag/joinRoomIndex'),
    meta: { title: '客户入群行为打标签列表' }
  },
  '/autoTag/joinRoomCreate': {
    path: '/autoTag/joinRoomCreate',
    name: '/autoTagJoinRoomCreate',
    hidden: true,
    component: () => import(/* webpackChunkName: "auto-tag" */ '@/views/autoTag/joinRoomCreate'),
    meta: { title: '客户入群行为打标签添加规则' }
  },
  '/autoTag/joinRoomShow': {
    path: '/autoTag/joinRoomShow',
    name: '/autoTagJoinRoomShow',
    hidden: true,
    component: () => import(/* webpackChunkName: "auto-tag" */ '@/views/autoTag/joinRoomShow'),
    meta: { title: '客户入群行为打标签详情' }
  },
  '/autoTag/dayPartIndex': {
    path: '/autoTag/dayPartIndex',
    name: '/autoTagDayPartIndex',
    hidden: true,
    component: () => import(/* webpackChunkName: "auto-tag" */ '@/views/autoTag/dayPartIndex'),
    meta: { title: '分时段打标签列表' }
  },
  '/autoTag/dayPartCreate': {
    path: '/autoTag/dayPartCreate',
    name: '/autoTagDayPartCreate',
    hidden: true,
    component: () => import(/* webpackChunkName: "auto-tag" */ '@/views/autoTag/dayPartCreate'),
    meta: { title: '分时段打标签添加规则' }
  },
  '/autoTag/dayPartShow': {
    path: '/autoTag/dayPartShow',
    name: '/autoTagDayPartShow',
    hidden: true,
    component: () => import(/* webpackChunkName: "auto-tag" */ '@/views/autoTag/dayPartShow'),
    meta: { title: '分时段打标签添加规则' }
  },
  // 客户统计
  '/statistics/contact': {
    path: '/statistics/contact',
    name: '/statisticsContact',
    component: () => import(/* webpackChunkName: "statistics" */ '@/views/statistics/contact'),
    meta: { title: '客户统计' }
  },
  // 成员统计
  '/statistics/employee': {
    path: '/statistics/employee',
    name: '/statisticsEmployee',
    component: () => import(/* webpackChunkName: "statistics" */ '@/views/statistics/employee'),
    meta: { title: '成员统计' }
  },
  // 客户群群发
  '/roomMessageBatchSend/index': {
    path: '/roomMessageBatchSend/index',
    name: 'roomMessageBatchSendIndex',
    component: () => import(/* webpackChunkName: "room-message-batch-send" */ '@/views/roomMessageBatchSend/index'),
    meta: { title: '客户群群发列表' }
  },
  '/roomMessageBatchSend/store': {
    path: '/roomMessageBatchSend/store',
    name: 'roomMessageBatchSendStore',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-message-batch-send" */ '@/views/roomMessageBatchSend/store'),
    meta: { title: '客户群-新建消息' }
  },
  '/roomMessageBatchSend/show': {
    path: '/roomMessageBatchSend/show',
    name: 'roomMessageBatchSend/show',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-message-batch-send" */ '@/views/roomMessageBatchSend/show'),
    meta: { title: '群发详情' }
  },
  // 批量加好友
  '/contactBatchAdd/index': {
    path: '/contactBatchAdd/index',
    name: '/contactBatchAdd/index',
    component: () => import(/* webpackChunkName: "contact-batch-add" */ '@/views/contactBatchAdd/index'),
    meta: { title: '客户列表' }
  },
  '/contactBatchAdd/importIndex': {
    path: '/contactBatchAdd/importIndex',
    name: '/contactBatchAdd/importIndex',
    hidden: true,
    component: () => import(/* webpackChunkName: "contact-batch-add" */ '@/views/contactBatchAdd/importIndex'),
    meta: { title: '导入记录列表' }
  },
  '/contactBatchAdd/importShow': {
    path: '/contactBatchAdd/importShow',
    name: '/contactBatchAdd/importShow',
    hidden: true,
    component: () => import(/* webpackChunkName: "contact-batch-add" */ '@/views/contactBatchAdd/importShow'),
    meta: { title: '导入记录详情' }
  },
  '/contactBatchAdd/dataShow': {
    path: '/contactBatchAdd/dataShow',
    name: '/contactBatchAdd/dataShow',
    hidden: true,
    component: () => import(/* webpackChunkName: "contact-batch-add" */ '@/views/contactBatchAdd/dataShow'),
    meta: { title: '数据统计' }
  },
  '/contactBatchAdd/dataStatistic': {
    path: '/contactBatchAdd/dataStatistic',
    name: '/contactBatchAdd/dataStatistic',
    hidden: true,
    component: () => import(/* webpackChunkName: "contact-batch-add" */ '@/views/contactBatchAdd/dataStatistic'),
    meta: { title: '数据统计详情' }
  },
  // 无限拉群
  '/roomInfinitePull/index': {
    path: '/roomInfinitePull/index',
    name: '/roomInfinitePullIndex',
    component: () => import(/* webpackChunkName: "room-infinite-pull" */ '@/views/roomInfinitePull/index'),
    meta: { title: '列表' }
  },
  '/roomInfinitePull/create': {
    path: '/roomInfinitePull/create',
    name: '/roomInfinitePullCreate',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-infinite-pull" */ '@/views/roomInfinitePull/create'),
    meta: { title: '创建' }
  },
  '/roomInfinitePull/show': {
    path: '/roomInfinitePull/show',
    name: '/roomInfinitePullShow',
    hidden: true,
    component: () => import(/* webpackChunkName: "room-infinite-pull" */ '@/views/roomInfinitePull/show'),
    meta: { title: '详情' }
  },
  // 互动雷达
  '/radar/index': {
    path: '/radar/index',
    name: '/radarIndex',
    component: () => import(/* webpackChunkName: "radar" */ '@/views/radar/index'),
    meta: { title: '互动雷达' }
  },
  '/radar/createLink': {
    path: '/radar/createLink',
    name: '/radarCreateLink',
    component: () => import(/* webpackChunkName: "radar" */ '@/views/radar/createLink'),
    hidden: true,
    meta: { title: '创建链接' }
  },
  '/radar/createPdf': {
    path: '/radar/createPdf',
    name: '/radarCreatePdf',
    component: () => import(/* webpackChunkName: "radar" */ '@/views/radar/createPdf'),
    hidden: true,
    meta: { title: '创建PDF' }
  },
  '/radar/createArticle': {
    path: '/radar/createArticle',
    name: '/radarCreateArticle',
    component: () => import(/* webpackChunkName: "radar" */ '@/views/radar/createArticle'),
    hidden: true,
    meta: { title: '创建文章' }
  },
  '/radar/edit': {
    path: '/radar/edit',
    name: '/radarEdit',
    component: () => import(/* webpackChunkName: "radar" */ '@/views/radar/edit'),
    hidden: true,
    meta: { title: '修改' }
  },
  '/radar/editPdf': {
    path: '/radar/editPdf',
    name: '/radarEditpdf',
    component: () => import(/* webpackChunkName: "radar" */ '@/views/radar/editPdf'),
    hidden: true,
    meta: { title: '修改PDF' }
  },
  '/radar/editArticle': {
    path: '/radar/editArticle',
    name: '/radarEditArticle',
    component: () => import(/* webpackChunkName: "radar" */ '@/views/radar/editArticle'),
    hidden: true,
    meta: { title: '修改文章' }
  },
  '/radar/detail': {
    path: '/radar/detail',
    name: '/radarDetail',
    component: () => import(/* webpackChunkName: "radar" */ '@/views/radar/detail'),
    hidden: true,
    meta: { title: '详情' }
  },
  '/radar/pdfDetail': {
    path: '/radar/pdfDetail',
    name: '/radarPdfDetail',
    component: () => import(/* webpackChunkName: "radar" */ '@/views/radar/pdfDetail'),
    hidden: true,
    meta: { title: 'pdf详情' }
  },
  '/radar/articleDetail': {
    path: '/radar/articleDetail',
    name: '/radarArticleDetail',
    component: () => import(/* webpackChunkName: "radar" */ '@/views/radar/articleDetail'),
    hidden: true,
    meta: { title: '文章详情' }
  }
}
