import request from '@/utils/request'

// 客户列表
export function contactList (params) {
  return request({
    url: '/contactBatchAdd/index',
    method: 'get',
    params
  })
}

// 分配客户
export function allot (params) {
  return request({
    url: '/contactBatchAdd/allot',
    method: 'POST',
    data: params
  })
}

// 数据统计
export function dashboard (params) {
  return request({
    url: '/contactBatchAdd/dashboard',
    method: 'GET',
    params
  })
}

// 删除客户
export function contactDel (params) {
  return request({
    url: '/contactBatchAdd/destroy',
    method: 'GET',
    params
  })
}

// 导入记录
export function importData (params) {
  return request({
    url: '/contactBatchAdd/importIndex',
    method: 'GET',
    params
  })
}

// 导入客户
export function importContact (params) {
  return request({
    url: '/contactBatchAdd/importStore',
    method: 'POST',
    data: params
  })
}

// 获取设置
export function getSetting (params) {
  return request({
    url: '/contactBatchAdd/settingEdit',
    method: 'GET',
    params
  })
}

// 修改设置
export function updateSetting (params) {
  return request({
    url: '/contactBatchAdd/settingUpdate',
    method: 'POST',
    data: params
  })
}
