import request from '@/utils/request'

export function login (params) {
  return request({
    url: '/user/auth',
    method: 'post',
    data: params
  })
}

// 登录用户详情
export function getInfo (params) {
  return request({
    url: '/user/loginShow',
    method: 'get',
    params: params
  })
}

// 企业下拉列表
export function corpSelect (params) {
  return request({
    url: '/corp/select',
    method: 'get',
    params: params
  })
}

// 登录用户绑定企业
export function corpBind (params) {
  return request({
    url: '/corp/bind',
    method: 'post',
    data: params
  })
}

// 登出
export function logout (params) {
  return request({
    url: '/user/logout',
    method: 'put',
    data: params
  })
}

// 用户角色
export function permissionByUser (params) {
  return request({
    url: '/role/permissionByUser',
    method: 'get',
    data: params
  })
}
