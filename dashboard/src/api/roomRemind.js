// 引入
import request from '@/utils/request'

// 写接口
// 新增
export function storeApi (params) {
  return request({
    url: '/roomRemind/store',
    method: 'post',
    data: params
  })
}
// 详情
export function infoApi (params) {
  return request({
    url: '/roomRemind/info',
    method: 'get',
    params: params
  })
}
// 修改
export function updateApi (params) {
  return request({
    url: '/roomRemind/update',
    method: 'put',
    data: params
  })
}
// 状态
export function statusApi (params) {
  return request({
    url: '/roomRemind/status',
    method: 'get',
    params: params
  })
}
// 获取任务列表
export function indexApi (params) {
  return request({
    url: '/roomRemind/index',
    method: 'get',
    params: params
  })
}
// 定时任务
export function roomRemindApi (params) {
  return request({
    url: '/task/roomRemind',
    method: 'get',
    params: params
  })
}
// 删除
export function destroyApi (params) {
  return request({
    url: '/roomRemind/destroy',
    method: 'delete',
    data: params
  })
}
