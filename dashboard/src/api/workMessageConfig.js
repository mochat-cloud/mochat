import request from '@/utils/request'

// 企业信息添加
export function addInformation (params) {
  return request({
    url: '/workMessageConfig/corpStore',
    method: 'post',
    data: params
  })
}

// 企业成员查看
export function getEnterMembers (params) {
  return request({
    url: '/workMessageConfig/corpShow',
    method: 'get',
    params: params
  })
}

// 列表
export function wechatAuthList (params) {
  return request({
    url: '/workMessageConfig/corpIndex',
    method: 'get',
    params: params
  })
}
