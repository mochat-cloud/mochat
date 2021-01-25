import request from '@/utils/request'

// 欢迎语列表
export function greetingList (params) {
  return request({
    url: '/greeting/index',
    method: 'get',
    params: params
  })
}

// 欢迎语删除
export function delGreeting (params) {
  return request({
    url: '/greeting/destroy',
    method: 'delete',
    data: params
  })
}

// 创建欢迎语
export function greetingStore (params) {
  return request({
    url: '/greeting/store',
    method: 'post',
    data: params
  })
}

// 修改欢迎语
export function upDateGreeting (params) {
  return request({
    url: '/greeting/update',
    method: 'put',
    data: params
  })
}

// 欢迎语详情
export function greetingDetail (params) {
  return request({
    url: '/greeting/show',
    method: 'get',
    params: params
  })
}
