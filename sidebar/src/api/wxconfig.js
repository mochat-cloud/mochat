import request from '@/utils/request'

//
export function getConfigInfo (params) {
  return request({
    url: '/wxJsSdk/config',
    method: 'get',
    params: params
  })
}
export function getAgentInfo (params) {
  return request({
    url: '/agent/oauth',
    method: 'get',
    params: params
  })
}

export function getUserInfo (params) {
  return request({
    url: '/user/loginShow',
    method: 'get',
    params: params
  })
}
