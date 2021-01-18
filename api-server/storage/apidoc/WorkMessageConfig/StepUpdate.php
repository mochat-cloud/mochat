<?php

/**
 * @api {put} /workMessageConfig/stepUpdate 微信后台配置-步骤动作
 * @apiName GetWorkMessageConfigStepUpdate
 * @apiDescription [完成]
 * @apiGroup 会话内容存档配置
 *
 * @apiParam {Number} chatApplyStatus (会话内容)申请进度 0未申请 1填写企业信息 2添加客服提交资料 3配置后台 4完成
 * @apiParam {String[]} chatWhitelistIp 白名单IP[可信IP地址]
 * @apiParam {String[]} chatRsaKey rsa密钥，详情见下
 * @apiParam {String} chatSecret 会话内容存档secret
 * @apiParam {String} chatStatus 存档状态 0不存储 1存储
 *
 * @apiParamExample {json} Success-Request:
 *    {
 *        "chatWhitelistIp": ["192.168.0.12", "192.168.0.13"],
 *        "chatRsaKey": {
 *              "publicKey": "asdfasdfasdf...",
 *              "privateKey": "asdfasdfasdf..",
 *              "version": "1.3"
 *        },
 *        "chatSecret": "asdfsdfk4k23kkasdfaskdjfd",
 *        "chatApplyStatus": 3,
 *        "chatStatus": 1
 *    }
 *
 * @apiUse CommonPost
 */
