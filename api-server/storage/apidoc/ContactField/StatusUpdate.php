<?php

/**
 * @api {PUT} /contactField/statusUpdate 状态修改
 * @apiName contactField.statusUpdate
 * @apiDescription [完成]
 * @apiGroup 【客户】高级属性
 *
 * @apiParam {Number} id 高级属性ID
 * @apiParam {Number=0,1} status 状态 0关闭 1开启
 *
 * @apiParamExample {json} Request-Response:
 *    {
 *        "id": 1,
 *        "status": 1
 *    }
 *
 * @apiUse CommonPost
 */
