import request from '@/utils/request'

// 客户详情基本信息
export function getWorkContactInfo (params) {
  return request({
    url: '/workContact/show',
    method: 'get',
    params: params
  })
}
// 客户id
export function getWorkContactDetail (params) {
  return request({
    url: '/workContact/detail',
    method: 'get',
    params: params
  })
}

// 编辑客户基本信息
export function editWorkContactInfo (params) {
  return request({
    url: '/workContact/update',
    method: 'put',
    data: params
  })
}

// 用户画像
export function getUserPortrait (params) {
  return request({
    url: '/contactFieldPivot/index',
    method: 'get',
    params: params
  })
}

// 修改用户画像
export function updateUserPortrait (params) {
  return request({
    url: '/contactFieldPivot/update',
    method: 'put',
    data: params
  })
}
// 互动轨迹
export function track (params) {
  return request({
    url: '/workContact/track',
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
// 所有标签分组
export function workContactTagGroup (params) {
  return request({
    url: '/workContactTagGroup/index',
    method: 'get',
    params: params
  })
}
// 上传
export function upload (params) {
  return request({
    url: '/common/upload',
    method: 'post',
    data: params
  })
}
