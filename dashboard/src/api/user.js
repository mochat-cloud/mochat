import request from '@/utils/request'

// 子账户管理列表
export function subManagementList (params) {
  return request({
    url: '/user/index',
    method: 'get',
    params: params
  })
}

// 创建子账户
export function addSubManagement (params) {
  return request({
    url: '/user/store',
    method: 'post',
    data: params
  })
}

// 修改子账户
export function editSubManagement (params) {
  return request({
    url: '/user/update',
    method: 'put',
    data: params
  })
}

// 更改子账户状态
export function editStatus (params) {
  return request({
    url: '/user/statusUpdate',
    method: 'put',
    data: params
  })
}

// 获取子账户详情
export function getSubManagement (params) {
  return request({
    url: '/user/show',
    method: 'get',
    params: params
  })
}

// 子账户启用
export function changeStatus (params) {
  return request({
    url: '/user/statusUpdate',
    method: 'put',
    data: params
  })
}

// 根据手机号匹配成员部门
export function selectByPhone (params) {
  return request({
    url: '/workDepartment/selectByPhone',
    method: 'get',
    params: params
  })
}

// 角色列表
export function selectRole (params) {
  return request({
    url: '/role/select',
    method: 'get',
    params: params
  })
}
