import request from '@/utils/request'

// 弹窗h5接口
export function contactBatchAddDetailApi (params) {
  return request({
    url: '/contactBatchAdd/detail',
    method: 'get',
    params: params
  })
}
