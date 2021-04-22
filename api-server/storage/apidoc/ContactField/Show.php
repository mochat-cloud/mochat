<?php

/**
 * @api {get} /contactField/show 查看
 * @apiName contactField.show
 * @apiDescription [完成]
 * @apiGroup 【客户】高级属性
 *
 * @apiParam {Number} id  高级属性ID
 *
 * @apiSuccess {Number} id  高级属性ID
 * @apiSuccess {String} name 字段标识(性别标识为gender)
 * @apiSuccess {String} label 字段名称
 * @apiSuccess {Number} type  填写格式 0text 1radio 2 checkbox 3select 4file 5textarea 6date 7dateTime 8number 9phone 10email 11picture
 * @apiSuccess {String} typeText 填写格式文本
 * @apiSuccess {String[]} options  选项内容
 * @apiSuccess {Number} order  排序展示
 * @apiSuccess {Number} status  状态 0关闭 1开启
 * @apiSuccess {Number} isSys  是否为系统初始化数据 0否1是
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *          "id": 1,
 *          "name": "gender",
 *          "label": "性别",
 *          "type": 1,
 *          "typeText": "单选",
 *          "options": ["保密", "男", "女"],
 *          "order": 99,
 *          "status": 1,
 *          "isSys": 0
 *      }
 *
 * @apiUse CommonError
 */
