// 引入
import request from '@/utils/request'
// 新增
export function storeApi (params) {
  return request({
    url: '/roomQuality/store',
    method: 'post',
    data: params
  })
}
// 获取列表
export function indexApi (params) {
  return request({
    url: '/roomQuality/index',
    method: 'get',
    params: params
  })
}
// 规则状态
export function statusApi (params) {
  return request({
    url: '/roomQuality/status',
    method: 'put',
    data: params
  })
}
// 详情
export function infoApi (params) {
  return request({
    url: '/roomQuality/info',
    method: 'get',
    params: params
  })
}
// 修改
export function updateApi (params) {
  return request({
    url: '/roomQuality/update',
    method: 'put',
    data: params
  })
}
// 详情-客户数据
export function showContactApi (params) {
  return request({
    url: '/roomQuality/showContact',
    method: 'get',
    params: params
  })
}
// 删除
export function destroyApi (params) {
  return request({
    url: '/roomQuality/destroy',
    method: 'delete',
    data: params
  })
}
// workMessage/index
// 获取群聊消息
export function indexgroupApi (params) {
  return request({
    url: 'workMessage/index',
    method: 'get',
    params: params
  })
}
// 客户数据-详情
export function contactDetailApi (params) {
  return request({
    url: '/roomQuality/contactDetail',
    method: 'get',
    params: params
  })
}
