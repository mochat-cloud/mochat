import request from '@/utils/request'

// 获取列表
export function getList (params) {
  return request({
    url: '/roomCalendar/index',
    method: 'GET',
    params
  })
}

// 添加群聊
export function addRoom (params) {
  return request({
    url: '/roomCalendar/addRoom',
    method: 'POST',
    data: params
  })
}

// 删除群聊
export function delRoom (params) {
  return request({
    url: '/roomCalendar/destroyRoom',
    method: 'DELETE',
    data: params
  })
}

// 新增
export function add (params) {
  return request({
    url: '/roomCalendar/store',
    method: 'POST',
    data: params
  })
}

// 删除
export function del (params) {
  return request({
    url: '/roomCalendar/destroy',
    method: 'DELETE',
    data: params
  })
}

// 详情
export function getInfo (params) {
  return request({
    url: '/roomCalendar/show',
    method: 'GET',
    params
  })
}
// 修改  /roomCalendar/update
export function updateApi (params) {
  return request({
    url: '/roomCalendar/update',
    method: 'put',
    data: params
  })
}
