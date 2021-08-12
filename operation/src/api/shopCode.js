import request from "../plugins/axios";
import $store from "@/store";
import Storage from "@/store/Storage";

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
    return request({
        url: '/shopCode/openUserInfo',
        method: 'GET',
        params: params
    })
}
