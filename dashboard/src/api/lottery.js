import request from '@/utils/request'

// 获取列表
export function getList (params) {
  return request({
    url: '/lottery/index',
    method: 'GET',
    params
  })
}

// 新建活动
export function addActivity (params) {
  return request({
    url: 'lottery/store',
    method: 'POST',
    data: params
  })
}

// 客户数据详情
export function dataDetails (params) {
  return request({
    url: 'lottery/showContact',
    method: 'GET',
    params
  })
}

// 详情
export function getDetails (params) {
  return request({
    url: '/lottery/show',
    method: 'GET',
    params
  })
}

// 删除
export function del (params) {
  return request({
    url: '/lottery/destroy',
    method: 'DELETE',
    data: params
  })
}

// 分享
export function share (params) {
  return request({
    url: '/lottery/share',
    method: 'GET',
    params
  })
}

// 修改
export function update (params) {
  return request({
    url: '/lottery/update',
    method: 'PUT',
    data: params
  })
}

// 修改-详情
export function modify (params) {
  return request({
    url: '/lottery/info',
    method: 'GET',
    params
  })
}
// 核销
export function writeOffApi (params) {
  return request({
    url: '/lottery/writeOff',
    method: 'GET',
    params: params
  })
}
// 批量打标签
export function batchContactTagsApi (params) {
  return request({
    url: '/lottery/batchContactTags',
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
