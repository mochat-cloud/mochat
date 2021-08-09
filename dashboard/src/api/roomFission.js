// 引入
import request from '@/utils/request'
// 写接口
// 新增
export function storeApi (params) {
  return request({
    url: '/roomFission/store',
    method: 'post',
    data: params
  })
}
// 详情-修改
export function infoApi (params) {
  return request({
    url: '/roomFission/info',
    method: 'get',
    params: params
  })
}
// 修改
export function updateApi (params) {
  return request({
    url: '/roomFission/update',
    method: 'put',
    data: params
  })
}
// 删除
export function destroyApi (params) {
  return request({
    url: '/roomFission/destroy',
    method: 'delete',
    data: params
  })
}
// 获取列表
export function indexApi (params) {
  return request({
    url: '/roomFission/index',
    method: 'get',
    params: params
  })
}
// 邀请
export function inviteApi (params) {
  return request({
    url: '/roomFission/invite',
    method: 'post',
    data: params
  })
}
// 客服成员
export function department (params) {
  return request({
    url: '/workDepartment/index',
    method: 'get',
    params: params
  })
}
// 客户标签列表  page：页码必传   perPage：每页显示条数必传
export function contactTagListApi (params) {
  return request({
    url: 'workContactTag/contactTagList',
    method: 'get',
    params: params
  })
}
// 数据总览
export function showApi (params) {
  return request({
    url: '/roomFission/show',
    method: 'get',
    params: params
  })
}
// 群聊数据
export function showRoomApi (params) {
  return request({
    url: '/roomFission/showRoom',
    method: 'get',
    params: params
  })
}
// 客户数据
export function showContactApi (params) {
  return request({
    url: '/roomFission/showContact',
    method: 'get',
    params: params
  })
}
// 核销
export function writeOffApi (params) {
  return request({
    url: '/roomFission/writeOff',
    method: 'get',
    params: params
  })
}
// 公众号列表
export function publicApi (params) {
  return request({
    url: '/officialAccount/index',
    method: 'get',
    params: params
  })
}
