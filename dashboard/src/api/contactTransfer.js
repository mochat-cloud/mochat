// 引入
import request from '@/utils/request'
// 在职转接列表
export function infoApi (params) {
  return request({
    url: '/contactTransfer/info',
    method: 'get',
    params: params
  })
}
// 同步离职待分配客户列表
export function saveUnassignedListApi (params) {
  return request({
    url: '/contactTransfer/saveUnassignedList',
    method: 'get',
    params: params
  })
}
// 离职待分配客户列表
export function unassignedListApi (params) {
  return request({
    url: '/contactTransfer/unassignedList',
    method: 'get',
    params: params
  })
}
// 分配 离职/在职客户
export function indexApi (params) {
  return request({
    url: '/contactTransfer/index',
    method: 'post',
    data: params
  })
}
// 离职待分配群列表
export function roomApi (params) {
  return request({
    url: '/contactTransfer/room',
    method: 'get',
    params: params
  })
}
// 分配离职客服群
export function postRoomApi (params) {
  return request({
    url: '/contactTransfer/room',
    method: 'post',
    data: params
  })
}
// 分配记录
export function logApi (params) {
  return request({
    url: '/contactTransfer/log',
    method: 'get',
    params: params
  })
}
// 会话内容
export function messageList (params) {
  return request({
    url: '/workMessage/index',
    method: 'get',
    params: params
  })
}
// 成员下拉框
export function department (params) {
  return request({
    url: '/workDepartment/index',
    method: 'get',
    params: params
  })
}
