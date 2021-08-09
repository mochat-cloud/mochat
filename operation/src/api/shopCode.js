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

export function wechatApi (params) {
    return request({
        url: '/shopCode/wechat',
        method: 'get',
        params: params
    })
}
