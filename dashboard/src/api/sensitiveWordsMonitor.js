import request from '@/utils/request'

// 敏感词监控列表
export function sensitiveWordsMonitor (params) {
  return request({
    url: '/sensitiveWordsMonitor/index',
    method: 'get',
    params: params
  })
}

// 敏感词监控对话详情
export function dialogueDetails (params) {
  return request({
    url: '/sensitiveWordsMonitor/show',
    method: 'get',
    params: params
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
