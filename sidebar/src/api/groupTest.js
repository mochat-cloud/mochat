import request from '@/utils/request'
// 侧边栏-群聊质检列表
export function roomQualityApi (params) {
  return request({
    url: '/roomQuality/index',
    method: 'get',
    params: params
  })
}
// 侧边栏-群聊质检设置群聊
export function setRoomQualityApi (params) {
  return request({
    url: '/roomQuality/setRoom',
    method: 'get',
    params: params
  })
}
// 侧边栏-群聊质检移除群聊
export function delRoomQualityApi (params) {
  return request({
    url: '/roomQuality/deleteRoom',
    method: 'get',
    params: params
  })
}
