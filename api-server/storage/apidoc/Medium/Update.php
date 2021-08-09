<?php

/**
 * @api {put} /medium/update 修改
 * @apiName PutMediumUpdate
 * @apiDescription [完成]
 * @apiGroup 素材库
 *
 * @apiParam {Number} id  素材ID
 * @apiParam {Number} type  类型 1文本、2图片、3图文、4音频、5视频、6小程序、7文件
 * @apiParam {Number=1,2} [isSync]  是否同步素材库(1-同步2-不同步，默认:1)
 * @apiParam {String[]} content  详情,见下面说明
 * @apiParam {Number} mediumGroupId  素材分组ID
 *
 * @apiUse MediumContentRequest
 * @apiUse CommonPost
 */
