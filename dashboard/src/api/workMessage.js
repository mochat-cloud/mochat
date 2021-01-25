import request from '@/utils/request'

// 企业信息添加
export function corpStore (params) {
  return request({
    url: '/workMessageConfig/corpStore',
    method: 'post',
    data: params
  })
}

// 微信后台配置
export function stepUpdate (params) {
  return request({
    url: '/workMessageConfig/stepUpdate',
    method: 'put',
    data: params
  })
}
// 微信后台配置查看
export function stepCreate (params) {
  return request({
    url: '/workMessageConfig/stepCreate',
    method: 'get',
    params: params
  })
}
// 企业成员
export function workEmployee (params) {
  return request({
    url: '/workMessage/fromUsers',
    method: 'get',
    params: params
  })
}
// 会话对象列表
export function toUsersList (params) {
  return request({
    url: '/workMessage/toUsers',
    method: 'get',
    params: params
  })
}
// 会话内容
export function messageList (params) {
  return request({
    url: '/workMessage/index',
    method: 'get',
    params: params
  })
}
