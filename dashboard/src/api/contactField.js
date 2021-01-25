import request from '@/utils/request'

/**
 * @param params
 * @returns {*}
 */
// 客户资料卡列表
export function contactFieldList (params) {
  return request({
    url: '/contactField/index',
    method: 'get',
    params: params
  })
}

// 客户资料卡-新增属性
export function addContactField (params) {
  return request({
    url: '/contactField/store',
    method: 'post',
    data: params
  })
}

// 客户资料卡-编辑属性
export function editContactField (params) {
  return request({
    url: '/contactField/update',
    method: 'put',
    data: params
  })
}
// 客户资料卡-删除属性
export function delContactField (params) {
  return request({
    url: '/contactField/destroy',
    method: 'delete',
    data: params
  })
}

// 状态修改
export function statusUpdate (params) {
  return request({
    url: '/contactField/statusUpdate',
    method: 'put',
    data: params
  })
}

// 批量修改
export function batchUpdate (params) {
  return request({
    url: '/contactField/batchUpdate',
    method: 'put',
    data: params
  })
}
