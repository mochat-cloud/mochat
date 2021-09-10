import request from '@/utils/request'

// 客户统计
export function contactInfo (params) {
  return request({
    url: '/statistic/index',
    method: 'get',
    params
  })
}

// 联系客户数据
export function employeesInfo (params) {
  return request({
    url: '/statistic/employees',
    method: 'get',
    params
  })
}

// 趋势图/列表数据
export function employeesTrendInfo (params) {
  return request({
    url: '/statistic/employeesTrend',
    method: 'get',
    params
  })
}

// 排行榜前十数据
export function topList (params) {
  return request({
    url: '/statistic/topList',
    method: 'get',
    params
  })
}

// 按员工查看
export function getEmployeeCounts (params) {
  return request({
    url: '/statistic/employeeCounts',
    method: 'get',
    params
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
