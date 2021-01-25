import store from '@/store'
import router, { newRouter } from '@/router'

// 更改菜单路由
export function exChangeMenu (path) {
  const firstMenu = store.getters.permissionList.filter(item => {
    return item.routes.includes(path)
  })
  if (firstMenu.length !== 0) {
    const title = store.state.permission.topMenuKey.title
    if (firstMenu[0].title !== title) {
      store.commit('SET_TOP_MENU_KEY', firstMenu[0])
    }
    const { children } = firstMenu[0]
    if (children.length > 0) {
      store.commit('SET_SIDE_MENUS', children)
    }
  }
}
export function setBreadcrumb (path) {
  const sideMenus = store.state.permission.sideMenus
  let firstTitle = ''
  let secondTitle = ''
  sideMenus.find(item => {
    const title = item.meta.title
    item.children.find(inner => {
      if (inner.path == path) {
        firstTitle = title
        secondTitle = inner.meta.title
        return true
      }
    })
  })
  store.commit('SET_BREADCRUMB', [firstTitle, secondTitle])
}
export function resetRoutes () {
  router.matcher = newRouter().matcher
  store.commit('CLEAR_ROUTERS')
}
