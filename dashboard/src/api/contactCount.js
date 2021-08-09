import request from '@/utils/request'

// 客户统计
export function contactInfo (params) {
  return request({
    url: '/count/index',
    method: 'get',
    params
  })
}
