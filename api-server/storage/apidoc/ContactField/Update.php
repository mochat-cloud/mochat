<?php

/**
 * @api {PUT} /contactField/update 修改
 * @apiName PutContactFieldUpdate
 * @apiDescription [完成]
 * @apiGroup 【客户】高级属性
 *
 * @apiParam {Number} id 高级属性ID
 * @apiParam {String} label 字段名称
 * @apiParam {Number} type 字段类型
 * @apiParam {String[]} options 选项内容
 * @apiParam {Number} order 排序展示
 * @apiParam {Number=0,1} status 状态 0关闭 1开启
 *
 * @apiParamExample {json} Request-Response:
 *    {
 *        "id": 1,
 *        "label": "性别",
 *        "type": 1,
 *        "options": ["保密", "男", "女"],
 *        "order": 99,
 *        "status": 1
 *    }
 *
 * @apiUse CommonPost
 */
