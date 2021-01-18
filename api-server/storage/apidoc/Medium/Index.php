<?php

/**
 * @api {get} /medium/index 列表
 * @apiName GetMediumIndex
 * @apiDescription [完成]
 * @apiGroup 素材库
 *
 * @apiParam {String} [mediumGroupId]  分组ID
 * @apiParam {String} [searchStr]  搜索内容
 * @apiParam {Number} [type=0]  类型 0所有 1文本、2图片、3图文、4音频、5视频、6小程序、7文件
 *
 * @apiSuccess {Number} id  素材ID
 * @apiSuccess {String} title  标题
 * @apiSuccess {Number} type  类型 1文本、2图片、3图文、4音频、5视频、6小程序、7文件
 * @apiSuccess {String[]} content  内容,见下面说明
 * @apiSuccess {Number} mediumGroupId  素材分组ID
 * @apiSuccess {String} mediumGroupName  素材分组名称
 * @apiSuccess {String} mediaId  wx.media_id
 * @apiSuccess {Number} userId  上传者ID
 * @apiSuccess {String} userName  上传者名称
 * @apiSuccess {String} createdAt  添加时间
 *
 * @apiSuccessExample {json} Success-Response:
 *    [{
 *          "id": 11,
 *          "title": "...",
 *          "type": 1,
 *          "content": {},
 *          "mediumGroupId": 111,
 *          "mediumGroupName": "..",
 *          "userId": 111,
 *          "userName": "...",
 *          "createdAt": "2019-2-22",
 *     }]
 * @apiUse CommonError
 * @apiUse MediumContentResponse
 */
