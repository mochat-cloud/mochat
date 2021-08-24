import request from '@/utils/request'

// 获取列表
export function getList (params) {
  return request({
    url: '/roomWelcome/index',
    method: 'GET',
    data: params
  })
}

// 删除
export function del (params) {
  return request({
    url: '/roomWelcome/destroy',
    method: 'DELETE',
    data: params
  })
}

// 获取详情
export function getDetail (params) {
  return request({
    url: '/roomWelcome/show',
    method: 'GET',
    params
  })
}

// 修改
export function update (params) {
  return request({
    url: '/roomWelcome/update',
    method: 'PUT',
    data: params
  })
}

// 新增
export function add (params) {
  return request({
    url: '/roomWelcome/store',
    method: 'POST',
    data: params
  })
}
