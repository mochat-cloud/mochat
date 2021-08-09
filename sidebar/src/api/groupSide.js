import request from '@/utils/request'
// 获取规则列表
export function roomManageApi (params) {
  return request({
    url: '/workRoom/roomManage',
    method: 'get',
    params: params
  })
}
