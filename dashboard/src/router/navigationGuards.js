import router from './index'
import store from '@/store'
import storage from 'store'
import NProgress from 'nprogress' // progress bar
import '@/components/NProgress/nprogress.less' // progress bar custom style
import { setDocumentTitle } from '@/utils/domUtil'
import { exChangeMenu, setBreadcrumb } from '@/utils/menu'

NProgress.configure({ showSpinner: false }) // NProgress Configuration

const whiteList = ['login'] // no redirect whitelist
const loginRoutePath = '/login'
router.beforeEach(async (to, from, next) => {
  NProgress.start() // start progress bar
  to.meta && (typeof to.meta.title !== 'undefined' && setDocumentTitle(`${to.meta.title} - Powered by MoChat`))
  /* has token */
  if (storage.get('ACCESS_TOKEN') && to.path !== '/login') {
    const route = store.getters.addRouters
    if (route.length == 0) {
      await store.dispatch('getPermissionList')
      const routes = store.getters.addRouters
      if (routes.length > 0) {
        router.addRoutes(store.getters.addRouters)
        // 请求带有 redirect 重定向时，登录自动重定向到该地址
        let redirect = decodeURIComponent(from.query.redirect || to.path)
        if (redirect == '/') {
          redirect = store.getters.defaultRoutePath
        }
        exChangeMenu(redirect)
        if (to.path === redirect) {
          // set the replace: true so the navigation will not leave a history record
          next({ ...to, replace: true })
        } else {
          // 跳转到目的路由
          next({ path: redirect })
        }
      } else {
        if (to.path == loginRoutePath) {
          next()
        } else {
          next({ path: loginRoutePath, replace: true })
        }
      }
    } else {
      if (to.path == '/') {
        const defaultRoutePath = store.getters.defaultRoutePath || loginRoutePath
        to = { ...to, path: defaultRoutePath }
      }
      exChangeMenu(to.path)
      next()
    }
  } else {
    if (whiteList.includes(to.name)) {
      // 在免登录白名单，直接进入
      next()
    } else {
      next({ path: loginRoutePath, query: { redirect: to.fullPath } })
      NProgress.done() // if current page is login will not trigger afterEach hook, so manually handle it
    }
  }
  NProgress.done()
})
router.afterEach((to, from) => {
  setBreadcrumb(to.path)
  NProgress.done() // finish progress bar
})
