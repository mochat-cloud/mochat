<?php
/**
 * @api {get} /agent/oauth 应用验证
 * @apiName GetAgentOauth
 * @apiDescription
 * @apiGroup 企业应用
 *
 * @apiParam {String} agentId 应用ID(以下所有在地址URL获取)
 * @apiParam {String} isJsRedirect 是否跳转回本页面
 * @apiParam {String} act 跳转回本页面时带的自定义参数，如客户标识，素材库标识
 *
 * @apiSuccess {String} url 构造的网页授权链接
 *
 * @apiSuccessExample {json} Success-Response:
 *    {
 *        "url": "https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxCorpId&redirect_uri=http%3a%2f%2fapi.3dept.com%2fcgi-bin%2fquery%3faction%3dget&response_type=code&scope=snsapi_base&state=#wechat_redirect"
 *    }
 *
 * @apiUse CommonError
 */

/**
 * @api {get} -侧边栏域名+/codeAuth 应用验证-js回调
 * @apiName GetCodeAuth1
 * @apiDescription
 * @apiGroup 企业应用
 *
 * @apiSuccess {String} callValues 回调参数.base64(json(val))
 * @apiSuccess {String} callValues.data 回调参数-具体数据
 * @apiSuccess {String} callValues.data.corpId 企业ID
 * @apiSuccess {String} callValues.data.token 用户token
 * @apiSuccess {String} callValues.data.expire token失效时间
 * @apiSuccess {String} callValues.data.agentId 应用ID
 * @apiSuccess {String} callValues.data.isJsRedirect 是否跳转回本页面
 * @apiSuccess {String} callValues.data.act 跳转回本页面时带的自定义参数，如客户标识，素材库标识
 *
 * @apiSuccessExample {json} Success-Response:
 *    {
 *        "callValues": "%7B%0A++++%22code%22%3A+200%2C%0A++++%22msg%22%3A+%22%22%2C%0A++++%22data%22%3A+%7B%0A++++++++%22agentId%22%3A+%221%22%2C%0A++++++++%22act%22%3A+%22mediumGroup%22%2C%0A++++++++%22token%22%3A+%22asdfasdfasdfa%22%2C%0A++++++++%22expire%22%3A+3600%0A++++%7D%0A%7D"
 *    }
 * @apiSuccessExample {json} json-urldecode:
 * {
 *      "code": 200,
 *      "msg": "",
 *      "data": {
 *          "corpId": "1",
 *          "agentId": "1",
 *          "act": "mediumGroup",
 *          "token": "asdfasdfasdfa",
 *          "expire": 3600
 *      }
 * }
 *
 * @apiUse CommonError
 */

