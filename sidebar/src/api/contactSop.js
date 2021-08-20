import request from '@/utils/request'

// 弹窗h5接口
export function getSopInfoApi (params) {
  return request({
    url: '/contactSop/getSopInfo',
    method: 'get',
    params: params
  })
}
// 侧边栏h5接口
export function getSopTipInfoApi (params) {
  return request({
    url: '/contactSop/getSopTipInfo',
    method: 'get',
    params: params
  })
}
