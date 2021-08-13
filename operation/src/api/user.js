import {request, request_op} from "../plugins/axios";

//获取微信用户信息
export function openUserInfoApi (params) {
  return request({
    url: '/officialAccount/openUserInfo',
    method: 'GET',
    params: params
  })
}