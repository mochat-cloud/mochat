import request from '@/utils/request'

// 角色列表
export function roleList (params) {
  return request({
    url: '/role/index',
    method: 'get',
    params: params
  })
}
// 角色状态更改
export function statusUpdate (params) {
  return request({
    url: '/role/statusUpdate',
    method: 'put',
    data: params
  })
}
// 添加角色
export function roleStore (params) {
  return request({
    url: '/role/store',
    method: 'post',
    data: params
  })
}
// 修改角色
export function roleUpdate (params) {
  return request({
    url: '/role/update',
    method: 'put',
    data: params
  })
}
// 删除角色
export function roleDelete (params) {
  return request({
    url: '/role/destroy',
    method: 'delete',
    data: params
  })
}
// 角色详情
export function roleDetail (params) {
  return request({
    url: '/role/show',
    method: 'get',
    params: params
  })
}

// 查看人员
export function roleShowEmployee (params) {
  return request({
    url: '/role/showEmployee',
    method: 'get',
    params: params
  })
}
// 查看权限
export function rolePermission (params) {
  return request({
    url: '/role/permissionShow',
    method: 'get',
    params: params
  })
}

// 添加角色权限
export function rolePermissionStore (params) {
  return request({
    url: '/role/permissionStore',
    method: 'post',
    data: params
  })
}
