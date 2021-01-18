<?php

/**
 * @api {put} /greeting/update 更新提交
 * @apiName greeting.update
 *
 * @apiDescription [已完成]
 * @apiGroup 欢迎语
 *
 * @apiParam {Number} greetingId            欢迎语ID
 * @apiParam {Number=1,2} rangeType         适用成员类型(1-通用2-指定企业成员)
 * @apiParam {String} [employees]           使用成员ID(多个英文半角逗号连接)
 * @apiParam {String} type                  欢迎语类型,多个英文半角逗号连接(1-文本2-图片3-图文6-小程序)
 * @apiParam {String} [words]               欢迎语内容-文本部分
 * @apiParam {Number} [mediumId]            欢迎语内容-素材库部分(素材库ID)
 *
 * @apiUse CommonPost
 */
