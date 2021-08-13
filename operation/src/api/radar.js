import {request, request_op} from "../plugins/axios";

//获取雷达数据
export function getRadarApi (params) {
    return request({
        url: '/radar/getRadar',
        method: 'GET',
        params: params
    })
}

//获取微信用户信息
export function openUserInfoApi (params) {
    return request_op({
        url: '/openUserInfo/radar',
        method: 'GET',
        params: params
    })
}