import request from "../plugins/axios";
import $store from "@/store";
import Storage from "@/store/Storage";

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
