import request from '@/utils/request'

// 敏感词词库列表
export function sensitiveWordsList (params) {
  return request({
    url: '/sensitiveWord/index',
    method: 'get',
    params: params
  })
}

// 删除敏感词
export function delSensitiveWords (params) {
  return request({
    url: '/sensitiveWord/destroy',
    method: 'delete',
    data: params
  })
}

// 敏感词状态修改
export function sensitiveWordsStatus (params) {
  return request({
    url: '/sensitiveWord/statusUpdate',
    method: 'put',
    data: params
  })
}

// 敏感词移动
export function sensitiveWordsMove (params) {
  return request({
    url: '/sensitiveWord/move',
    method: 'put',
    data: params
  })
}

// 新增敏感词
export function sensitiveWordsAdd (params) {
  return request({
    url: '/sensitiveWord/store',
    method: 'post',
    data: params
  })
}

// 敏感词修改分组
export function sensitiveWordsGroupUp (params) {
  return request({
    url: '/sensitiveWordGroup/update',
    method: 'put',
    data: params
  })
}

// 敏感词分组列表
export function sensitiveWordsGroupList (params) {
  return request({
    url: '/sensitiveWordGroup/select',
    method: 'get',
    params: params
  })
}

// 分组添加
export function sensitiveWordsGroupAdd (params) {
  return request({
    url: '/sensitiveWordGroup/store',
    method: 'post',
    data: params
  })
}
