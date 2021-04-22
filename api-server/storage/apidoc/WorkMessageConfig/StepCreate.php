<?php

/**
 * @api {get} /workMessageConfig/stepCreate 微信后台配置-步骤页面
 * @apiName workMessageConfig.stepCreate
 * @apiDescription [完成]
 * @apiGroup 会话内容存档配置
 *
 * @apiSuccess {Number} id 会话配置ID
 * @apiSuccess {Number} chatApplyStatus (会话内容)申请进度 0未申请 1填写企业信息 2添加客服提交资料 3配置后台 4完成
 * @apiSuccess {String} corpName 企业名称
 * @apiSuccess {String} wxCorpId 微信企业ID
 * @apiSuccess {String} serviceContactUrl 客服联系方式(图片)
 * @apiSuccess {String[]} chatWhitelistIp 白名单IP[可信IP地址]
 * @apiSuccess {String} rsaPublicKey 公钥
 * @apiSuccess {String} rsaPrivateKey 私钥
 *
 * @apiSuccessExample {json} Success-Response:
 *    {
 *        "id": 1,
 *        "chatApplyStatus"： 0,
 *        "corpName"： "测试企业",
 *        "wxCorpId"： "wxdkdkfje9sd0fd9asdf0",
 *        "serviceContactUrl": "http://test.com/a.jpg",
 *        "chatWhitelistIp": ["192.168.0.12", "192.168.0.13"],
 *        "rsaPublicKey": "...",
 *        "rsaPrivateKey": "..."
 *    }
 *
 * @apiUse CommonError
 */
