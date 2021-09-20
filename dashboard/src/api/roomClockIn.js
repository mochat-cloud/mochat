import request from '@/utils/request'

//  新增
export function addActivity (params) {
  return request({
    url: '/roomClockIn/store',
    method: 'post',
    data: params
  })
}
//  修改
export function updateActivity (params) {
  return request({
    url: '/roomClockIn/update',
    method: 'put',
    data: params
  })
}
//  删除
export function delList (params) {
  return request({
    url: '/roomClockIn/destroy',
    method: 'delete',
    data: params
  })
}
//  获取列表
export function getList (params) {
  return request({
    url: '/roomClockIn/index',
    method: 'get',
    params: params
  })
}
//  详情
export function detailsList (params) {
  return request({
    url: '/roomClockIn/show',
    method: 'get',
    params: params
  })
}
//  详情-客户数据详情
export function clientDetails (params) {
  return request({
    url: '/roomClockIn/showContact',
    method: 'get',
    params: params
  })
}
//  批量打标签
export function batchLabel (params) {
  return request({
    url: '/roomClockIn/batchContactTags',
    method: 'put',
    data: params
  })
}
//  详情-修改
export function modifyDetails (params) {
  return request({
    url: '/roomClockIn/info',
    method: 'get',
    params: params
  })
}
// 创建客户标签   groupId：分组id必传   tagName：标签名称必传
export function setclientTags (params) {
  return request({
    url: '/workContactTag/store',
    method: 'post',
    data: params
  })
}
// 客户标签列表  page：页码必传   perPage：每页显示条数必传
export function clientTagsReceive (params) {
  return request({
    url: '/workContactTag/contactTagList',
    method: 'get',
    params: params
  })
}
// 详细打卡天数
export function dayDetail (params) {
  return request({
    url: '/roomClockIn/dayDetail',
    method: 'get',
    params: params
  })
}
// 新建标签组
export function newTagGroup (params) {
  return request({
    url: '/workContactTagGroup/store',
    method: 'post',
    data: params
  })
}
// 批量打标签
export function batchContactTagsApi (params) {
  return request({
    url: '/roomClockIn/batchContactTags',
    method: 'put',
    data: params
  })
}
// 公众号列表
export function publicIndexApi (params) {
  return request({
    url: '/officialAccount/index',
    method: 'GET',
    params: params
  })
}
