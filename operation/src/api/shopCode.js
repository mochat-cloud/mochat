import {request, request_op} from "../plugins/axios";

export function areaCodeApi (params) {
    return request({
        url: '/shopCode/areaCode',
        method: 'get',
        params: params
    })
}

export function weChatSdkConfig (params) {
    return request({
        url: '/shopCode/weChatSdkConfig',
        method: 'get',
        params: params
    })
}

//获取微信用户信息
export function openUserInfoApi (params) {
    return request_op({
        url: '/openUserInfo/shopCode',
        method: 'GET',
        params: params
    })
}
