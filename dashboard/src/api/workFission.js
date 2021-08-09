import request from '@/utils/request'

// 获取列表
export function getList (params) {
  return request({
    url: '/workFission/index',
    method: 'GET',
    params
  })
}

// 删除
export function del (params) {
  return request({
    url: '/workFission/destroy',
    method: 'DELETE',
    data: params
  })
}

// 获取详情
export function getDetails (params) {
  return request({
    url: '/workFission/show',
    method: 'GET',
    params
  })
}

// 获取修改用详情
export function getInfo (params) {
  return request({
    url: '/workFission/info',
    method: 'GET',
    params
  })
}

// 修改
export function update (params) {
  return request({
    url: '/workFission/update',
    method: 'PUT',
    data: params
  })
}

// 新增
export function add (params) {
  return request({
    url: '/workFission/store',
    method: 'POST',
    data: params
  })
}

// 获取统计信息
export function getStatistics (params) {
  return request({
    url: '/workFission/statistics',
    method: 'GET',
    params
  })
}

// 获取客户列表
export function getUserList (params) {
  return request({
    url: '/workFission/inviteData',
    method: 'GET',
    params
  })
}

// 获取客户邀请详情
export function getInviteInfo (params) {
  return request({
    url: '/workFission/inviteDetail',
    method: 'GET',
    params
  })
}

// 获取预计发送邀请数量
export function chooseContact (params) {
  return request({
    url: '/workFission/chooseContact',
    method: 'GET',
    params
  })
}

// 发送邀请信息
export function inviteMsg (params) {
  return request({
    url: '/workFission/invite',
    method: 'POST',
    data: params
  })
}
