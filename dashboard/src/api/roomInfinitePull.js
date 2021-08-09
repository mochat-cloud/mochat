import request from '@/utils/request'

// 获取列表
export function getList (params) {
  return request({
    url: '/roomInfinitePull/index',
    method: 'GET',
    params
  })
}

// 获取详情
export function getInfo (params) {
  return request({
    url: '/roomInfinitePull/info',
    method: 'GET',
    params
  })
}

// 修改
export function update (params) {
  return request({
    url: '/roomInfinitePull/update',
    method: 'PUT',
    data: params
  })
}

// 删除
export function del (params) {
  return request({
    url: '/roomInfinitePull/destroy',
    method: 'DELETE',
    data: params
  })
}

// 新增
export function add (params) {
  return request({
    url: '/roomInfinitePull/store',
    method: 'POST',
    data: params
  })
}
