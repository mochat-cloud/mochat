import request from "@/plugins/axios";
import $store from "@/store";
import Storage from "@/store/Storage";

//获取用户信息
export function userInfo (params) {
    return request({
        url: '/officialAccount/openUserInfo',
        method: 'GET',
        params: {
            ...params,
            appid: localStorage.getItem('mochat_component_appid')
        }
    })
}
//获取雷达数据
export function getRadarApi (params) {
    return request({
        url: '/radar/getRadar',
        method: 'GET',
        params: params
    })
}