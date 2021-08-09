import request from '@/utils/request'

// 渠道码分组列表
export function channelCodeGroup (params) {
  return request({
    url: '/channelCodeGroup/index',
    method: 'get',
    params: params
  })
}

// 渠道码分组修改
export function channelCodeGroupUpdate (params) {
  return request({
    url: '/channelCodeGroup/update',
    method: 'put',
    data: params
  })
}

// 渠道码分组新增
export function channelCodeGroupAdd (params) {
  return request({
    url: '/channelCodeGroup/store',
    method: 'post',
    data: params
  })
}

// 渠道码分组移动
export function channelCodeGroupMove (params) {
  return request({
    url: '/channelCodeGroup/move',
    method: 'put',
    data: params
  })
}

// 渠道码新增
export function channelCodeAdd (params) {
  return request({
    url: '/channelCode/store',
    method: 'post',
    data: params
  })
}

// 渠道码编辑
export function channelCodeUpdate (params) {
  return request({
    url: '/channelCode/update',
    method: 'put',
    data: params
  })
}

// 渠道码列表
export function channelCodeList (params) {
  return request({
    url: '/channelCode/index',
    method: 'get',
    params: params
  })
}

// 渠道码客户
export function channelCodeContact (params) {
  return request({
    url: '/channelCode/contact',
    method: 'get',
    params: params
  })
}

// 渠道码详情
export function channelCodeDetail (params) {
  return request({
    url: '/channelCode/show',
    method: 'get',
    params: params
  })
}

// 统计分页数据
export function statisticsIndex (params) {
  return request({
    url: '/channelCode/statisticsIndex',
    method: 'get',
    params: params
  })
}

// 统计折线图
export function statistics (params) {
  return request({
    url: '/channelCode/statistics',
    method: 'get',
    params: params
  })
}

// 成员下拉框
export function department (params) {
  return request({
    url: '/workDepartment/index',
    method: 'get',
    params: params
  })
}

// 部门成员
export function member (params) {
  return request({
    url: '/workDepartment/memberIndex',
    method: 'get',
    params: params
  })
}
