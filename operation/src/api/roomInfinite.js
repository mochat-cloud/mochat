import {request, request_op} from "../plugins/axios";

export function qrCodeApi (params) {
    return request({
        url: '/roomInfinitePull/qrCode',
        method: 'get',
        params: params
    })
}

