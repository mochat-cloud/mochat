import request from "../plugins/axios";
import $store from "@/store";
import Storage from "@/store/Storage";

export function qrCodeApi (params) {
    return request({
        url: '/roomInfinitePull/qrCode',
        method: 'get',
        params: params
    })
}

