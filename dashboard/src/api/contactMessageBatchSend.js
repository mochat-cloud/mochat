import request from '@/utils/request'

// 消息列表
export function indexApi (params) {
  return request({
    url: '/contactMessageBatchSend/index',
    method: 'get',
    params
  })
}
// 新建
export function storeApi (params) {
  return request({
    url: '/contactMessageBatchSend/store',
    method: 'post',
    data: params
  })
}
// 基础信息、数据统计
export function showApi (params) {
  return request({
    url: '/contactMessageBatchSend/show',
    method: 'get',
    params
  })
}
// 删除
export function destroyApi (params) {
  return request({
    url: '/contactMessageBatchSend/destroy',
    method: 'delete',
    data: params
  })
}
// 预览
export function messageShowApi (params) {
  return request({
    url: '/contactMessageBatchSend/messageShow',
    method: 'get',
    params
  })
}
// 提醒发送
export function remindApi (params) {
  return request({
    url: '/contactMessageBatchSend/remind',
    method: 'post',
    data: params
  })
}
// 客户详情
export function contactReceiveIndexApi (params) {
  return request({
    url: '/contactMessageBatchSend/contactReceiveIndex',
    method: 'get',
    params
  })
}
// 成员详情
export function employeeSendIndexApi (params) {
  return request({
    url: '/contactMessageBatchSend/employeeSendIndex',
    method: 'get',
    params
  })
}
