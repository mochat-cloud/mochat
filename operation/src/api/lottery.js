import {request, request_op} from "../plugins/axios";

export function contactDataApi (params) {
    return request({
        url: '/lottery/contactData',
        method: 'post',
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

//获取微信用户信息
export function openUserInfoApi (params) {
    return request_op({
        url: '/openUserInfo/lottery',
        method: 'GET',
        params: params
    })
}