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

  '/chatTool/customer': {
    path: '/chatTool/customer',
    name: 'customer',
    component: () => import(/* webpackChunkName: "chat-tool" */ '@/views/chatTool/customer'),
    meta: { title: '用户画像' }
  },
  '/chatTool/enhance': {
    path: '/chatTool/enhance',
    name: 'chatEnhance',
    component: () => import(/* webpackChunkName: "chat-tool" */ '@/views/chatTool/enhance'),
    meta: { title: '聊天增强' }
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

  '/mediumGroup/index': {
    path: '/mediumGroup/index',
    name: 'mediumGroupIndex',
    component: () => import(/* webpackChunkName: "medium" */ '@/views/mediumGroup/index'),
    meta: { title: '素材库' }
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
  }

}
