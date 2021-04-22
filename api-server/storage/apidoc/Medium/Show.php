<?php

/**
 * @api {get} /medium/show 查看
 * @apiName medium.show
 * @apiDescription [完成]
 * @apiGroup 素材库
 *
 * @apiParam {Number} id  素材ID
 *
 * @apiSuccess {Number} id  素材ID
 * @apiSuccess {Number} corpId  企业ID
 * @apiSuccess {Number} type  类型 1文本、2图片、3图文、4音频、5视频、6小程序、7文件
 * @apiSuccess {String[]} content  详情,见下面说明
 * @apiSuccess {Number} mediumGroupId  素材分组ID
 *
 * @apiSuccessExample {json} Success-Response:
 *    {
 *          "id": 11,
 *          "corpId": 111,
 *          "type": 1,
 *          "content": {},
 *          "mediumGroupId": 111,
 *     }
 * @apiUse CommonError
 * @apiUse MediumContentResponse
 */
