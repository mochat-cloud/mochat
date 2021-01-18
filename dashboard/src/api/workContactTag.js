import request from '@/utils/request'

// 客户标签列表
export function contactTagList (params) {
  return request({
    url: '/workContactTag/index',
    method: 'get',
    params: params
  })
}

// 删除标签
export function delContactTag (params) {
  return request({
    url: '/workContactTag/destroy',
    method: 'delete',
    data: params
  })
}

// 新增标签
export function addContactTag (params) {
  return request({
    url: '/workContactTag/store',
    method: 'post',
    data: params
  })
}

// 标签详情
export function contactTagDetail (params) {
  return request({
    url: '/workContactTag/detail',
    method: 'get',
    params: params
  })
}

// 移动标签
export function moveContactTag (params) {
  return request({
    url: '/workContactTag/move',
    method: 'put',
    data: params
  })
}

// 编辑标签
export function editContactTag (params) {
  return request({
    url: '/workContactTag/update',
    method: 'put',
    data: params
  })
}

// 分组列表
export function getContactTagGroup (params) {
  return request({
    url: '/workContactTagGroup/index',
    method: 'get',
    params: params
  })
}

// 新增分组
export function addContactTagGroup (params) {
  return request({
    url: '/workContactTagGroup/store',
    method: 'post',
    data: params
  })
}

// 编辑分组
export function editContactTagGroup (params) {
  return request({
    url: '/workContactTagGroup/update',
    method: 'put',
    data: params
  })
}

// 删除分组
export function delContactTagGroup (params) {
  return request({
    url: '/workContactTagGroup/destroy',
    method: 'delete',
    data: params
  })
}

// 分组详情
export function contactTagGroupDetail (params) {
  return request({
    url: '/workContactTagGroup/detail',
    method: 'get',
    params: params
  })
}

// 同步标签
export function syncTag (params) {
  return request({
    url: '/workContactTag/synContactTag',
    method: 'put',
    data: params
  })
}
