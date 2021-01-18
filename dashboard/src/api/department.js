import request from '@/utils/request'

// 组织架构列表
export function departmentList (params) {
  return request({
    url: '/workDepartment/pageIndex',
    method: 'get',
    params: params
  })
}
// 组织架构成员列表
export function showEmployee (params) {
  return request({
    url: '/workDepartment/showEmployee',
    method: 'get',
    params: params
  })
}
