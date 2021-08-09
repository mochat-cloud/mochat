const routes = [
  {
    path: '/',
    redirect: '/codeAuth'
  },
  {
    path: '/codeAuth',
    name: 'CodeAuth',
    component: () => import(/* webpackChunkName: "customer" */ 'views/codeAuth')
  },
  {
    path: '/customer',
    name: 'Customer',
    component: () => import(/* webpackChunkName: "customer" */ 'views/customer')
  },
  {
    path: '/customer/remark',
    name: 'Remark',
    component: () => import(/* webpackChunkName: "remark" */ 'views/customer/remark')
  },
  {
    path: '/customer/settingTag',
    name: 'SettingTag',
    component: () => import(/* webpackChunkName: "settingTag" */ 'views/customer/settingTag')
  },
  {
    path: '/customer/editDetail',
    name: 'EditDetail',
    component: () => import(/* webpackChunkName: "editDetail" */ 'views/customer/editDetail')
  },
  {
    path: '/mediumGroup',
    name: 'MediumGroup',
    component: () => import(/* webpackChunkName: "mediumGroup" */ 'views/mediumGroup')
  },
  {
    path: '/404',
    name: '404',
    component: () => import(/* webpackChunkName: "404" */ 'views/error/404')
  },
  { path: '/:pathMatch(.*)', redirect: { name: '404' } },
  {
    path: '/personalSop',
    name: 'personalSop',
    component: () => import(/* webpackChunkName: "404" */ 'views/personalSop/personalSop')
  },
  // {
  //   path: '/personalSopIndex',
  //   name: 'personalSopIndex',
  //   component: () => import(/* webpackChunkName: "404" */ 'views/personalSop/personalSopIndex')
  // },
  {
    path: '/groupSop',
    name: 'groupSop',
    component: () => import(/* webpackChunkName: "404" */ 'views/groupSop/groupSop')
  },
  {
    path: '/groupSide',
    name: 'groupSide',
    component: () => import(/* webpackChunkName: "404" */ 'views/groupSop/groupSide')
  }
  // {
  //   path: '/pushRule',
  //   name: 'pushRule',
  //   component: () => import(/* webpackChunkName: "404" */ 'views/groupSop/pushRule')
  // },
  // {
  //   path: '/setCalendar',
  //   name: 'setCalendar',
  //   component: () => import(/* webpackChunkName: "404" */ 'views/groupSop/setCalendar')
  // }
]
export default routes
