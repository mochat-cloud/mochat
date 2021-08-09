import request from '@/utils/request'

// 素材库分组
export function mediumGroup (params) {
  return request({
    url: '/mediumGroup/index',
    method: 'get',
    params: params
  })
}

// 素材库
export function mediumDetail (params) {
  return request({
    url: '/medium/index',
    method: 'get',
    params: params
  })
}
