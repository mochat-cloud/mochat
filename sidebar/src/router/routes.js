const routes = [
  {
    path: '/',
    redirect: { name: 'codeAuth' }
  },
  {
    path: '/codeAuth',
    name: 'codeAuth',
    component: () => import(/* webpackChunkName: "contact" */ 'views/codeAuth')
  },
  {
    path: '/contact',
    name: 'contact',
    component: () => import(/* webpackChunkName: "contact" */ 'views/contact')
  },
  {
    path: '/contact/remark',
    name: 'contactRemark',
    component: () => import(/* webpackChunkName: "remark" */ 'views/contact/remark')
  },
  {
    path: '/contact/settingTag',
    name: 'contactSettingTag',
    component: () => import(/* webpackChunkName: "settingTag" */ 'views/contact/settingTag')
  },
  {
    path: '/contact/editDetail',
    name: 'contactEditDetail',
    component: () => import(/* webpackChunkName: "editDetail" */ 'views/contact/editDetail')
  },
  {
    path: '/medium',
    name: 'medium',
    component: () => import(/* webpackChunkName: "medium" */ 'views/medium')
  },
  {
    path: '/404',
    name: '404',
    component: () => import(/* webpackChunkName: "404" */ 'views/error/404')
  },
  { path: '/:pathMatch(.*)', redirect: { name: '404' } },
  {
    path: '/contactSop',
    name: 'contactSop',
    component: () => import(/* webpackChunkName: "404" */ 'views/contactSop/contactSop')
  },
  // {
  //   path: '/contactSopIndex',
  //   name: 'contactSopIndex',
  //   component: () => import(/* webpackChunkName: "404" */ 'views/contactSop/contactSopIndex')
  // },
  {
    path: '/roomSop',
    name: 'roomSop',
    component: () => import(/* webpackChunkName: "404" */ 'views/roomSop/roomSop')
  },
  {
    path: '/roomSide',
    name: 'roomSide',
    component: () => import(/* webpackChunkName: "404" */ 'views/roomSop/roomSide')
  }
  // {
  //   path: '/pushRule',
  //   name: 'pushRule',
  //   component: () => import(/* webpackChunkName: "404" */ 'views/roomSop/pushRule')
  // },
  // {
  //   path: '/setCalendar',
  //   name: 'setCalendar',
  //   component: () => import(/* webpackChunkName: "404" */ 'views/roomSop/setCalendar')
  // }
]
export default routes
