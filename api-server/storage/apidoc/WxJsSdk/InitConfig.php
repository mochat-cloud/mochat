<?php
/**
 * @api {get} /wxJsSdk/config JSSDK-企业、企业应用配置参数
 * @apiName GetWxJsSdkConfig
 * @apiDescription
 * @apiGroup 侧边栏-JSSDK
 *
 * @apiParam {Number} corpId 企业ID(以下所有在地址URL获取)
 * @apiParam {String} [uriPath=''] 当前页面的uri.path部分
 * @apiParam {Number} [agentId] 应用ID(不传时为企业config，传时为应用config)
 *
 * @apiSuccess {String} appid 企业ID
 * @apiSuccess {String} corpid 企业ID
 * @apiSuccess {String} agentid 应用ID
 * @apiSuccess {String} nonceStr 生成签名的随机串
 * @apiSuccess {String} timestamp 生成签名的时间戳
 * @apiSuccess {String} signature 签名
 * @apiSuccess {bool} beta true
 * @apiSuccess {bool} debug 调试
 * @apiSuccess {String[]} jsApiList 需要使用的JS接口列表
 *
 * @apiSuccessExample {json} Success-Response:
 *    {
 *        "appid": "",
 *        "corpid": "",
 *        "agentid": "",
 *        "nonceStr": "",
 *        "timestamp": "",
 *        "signature": "",
 *    }
 *
 * @apiUse CommonError
 */

