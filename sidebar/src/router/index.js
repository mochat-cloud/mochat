import { createRouter } from 'vue-router'
import store from '@/store'
import routes from './routes'
import { Toast } from 'vant'
// eslint-disable-next-line no-unused-vars
import { getStorage, removeStorage } from '@/utils'

const router = createRouter({
// history: createWebHistory('/'),
  routes
})

router.beforeEach(async (to, from, next) => {
  try {
    if (from.path == '/codeAuth') {
      removeStorage('contactWxUserId')
      await store.dispatch('GET_USER_INFO')

      if (to.path === '/contact' || to.path === '/contact/remark' || to.path === '/contact/settingTag' || to.path === '/contact/editDetail'
      ) {
        await store.dispatch('GET_CUSTOMER_INFO')
      }
    } else if (to.path !== '/' && to.path !== '/codeAuth') {
      const contactWxUserId = getStorage('contactWxUserId')
      if (contactWxUserId) {
        store.commit('SET_CUSTOMER_WX_USER_ID', contactWxUserId)
      }
      const { userInfo, contactInfo } = store.state.app
      if (Object.keys(userInfo).length == 0) {
        await store.dispatch('GET_USER_INFO')
      }
      if (Object.keys(contactInfo).length == 0) {
        if (to.path === '/contact' || to.path === '/contact/remark' || to.path === '/contact/settingTag' || to.path === '/contact/editDetail'
        ) {
          await store.dispatch('GET_CUSTOMER_INFO')
        }
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
  if (to.path == '/medium' || to.path == '/contact') {
    show = true
  } else {
    show = false
  }
  store.commit('SET_NAV_SHOW', show)
})
export default router
