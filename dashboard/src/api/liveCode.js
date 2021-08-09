import request from '@/utils/request'

export function update (params) {
  return request({
    url: '/roomWelcome/update',
    method: 'PUT',
    data: params
  })
}

export function getList (params) {
  return request({
    url: '/clockIn/index',
    method: 'GET',
    params
  })
}
