import request from '@/utils/request'

// 微信授权列表
export function wechatAuthList (params) {
  return request({
    url: '/corp/index',
    method: 'get',
    params: params
  })
}

// 新增企业微信授权
export function addEnterpriseWeChat (params) {
  return request({
    url: '/corp/store',
    method: 'post',
    data: params
  })
}

// 编辑企业微信授权
export function editEnterpriseWeChat (params) {
  return request({
    url: '/corp/update',
    method: 'put',
    data: params
  })
}

// 查看企业微信授权
export function lookEnterpriseWeChat (params) {
  return request({
    url: '/corp/show',
    method: 'get',
    params: params
  })
}
