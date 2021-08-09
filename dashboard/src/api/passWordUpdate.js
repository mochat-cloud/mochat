import request from '@/utils/request'

export function passWordUpdate (params) {
  return request({
    url: '/user/passwordUpdate',
    method: 'put',
    data: params
  })
}
