import request from '@/utils/request'

// 菜单列表
export function menuList (params) {
  return request({
    url: 'menu/index',
    method: 'get',
    params: params
  })
}

// 菜单下拉
export function menuSelect (params) {
  return request({
    url: 'menu/select',
    method: 'get',
    params: params
  })
}
// 菜单详情
export function menuDetail (params) {
  return request({
    url: 'menu/show',
    method: 'get',
    params: params
  })
}
// 菜单禁用启用
export function statusUpdate (params) {
  return request({
    url: 'menu/statusUpdate',
    method: 'put',
    data: params
  })
}
// 菜单移除
export function destroy (params) {
  return request({
    url: 'menu/destroy',
    method: 'delete',
    data: params
  })
}
// 添加菜单
export function menuStore (params) {
  return request({
    url: 'menu/store',
    method: 'post',
    data: params
  })
}
// 修改菜单
export function menuUpdate (params) {
  return request({
    url: 'menu/update',
    method: 'put',
    data: params
  })
}
// 已使用图标
export function iconUsed (params) {
  return request({
    url: 'menu/iconIndex',
    method: 'get',
    params: params
  })
}
