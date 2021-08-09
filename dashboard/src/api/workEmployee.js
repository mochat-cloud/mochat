import request from '@/utils/request'

// 企业成员列表
export function enterMembersList (params) {
  return request({
    url: '/workEmployee/index',
    method: 'get',
    params: params
  })
}

// 同步企业成员
export function syncEmployee (params) {
  return request({
    url: '/workEmployee/synEmployee',
    method: 'put',
    data: params
  })
}

// 同步时间-成员列表搜索条件数据
export function syncTime (params) {
  return request({
    url: '/workEmployee/searchCondition',
    method: 'get',
    data: params
  })
}
