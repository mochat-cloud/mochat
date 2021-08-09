import { BasicLayout } from '@/layouts'

import { routeList } from './asyncRouter'

export function dealPermissionData (data) {
  const topMenus = []
  const secondMenus = []
  const whiteList = ['/passwordUpdate/index']
  if (data instanceof Array && data.length > 0) {
    data.forEach(item => {
      const { name } = item
      const firstMenuItem = {
        path: '',
        title: name,
        routes: [],
        children: []
      }
      // 二级
      if (item.children) {
        item.children.forEach(second => {
          const { name, icon } = second
          const secondMenuItem = {
            path: '',
            name: '',
            component: BasicLayout,
            meta: { title: name, icon: icon || 'menu' },
            redirect: '',
            children: []
          }
          // 三级
          if (second.children) {
            second.children.forEach(third => {
              const { name, icon, linkUrl, linkType } = third
              if (linkType == 1) {
                if (routeList[linkUrl] && (!routeList[linkUrl].hidden || whiteList.includes(linkUrl))) {
                  routeList[linkUrl].meta = {
                    title: name
                  }
                  if (icon) {
                    Object.assign(routeList[linkUrl].meta, { icon })
                  }
                  firstMenuItem.routes.push(linkUrl)
                  secondMenuItem.children.push(routeList[linkUrl])
                }
              } else {
                const child = {
                  path: linkUrl,
                  name: linkUrl,
                  meta: {
                    title: name,
                    target: '_blank' // 打开到新窗口
                  }
                }
                if (icon) {
                  Object.assign(child.meta, { icon })
                }
                secondMenuItem.children.push(child)
              }
              const url = third.linkUrl
              let actionList
              if (url && routeList[url]) {
                routeList[url].meta.actionList = []
                actionList = routeList[url].meta.actionList
              }
              // 四级
              if (third.children) {
                third.children.forEach(fourth => {
                  const { name, icon, linkUrl } = fourth
                  if (routeList[linkUrl]) {
                    const meta = routeList[linkUrl].meta
                    Object.assign(meta, {
                      title: name,
                      icon: icon || 'menu',
                      actionList: []
                    })
                    firstMenuItem.routes.push(linkUrl)
                    secondMenuItem.children.push(routeList[linkUrl])
                    // 四级菜单操作
                    if (fourth.children) {
                      fourth.children.forEach(fif => {
                        const { linkUrl } = fif
                        meta.actionList.push(linkUrl)
                      })
                    }
                  }
                  if (actionList) {
                    actionList.push(linkUrl)
                  }
                })
              }
            })
          }
          const firstItem = secondMenuItem.children[0]
          if (firstItem && !firstItem.meta.target) {
            const { path } = firstItem
            const reg = /\/\w+/
            let pathTitle
            if (reg.exec(path)) {
              pathTitle = reg.exec(path)[0]
            }
            Object.assign(secondMenuItem, {
              path: pathTitle,
              name: pathTitle,
              redirect: path
            })
            // firstMenuItem.path = pathTitle
          }
          firstMenuItem.children.push(secondMenuItem)
          secondMenus.push(secondMenuItem)
        })
      }
      firstMenuItem.path = (firstMenuItem.children && firstMenuItem.children[0] && firstMenuItem.children[0].path) || ''
      topMenus.push(firstMenuItem)
    })
  }
  const path = topMenus[0].children[0].redirect
  secondMenus.unshift({
    path: '/',
    name: '/',
    component: BasicLayout,
    meta: { title: '/' },
    redirect: path
  })
  return { topMenus, secondMenus, path }
}
