<?php

/**
 * @api {POST} /workMessageConfig/corpStore 企业信息添加
 * @apiName PostWorkMessageConfigCorpStore
 * @apiDescription [完成]
 * @apiGroup 会话内容存档配置
 *
 * @apiParam {String} [corpId] 企业ID
 * @apiParam {String} socialCode 企业代码
 * @apiParam {String} chatAdmin 企业负责人
 * @apiParam {String} chatAdminPhone 企业负责人电话
 * @apiParam {String} chatAdminIdcard 企业负责人身份证
 * @apiParam {Number=1,2} chatApplyStatus 当前申请进度 1填写企业信息 2添加客服提交资料(已经开通会话内容存档功能) 3更新微信后台设置
 *
 * @apiParamExample {json} Success-Request:
 *    {
 *        "chatApplyStatus": 0,
 *        "socialCode": "...",
 *        "chatAdmin": "...",
 *        "chatAdminPhone": "...",
 *        "chatAdminIdcard": "...",
 *        "chatStatus": 1
 *    }
 *
 * @apiSuccess {Number} id 会话配置ID
 *
 * @apiSuccessExample {json} Success-Response:
 *    {
 *        "id": 1
 *    }
 *
 * @apiUse CommonPost
 */
