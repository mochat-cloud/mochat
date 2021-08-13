import {request, request_op} from "../plugins/axios";

//客户数据
export function contactDataApi (params) {
    return request({
        url: '/roomClockIn/contactData',
        method: 'get',
        params: params
    })
}
//排行榜
export function clockInRankingApi (params) {
    return request({
        url: '/roomClockIn/clockInRanking',
        method: 'get',
        params: params
    })
}
//参与打卡活动
export function contactClockInApi (params) {
    return request({
        url: '/roomClockIn/contactClockIn',
        method: 'put',
        data: params
    })
}
//领奖
export function receiveApi (params) {
    return request({
        url: '/roomClockIn/receive',
        method: 'put',
        data: params
    })
}

//获取微信用户信息
export function openUserInfoApi (params) {
    return request_op({
        url: '/openUserInfo/roomClockIn',
        method: 'GET',
        params: params
    })
}