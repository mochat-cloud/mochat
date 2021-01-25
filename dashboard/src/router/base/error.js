
export const errorPage = [
  {
    path: '/404',
    name: '404',
    component: () => import(/* webpackChunkName: "fail" */ '@/views/error/404'),
    meta: { title: '404' }
  },
  {
    path: '*', redirect: '/404', hidden: true
  }
]
