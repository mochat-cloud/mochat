
export const baseRouterMap = [
  {
    path: '/login',
    name: 'login',
    component: () => import(/* webpackChunkName: "user" */ '@/views/login/login')
  }
]
