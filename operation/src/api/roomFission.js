import {request, request_op} from "../plugins/axios";

//海报
export function posterApi (params) {
    return request({
        url: '/roomFission/poster',
        method: 'get',
        params: params
    })
}
//查看助力进度
export function inviteFriendsApi (params) {
    return request({
        url: '/roomFission/inviteFriends',
        method: 'get',
        params: params
    })
}
//领奖
export function receiveApi (params) {
    return request({
        url: '/roomFission/receive',
        method: 'get',
        params: params
    })
}

//获取微信用户信息
export function openUserInfoApi (params) {
    return request_op({
        url: '/openUserInfo/roomFission',
        method: 'GET',
        params: params
    })
}