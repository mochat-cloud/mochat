import request from "@/plugins/axios";
import $store from "@/store";
import Storage from "@/store/Storage";

//获取雷达数据
export function getRadarApi (params) {
    return request({
        url: '/radar/getRadar',
        method: 'GET',
        params: params
    })
}