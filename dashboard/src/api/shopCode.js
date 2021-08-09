import request from '@/utils/request'

// 经纬度查询
export function location (params) {
  return request({
    url: '/shopCode/location',
    method: 'GET',
    params
  })
}

// 关键词列表
export function addressKeyWordList (params) {
  return request({
    url: '/shopCode/addressKeyWordList',
    method: 'GET',
    params
  })
}

// 新增
export function storeApi (params) {
  return request({
    url: '/shopCode/store',
    method: 'post',
    data: params
  })
}
// 修改
export function updateApi (params) {
  return request({
    url: '/shopCode/update',
    method: 'put',
    data: params
  })
}
// 删除
export function destroyApi (params) {
  return request({
    url: '/shopCode/destroy',
    method: 'delete',
    data: params
  })
}
// 详情
export function infoApi (params) {
  return request({
    url: '/shopCode/info',
    method: 'get',
    params: params
  })
}
// 开启状态
export function statusApi (params) {
  return request({
    url: '/shopCode/status',
    method: 'put',
    data: params
  })
}
// 获取列表
export function indexApi (params) {
  return request({
    url: '/shopCode/index',
    method: 'get',
    params: params
  })
}
// workRoomAutoPull/index
// 拉群活码
export function workRoomIndexApi (params) {
  return request({
    url: 'workRoomAutoPull/index',
    method: 'get',
    params: params
  })
}
// 列表查询-城市
export function searchCityApi (params) {
  return request({
    url: '/shopCode/searchCity',
    method: 'get',
    params: params
  })
}
// 门店地址-关键词列表
export function addressKeyWordListApi (params) {
  return request({
    url: '/shopCode/addressKeyWordList',
    method: 'get',
    params: params
  })
}
// 门店地址-经纬度查询
export function locationApi (params) {
  return request({
    url: '/shopCode/location',
    method: 'get',
    params: params
  })
}
// 分享
export function shareApi (params) {
  return request({
    url: '/shopCode/share',
    method: 'get',
    params: params
  })
}
// 设置详情
export function pageInfoApi (params) {
  return request({
    url: '/shopCode/pageInfo',
    method: 'get',
    params: params
  })
}
// 设置页面
export function pageSetApi (params) {
  return request({
    url: '/shopCode/pageSet',
    method: 'post',
    data: params
  })
}
// 数据分析-数据总览
export function showApi (params) {
  return request({
    url: '/shopCode/show',
    method: 'get',
    params: params
  })
}
// 数据分析-客户数据
export function showContactApi (params) {
  return request({
    url: '/shopCode/showContact',
    method: 'get',
    params: params
  })
}
// 数据分析-门店数据
export function showShopApi (params) {
  return request({
    url: '/shopCode/showShop',
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
export function updateEmployeeApi (params) {
  return request({
    url: '/shopCode/updateEmployee',
    method: 'post',
    data: params
  })
}
// 修改门店活码
export function updateQrcodeApi (params) {
  return request({
    url: '/shopCode/updateQrcode',
    method: 'post',
    data: params
  })
}
// 批量打标签
export function batchContactTagsApi (params) {
  return request({
    url: '/shopCode/batchContactTags',
    method: 'put',
    data: params
  })
}
// 公众号列表
export function publicIndexApi (params) {
  return request({
    url: '/officialAccount/index',
    method: 'GET',
    params: params
  })
}
