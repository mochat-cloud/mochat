import request, { newRequest } from '@/utils/request'

// 后台首页-首页统计
export function corpData (params) {
  return request({
    url: '/corpData/index',
    method: 'get',
    params: params
  })
}

// 后台首页-折线图
export function lineChat (params) {
  return request({
    url: '/corpData/lineChat',
    method: 'get',
    params: params
  })
}

//
export function tenantIndex (params) {
  return newRequest({
    url: '/external/tenantIndex',
    method: 'get',
    params: params
  })
}
