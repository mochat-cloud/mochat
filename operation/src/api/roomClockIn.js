import request from "../plugins/axios";
import $store from "@/store";
import Storage from "@/store/Storage";

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
        url: '/roomClockIn/roomClockInRanking',
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
//获取用户信息
export function userInfo (params) {
    return request({
        url: '/officialAccount/openUserInfo',
        method: 'GET',
        params: {
            ...params,
            appid: localStorage.getItem('appid')
        }
    })
}