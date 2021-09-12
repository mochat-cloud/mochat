import { createRouter, createWebHistory } from 'vue-router'
// import store from '@/store'
import routes from './routes'
// import { Toast } from 'vant'
// eslint-disable-next-line no-unused-vars
import { checkLogin, navShow, initConfig, getCookie } from '@/utils'

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

router.beforeEach(async (to, from, next) => {
  try {
    if (checkLogin(to, from, next) === false) {
      let agentId = to.query.agentId

      if (!agentId) {
        agentId = getCookie('agentId')
      }
      next({ path: '/login', query: { agentId: agentId, target: to.fullPath } })
    } else {
      if (to.matched.some(record => record.meta.initConfig)) {
        // await initConfig(to, from, next)
      }
      next()
    }
  } catch (e) {
    next({ path: '/' })
  }
})

router.afterEach(async (to, from) => {
  navShow(to)
})
export default router
