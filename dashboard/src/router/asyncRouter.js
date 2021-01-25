export const routeList = {
  '/corp/index': {
    path: '/corp/index',
    name: 'corpIndex',
    component: () => import('@/views/corp/index'),
    meta: { title: '企业微信授权' }
  },
  '/user/index': {
    path: '/user/index',
    name: 'user',
    component: () => import('@/views/user/index'),
    meta: { title: '子账户管理' }
  },
  '/passwordUpdate/index': {
    path: '/passwordUpdate/index',
    name: 'passwordUpdate',
    hidden: true,
    component: () => import('@/views/passwordUpdate/index'),
    meta: { title: '修改密码' }
  },
  '/workEmployee/index': {
    path: '/workEmployee/index',
    name: 'workEmployeeIndex',
    component: () => import('@/views/workEmployee/index'),
    meta: { title: '企业成员' }
  },
  '/workMessageConfig/corpShow': {
    path: '/workMessageConfig/corpShow',
    name: 'workMessageConfigCorpShow',
    component: () => import('@/views/workMessageConfig/corpShow'),
    meta: { title: '聊天记录申请' }
  },
  '/role/index': {
    path: '/role/index',
    name: 'roleIndex',
    component: () => import('@/views/role/index'),
    meta: { title: '角色管理' }
  },
  '/role/permissionShow': {
    path: '/role/permissionShow',
    name: 'rolePermissionShow',
    hidden: true,
    component: () => import('@/views/role/permissionShow'),
    meta: { title: '权限设置' }
  },
  '/chatTool/config': {
    path: '/chatTool/config',
    name: 'chatTool',
    component: () => import('@/views/chatTool/config'),
    meta: { title: '聊天侧边栏' }
  },
  '/menu/index': {
    path: '/menu/index',
    name: 'menuIndex',
    component: () => import('@/views/menu/index'),
    meta: { title: '菜单' }
  },
  '/department/index': {
    path: '/department/index',
    name: 'departmentIndex',
    component: () => import('@/views/department/index'),
    meta: { title: '组织架构' }
  },
  '/workContact/index': {
    path: '/workContact/index',
    name: 'workContactIndex',
    // hideChildrenInMenu: true,
    component: () => import('@/views/workContact/index'),
    meta: { title: '客户列表' }
  },
  '/workContact/contactFieldPivot': {
    path: '/workContact/contactFieldPivot',
    name: 'contactFieldPivot',
    hidden: true,
    component: () => import('@/views/workContact/contactFieldPivot'),
    meta: { title: '客户详情' }
  },
  '/contactField/index': {
    path: '/contactField/index',
    name: 'contactField',
    component: () => import('@/views/contactField/index'),
    meta: { title: '客户资料卡' }
  },
  '/lossContact/index': {
    path: '/lossContact/index',
    name: 'lossContact',
    component: () => import('@/views/lossContact/index'),
    meta: { title: '流失客户' }
  },
  '/workContactTag/index': {
    path: '/workContactTag/index',
    name: 'workContactTag',
    component: () => import('@/views/workContactTag/index'),
    meta: { title: '标签管理' }
  },
  '/workRoom/index': {
    path: '/workRoom/index',
    name: 'workRoomIndex',
    component: () => import('@/views/workRoom/index'),
    meta: { title: '群列表' }
  },
  '/workRoom/statistics': {
    path: '/workRoom/statistics',
    name: 'statistics',
    hidden: true,
    component: () => import('@/views/workRoom/statistics'),
    meta: { title: '群统计' }
  },
  '/workRoomAutoPull/index': {
    path: '/workRoomAutoPull/index',
    name: 'workRoomAutoPull',
    component: () => import('@/views/workRoomAutoPull/index'),
    meta: { title: '自动拉群列表' }
  },
  '/workRoomAutoPull/store': {
    path: '/workRoomAutoPull/store',
    name: 'workRoomAutoPullStore',
    hidden: true,
    component: () => import('@/views/workRoomAutoPull/store'),
    meta: { title: '新建拉群' }
  },
  '/mediumGroup/index': {
    path: '/mediumGroup/index',
    name: 'mediumGroupIndex',
    component: () => import('@/views/mediumGroup/index'),
    meta: { title: '素材库' }
  },
  '/workMessage/index': {
    path: '/workMessage/index',
    name: 'workMessageIndex',
    component: () => import('@/views/workMessage/index'),
    meta: { title: '聊天记录' }
  },
  '/workMessage/toUsers': {
    path: '/workMessage/toUsers',
    name: 'workMessageToUsers',
    hidden: true,
    component: () => import('@/views/workMessage/toUsers'),
    meta: { title: '聊天记录查看' }
  },
  '/sensitiveWords/index': {
    path: '/sensitiveWords/index',
    name: 'sensitiveWordsIndex',
    component: () => import('@/views/sensitiveWords/index'),
    meta: { title: '敏感词词库' }
  },
  '/sensitiveWordsMonitor/index': {
    path: '/sensitiveWordsMonitor/index',
    name: 'sensitiveWordsMonitorIndex',
    component: () => import('@/views/sensitiveWordsMonitor/index'),
    meta: { title: '敏感词监控' }
  },
  '/greeting/index': {
    path: '/greeting/index',
    name: 'greetingIndex',
    component: () => import('@/views/greeting/index'),
    meta: { title: '欢迎语' }
  },
  '/greeting/store': {
    path: '/greeting/store',
    name: 'greetingStore',
    hidden: true,
    component: () => import('@/views/greeting/store'),
    meta: { title: '新建欢迎语' }
  },
  '/channelCode/index': {
    path: '/channelCode/index',
    name: 'channelCodeIndex',
    component: () => import('@/views/channelCode/index'),
    meta: { title: '渠道码' }
  },
  '/channelCode/store': {
    path: '/channelCode/store',
    name: 'channelCodeStore',
    hidden: true,
    component: () => import('@/views/channelCode/store'),
    meta: { title: '新建渠道码' }
  },
  '/channelCode/statistics': {
    path: '/channelCode/statistics',
    name: 'channelCodeStatistics',
    hidden: true,
    component: () => import('@/views/channelCode/statistics'),
    meta: { title: '统计' }
  },
  '/corpData/index': {
    path: '/corpData/index',
    name: 'systemHomePage',
    component: () => import('@/views/corpData/index'),
    meta: { title: '系统首页' }
  }
}
