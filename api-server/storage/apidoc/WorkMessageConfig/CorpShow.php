<?php

/**
 * @api {get} /workMessageConfig/corpShow 企业信息查看
 * @apiName workMessageConfig.corpShow
 * @apiDescription [完成]
 * @apiGroup 会话内容存档配置
 *
 * @apiParam {Number} corpId 后台企业ID
 *
 * @apiSuccess {Number} id 会话配置ID
 * @apiSuccess {String} name 企业名称
 * @apiSuccess {String} corpId 后台企业ID
 * @apiSuccess {String} wxCorpid 微信企业ID
 * @apiSuccess {String} socialCode 企业代码
 * @apiSuccess {String} chatAdmin 企业负责人
 * @apiSuccess {String} chatAdminPhone 企业负责人电话
 * @apiSuccess {String} chatAdminIdcard 企业负责人身份证
 * @apiSuccess {Number} chatApplyStatus 状态 0未开通 大于1为已开通
 *
 * @apiSuccessExample {json} Success-Response:
 *    {
 *        "id": 1,
 *        "name": "...",
 *        "corpId": "...",
 *        "wxCorpid": "...",
 *        "socialCode": "...",
 *        "chatAdmin": "...",
 *        "chatAdminPhone": "...",
 *        "chatAdminIdcard": "..."
 *        "chatApplyStatus": "..."
 *    }
 *
 * @apiUse CommonError
 */
