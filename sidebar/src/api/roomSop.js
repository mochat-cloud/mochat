import request from '@/utils/request'

// 弹窗h5接口
export function getSopInfoApi (params) {
  return request({
    url: '/roomSop/getSopInfo',
    method: 'get',
    params: params
  })
}
// 弹窗h5接口
export function logStateApi (params) {
  return request({
    url: '/roomSop/logState',
    method: 'put',
    data: params
  })
}
// 设置群聊
export function setRoomApi (params) {
  return request({
    url: '/roomSop/setRoom',
    method: 'put',
    data: params
  })
}

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

// // 获取群日历
// export function indexApi (params) {
//   return request({
//     url: '/roomCalendar/index',
//     method: 'get',
//     params: params
//   })
// }
