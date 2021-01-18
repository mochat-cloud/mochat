<?php

/**
 * @api {get} /contactFieldPivot/index 客户详情 - 用户画像
 * @apiName contactFieldPivot.index
 * @apiDescription  [已完成]
 * @apiGroup 客户
 *
 * @apiParam {Number}  contactId   客户id
 *
 * @apiSuccessExample [json-app]
 * [
 *     {
 *         "contactFieldId":"1",          //客户资料卡id（高级属性id）
 *         "contactFieldPivotId":"1",     //用户画像唯一标识
 *         "name":"手机号"                 //字段名称
 *         "value":"13911234567"          //用户画像值（类型为多选时返回的是数组，为图片时返回未处理的图片链接）
 *         "pictureFlag":"13911234567"    //用户画像值（类型为图片时返回该字段，返回处理后的图片链接）
 *         "type":"1"                     //属性类型
 *         "typeText":"单选"               //属性类型文本
 *         "options":["保密", "男", "女"],  //选项内容
 *     },
 *     ......
 * ]
 */
