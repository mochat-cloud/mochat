// 引入
import request from '@/utils/request'

// 写接口
// 新增消息
export function storeApi (params) {
  return request({
    url: '/autoTag/store',
    method: 'post',
    data: params
  })
}
// 获取列表
export function indexApi (params) {
  return request({
    url: '/autoTag/index',
    method: 'get',
    params: params
  })
}
// 删除
export function destroyApi (params) {
  return request({
    url: '/autoTag/destroy',
    method: 'delete',
    data: params
  })
}
// 规则状态
export function onOffApi (params) {
  return request({
    url: '/autoTag/onOff',
    method: 'put',
    data: params
  })
}
// 详情
export function showApi (params) {
  return request({
    url: '/autoTag/show',
    method: 'get',
    params: params
  })
}
// 详情-关键字 客户数据
export function showContactKeyWordApi (params) {
  return request({
    url: '/autoTag/showContactKeyWord',
    method: 'get',
    params: params
  })
}
// 关键字打标签-定时任务
export function KeyWordTagApi (params) {
  return request({
    url: '/Task/AutoTag/KeyWordTag',
    method: 'get',
    params: params
  })
}
// 详情-客户入群-打标签客户数据
export function showContactRoomApi (params) {
  return request({
    url: '/autoTag/showContactRoom',
    method: 'get',
    params: params
  })
}
// 详情 - 分时段打标签客户数据
export function showContactTimeApi (params) {
  return request({
    url: '/autoTag/showContactTime',
    method: 'get',
    params: params
  })
}
// 会话对象列表
export function toUsersList (params) {
  return request({
    url: '/workMessage/toUsers',
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
