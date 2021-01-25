import request from '@/utils/request'

// 客户-客户详情-用户画像
export function getLossContactList (params) {
  return request({
    url: '/workContact/lossContact',
    method: 'get',
    params: params
  })
}
