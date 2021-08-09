// 引入
import request from '@/utils/request'

// 写接口
// 新增消息
export function storeApi (params) {
  return request({
    url: '/radar/store',
    method: 'post',
    data: params
  })
}
// 修改消息
export function updateApi (params) {
  return request({
    url: '/radar/update',
    method: 'put',
    data: params
  })
}
// 获取列表
export function indexApi (params) {
  return request({
    url: '/radar/index',
    method: 'get',
    params: params
  })
}
// 删除
export function destroyApi (params) {
  return request({
    url: '/radar/destroy',
    method: 'delete',
    data: params
  })
}
// 详情-修改
export function infoApi (params) {
  return request({
    url: '/radar/info',
    method: 'get',
    params: params
  })
}
// 添加渠道
export function storeChannelApi (params) {
  return request({
    url: '/radar/storeChannel',
    method: 'post',
    data: params
  })
}
// 生成渠道链接
export function storeChannelLinkApi (params) {
  return request({
    url: '/radar/storeChannelLink',
    method: 'post',
    data: params
  })
}
// 渠道列表
export function indexChannelApi (params) {
  return request({
    url: '/radar/indexChannel',
    method: 'get',
    params: params
  })
}
// 渠道链接列表
export function indexChannelLinkApi (params) {
  return request({
    url: '/radar/indexChannelLink',
    method: 'get',
    params: params
  })
}
// 详情
export function showApi (params) {
  return request({
    url: '/radar/show',
    method: 'get',
    params: params
  })
}
// 详情-客户数据
export function showContactApi (params) {
  return request({
    url: '/radar/showContact',
    method: 'get',
    params: params
  })
}
// 详情-渠道数据
export function showChannelApi (params) {
  return request({
    url: '/radar/showChannel',
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
// 生成雷达文章
export function radarArticleApi (params) {
  return request({
    url: '/radar/radarArticle',
    method: 'get',
    params: params
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
