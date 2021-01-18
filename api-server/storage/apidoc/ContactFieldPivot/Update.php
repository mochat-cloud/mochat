<?php

/**
 * @api {put} /contactFieldPivot/update 客户详情 - 编辑用户画像
 * @apiName contactFieldPivot.update
 * @apiDescription [已完成]
 * @apiGroup 客户
 *
 * @apiParam {Number}  contactId      客户id
 * @apiParam {Json}    userPortrait   修改内容
 * @apiParamExample {Json}  userPortrait
 *                              [
 *                                 {
 *                                     "contactFieldId":"1",       //客户资料卡id（高级属性id）
 *                                     "contactFieldPivotId":"1",  //用户画像唯一标识
 *                                     "name":"年龄",               //字段名称
 *                                     "value":"18",               //用户画像值（类型为多选时传数组）
 *                                     "type":"1",                 //属性类型
 *                                 }
 *                                 ......
 *                              ]
 *
 * @apiSuccessExample [json-app]
 * {
 * }
 *
 * @apiUse CommonPost
 */