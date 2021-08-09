import request from '@/utils/request'
// 侧边栏-群日历列表
export function roomCalendarApi (params) {
  return request({
    url: '/roomCalendar/index',
    method: 'get',
    params: params
  })
}
export function setRoomCalendarApi (params) {
  return request({
    url: '/roomCalendar/setRoom',
    method: 'get',
    params: params
  })
}
