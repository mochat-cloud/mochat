import request from '@/utils/request'
// 获取规则列表
export function tipSopListApi (params) {
  return request({
    url: '/roomSop/tipSopList',
    method: 'get',
    params: params
  })
}
// 确定
export function tipSopAddRoomApi (params) {
  return request({
    url: '/roomSop/tipSopAddRoom',
    method: 'get',
    params: params
  })
}
// 移除
export function tipSopDelRoomApi (params) {
  return request({
    url: '/roomSop/tipSopDelRoom',
    method: 'get',
    params: params
  })
}
