import request from '@/utils/request'
// 构建PC端授权链接
export function componentloginpageApi (params) {
  return request({
    url: '/officialAccount/getPreAuthUrl',
    method: 'GET',
    params
  })
}
// 公众号列表
export function publicApi (params) {
  return request({
    url: '/officialAccount/index',
    method: 'get',
    params: params
  })
}
