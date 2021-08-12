import request from "../plugins/axios";
import $store from "@/store";
import Storage from "@/store/Storage";

export function contactDataApi (params) {
    return request({
        url: '/lottery/contactData',
        method: 'get',
        params: params
    })
}

export function contactLotteryApi (params) {
    return request({
        url: '/lottery/contactLottery',
        method: 'put',
        data: params
    })
}

export function receiveApi (params) {
    return request({
        url: '/lottery/receive',
        method: 'put',
        data: params
    })
}