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
// // 获取群日历
// export function indexApi (params) {
//   return request({
//     url: '/roomCalendar/index',
//     method: 'get',
//     params: params
//   })
// }
