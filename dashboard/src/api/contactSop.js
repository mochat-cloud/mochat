import request from '@/utils/request'

// 获取列表
export function getList (params) {
  return request({
    url: '/contactSop/index',
    method: 'GET',
    params
  })
}

// 创建
export function add (params) {
  return request({
    url: '/contactSop/store',
    method: 'POST',
    data: params
  })
}

// 添加客服
export function setEmployee (params) {
  return request({
    url: '/contactSop/setEmployee',
    method: 'PUT',
    data: params
  })
}

// 开关规则
export function switchState (params) {
  return request({
    url: '/contactSop/state',
    method: 'PUT',
    data: params
  })
}

// 获取详情
export function getInfo (params) {
  return request({
    url: '/contactSop/info',
    method: 'GET',
    params
  })
}

// 删除规则
export function del (params) {
  return request({
    url: '/contactSop/destroy',
    method: 'DELETE',
    data: params
  })
}

// 编辑规则
export function edit (params) {
  return request({
    url: '/contactSop/update',
    method: 'PUT',
    data: params
  })
}
