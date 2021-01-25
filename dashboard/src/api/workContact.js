import request from '@/utils/request'

// 客户-客户详情-用户画像
export function getUserPortrait (params) {
  return request({
    url: '/contactFieldPivot/index',
    method: 'get',
    params: params
  })
}

// 客户-客户详情-编辑用户画像
export function editUserPortrait (params) {
  return request({
    url: '/contactFieldPivot/update',
    method: 'put',
    data: params
  })
}

// 客户-客户详情-批量打标签
export function addTag (params) {
  return request({
    url: '/workContact/batchLabeling',
    method: 'post',
    data: params
  })
}

// 客户-客户详情-查看客户详情基本信息
export function getWorkContactInfo (params) {
  return request({
    url: '/workContact/show',
    method: 'get',
    params: params
  })
}

// 客户-客户详情-编辑客户详情基本信息
export function editWorkContactInfo (params) {
  return request({
    url: '/workContact/update',
    method: 'put',
    data: params
  })
}

// 客户-客户列表
export function workContactList (params) {
  return request({
    url: '/workContact/index',
    method: 'get',
    params: params
  })
}

// 所有标签
export function allTag (params) {
  return request({
    url: '/workContactTag/allTag',
    method: 'get',
    params: params
  })
}

// 群聊列表下拉框
export function groupChatList (params) {
  return request({
    url: '/workRoom/roomIndex',
    method: 'get',
    params: params
  })
}

// 客户来源下拉框
export function customersSource (params) {
  return request({
    url: '/workContact/source',
    method: 'get',
    params: params
  })
}

// 客户 - 客户列表筛选 -- 用户画像
export function UserPortraitList (params) {
  return request({
    url: '/contactField/portrait',
    method: 'get',
    params: params
  })
}

// 客户 - 同步客户
export function synContact (params) {
  return request({
    url: '/workContact/synContact',
    method: 'put',
    data: params
  })
}

// 客户 - 互动轨迹
export function track (params) {
  return request({
    url: '/workContact/track',
    method: 'get',
    params: params
  })
}
