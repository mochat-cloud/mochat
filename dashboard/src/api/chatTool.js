import request from '@/utils/request'

// 应用信息
export function chatTool (params) {
  return request({
    url: '/chatTool/config',
    method: 'get',
    params: params
  })
}
// 添加应用
export function addChatTool (params) {
  return request({
    url: '/agent/store',
    method: 'post',
    data: params
  })
}
