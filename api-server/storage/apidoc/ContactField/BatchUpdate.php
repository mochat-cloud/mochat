<?php

/**
 * @api {PUT} /contactField/batchUpdate 批量修改
 * @apiName PutContactFieldBatchUpdate
 * @apiDescription 负责人[是否完成]: [已完成]
 * @apiGroup 【客户】高级属性
 *
 * @apiParam {String[]} update 修改
 * @apiParam {Number} update.id 高级属性ID
 * @apiParam {String} update.name 字段名称
 * @apiParam {Number} update.type 字段类型
 * @apiParam {String[]} update.options 选项内容
 * @apiParam {Number} update.order 排序展示
 * @apiParam {Number=0,1} update.status 状态 0关闭 1开启
 * @apiParam {String[]} destroy 删除ID
 *
 * @apiParamExample {json} Request-Response:
 * {
 *    "update": [{
 *        "id": 1,
 *        "name": "性别",
 *        "type": 1,
 *        "options": ["保密", "男", "女"],
 *        "order": 99,
 *        "status": 1
 *    }],
 *    "destroy": [1,2,3]
 * }
 *
 * @apiUse CommonPost
 */
