import request from '@/utils/request'

// 新增消息
export function addMessage (params) {
  return request({
    url: '/roomMessageBatchSend/store',
    method: 'post',
    params: params
  })
}
