import { createRouter, createWebHistory } from 'vue-router'
import store from '@/store'
import routes from './routes'
import { Toast } from 'vant'
import { getStorage, removeStorage } from '@/utils'

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

router.beforeEach(async (to, from, next) => {
  try {
    if (from.path == '/codeAuth') {
      removeStorage('customerWxUserId')
      await store.dispatch('GET_USER_INFO')
      await store.dispatch('GET_CUSTOMER_INFO')
    } else if (to.path !== '/' && to.path !== '/codeAuth') {
      const customerWxUserId = getStorage('customerWxUserId')
      if (customerWxUserId) {
        store.commit('SET_CUSTOMER_WX_USER_ID', customerWxUserId)
      }
      const { userInfo, customerInfo } = store.state.app
      if (Object.keys(userInfo).length == 0) {
        await store.dispatch('GET_USER_INFO')
      }
      if (Object.keys(customerInfo).length == 0) {
        await store.dispatch('GET_CUSTOMER_INFO')
      }
    }
    next()
  } catch (e) {
    Toast({ position: 'top', message: '获取用户信息失败' })
    next({ path: '/' })
    console.log(e)
  }
})

router.afterEach((to, from) => {
  let show
  if (to.path == '/mediumGroup' || to.path == '/customer') {
    show = true
  } else {
    show = false
  }
  store.commit('SET_NAV_SHOW', show)
})
export default router
