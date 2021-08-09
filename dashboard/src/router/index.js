import Vue from 'vue'
import Router from 'vue-router'
import { baseRouterMap } from './base'

// hack router push callback
const originalPush = Router.prototype.push
Router.prototype.push = function push (location, onResolve, onReject) {
  if (onResolve || onReject) return originalPush.call(this, location, onResolve, onReject)
  return originalPush.call(this, location).catch(err => err)
}

Vue.use(Router)

export default new Router({
  mode: 'history',
  routes: baseRouterMap
})
export function newRouter () {
  return new Router({
    mode: 'history',
    routes: baseRouterMap
  })
}
