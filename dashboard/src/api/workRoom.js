// 客户群模块
import request from '@/utils/request'

// 客户群分组列表
export function workRoomGroupList (params) {
  return request({
    url: '/workRoomGroup/index',
    method: 'get',
    params: params
  })
}
// 删除分组
export function deleteGroup (params) {
  return request({
    url: '/workRoomGroup/destroy',
    method: 'delete',
    data: params
  })
}
// 新建分组
export function createGroup (params) {
  return request({
    url: '/workRoomGroup/store',
    method: 'post',
    data: params
  })
}
// 更新分组
export function updateGroup (params) {
  return request({
    url: '/workRoomGroup/update',
    method: 'put',
    data: params
  })
}
// 客户群列表
export function workRoomList (params) {
  return request({
    url: '/workRoom/index',
    method: 'get',
    params: params
  })
}
// 同步群
export function synList (params) {
  return request({
    url: '/workRoom/syn',
    method: 'put',
    data: params
  })
}
// 批量修改群
export function batchUpdate (params) {
  return request({
    url: '/workRoom/batchUpdate',
    method: 'put',
    data: params
  })
}
// 客户群成员
export function workContactRoom (params) {
  return request({
    url: '/workContactRoom/index',
    method: 'get',
    params: params
  })
}
// 部门成员列表
export function workDepartmentList (params) {
  return request({
    url: '/workEmployeeDepartment/memberIndex',
    method: 'get',
    params: params
  })
}
// 部门列表
export function departmentList (params) {
  return request({
    url: '/workDepartment/index',
    method: 'get',
    params: params
  })
}
// 统计分页数据
export function statisticsIndex (params) {
  return request({
    url: '/workRoom/statisticsIndex',
    method: 'get',
    params: params
  })
}
// 折线图
export function statistics (params) {
  return request({
    url: '/workRoom/statistics',
    method: 'get',
    params: params
  })
}
// 自动拉群列表
export function workRoomAutoPullList (params) {
  return request({
    url: '/workRoomAutoPull/index',
    method: 'get',
    params: params
  })
}
// 更新
export function autoPullUpdate (params) {
  return request({
    url: '/workRoomAutoPull/update',
    method: 'put',
    data: params
  })
}
// 创建
export function autoPullCreate (params) {
  return request({
    url: '/workRoomAutoPull/store',
    method: 'post',
    data: params
  })
}
// 移动
export function autoPullMove (params) {
  return request({
    url: '/workRoomAutoPull/move',
    method: 'put',
    data: params
  })
}
// 详情
export function autoPullShow (params) {
  return request({
    url: '/workRoomAutoPull/show',
    method: 'get',
    params: params
  })
}
// 标签分组
export function workContactTagGroup (params) {
  return request({
    url: '/workContactTagGroup/index',
    method: 'get',
    params: params
  })
}
// 新建标签
export function addWorkContactTag (params) {
  return request({
    url: '/workContactTag/store',
    method: 'post',
    data: params
  })
}
// 标签列表
export function tagList (params) {
  return request({
    url: '/workContactTag/allTag',
    method: 'get',
    params: params
  })
}

// 群聊
export function roomList (params) {
  return request({
    url: '/workRoom/roomIndex',
    method: 'get',
    params: params
  })
}
