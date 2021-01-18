import request from '@/utils/request'

// 客户资料卡列表
export function chatTool (params) {
  return request({
    url: '/chatTool/config',
    method: 'get',
    params: params
  })
}
