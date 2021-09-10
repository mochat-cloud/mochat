import request from '@/utils/request'

// 新增消息
export function addMessage (params) {
  return request({
    url: '/roomMessageBatchSend/store',
    method: 'post',
    params: params
  })
}

// 查询列表
export function index (params) {
  return request({
    url: '/roomMessageBatchSend/index',
    method: 'get',
    params: params
  })
}

// 消息提醒
export function remind (params) {
  return request({
    url: 'roomMessageBatchSend/remind',
    method: 'get',
    params: params
  })
}
// 消息详情-基础信息、数据统计
export function show (params) {
  return request({
    url: 'roomMessageBatchSend/show',
    method: 'get',
    params: params
  })
}

// 删除
export function destroyApi (params) {
  return request({
    url: '/roomMessageBatchSend/destroy',
    method: 'delete',
    data: params
  })
}

// 消息详情-客户群接收详情
export function roomOwnerSendIndex (params) {
  return request({
    url: 'roomMessageBatchSend/roomOwnerSendIndex',
    method: 'get',
    params: params
  })
}

// 消息详情-客户群接收详情
export function roomReceiveIndex (params) {
  return request({
    url: 'roomMessageBatchSend/roomReceiveIndex',
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
