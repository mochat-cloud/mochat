import Vue from 'vue'
import store from '@/store'

const directive = Vue.directive('permission', {
  inserted: function (el, binding, vnode) {
    const action = binding.value
    const actionList = vnode.context.$route.meta.actionList
    if (actionList) {
      if (!actionList.includes(action)) {
        (el.parentNode && el.parentNode.removeChild(el)) || (el.style.display = 'none')
      }
    } else {
      (el.parentNode && el.parentNode.removeChild(el)) || (el.style.display = 'none')
    }
  }
})
Vue.prototype.$setPageBreadcrumb = function (title) {
  const breadcrumb = store.state.permission.breadcrumb
  store.commit('SET_BREADCRUMB', [breadcrumb[0], title])
}
export default directive
