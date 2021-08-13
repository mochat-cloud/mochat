import {request, request_op} from "../plugins/axios";

//海报
export function posterApi (params) {
    return request({
        url: '/workFission/poster',
        method: 'get',
        params: params
    })
}
export function inviteFriendsApi (params) {
    return request({
        url: '/workFission/inviteFriends',
        method: 'get',
        params: params
    })
}
export function taskDataApi (params) {
    return request({
        url: '/workFission/taskData',
        method: 'get',
        params: params
    })
}
export function receiveApi (params) {
    return request({
        url: '/workFission/receive',
        method: 'get',
        params: params
    })
}

//获取微信用户信息
export function openUserInfoApi (params) {
    return request_op({
        url: '/openUserInfo/workFission',
        method: 'GET',
        params: params
    })
}
