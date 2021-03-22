<?php

/**
 * @api {post} /contactMessageBatchSend/store 消息列表-消息创建提交
 * @apiName contactMessageBatchSend.store
 * @apiDescription [已完成]
 * @apiGroup 客户群发管理
 *
 * @apiParam {String}   employeeIds     成员ID多个用英文逗号隔开
 * @apiParamExample {Json}  filterParams  过滤条件
 *  {
 *      "gender": 1,                                       性别（0.未知,1.男,2.女）
 *      "rooms":[5,8,9],                                客户群ID（传数组，如[5,8,9]）
 *      "addTimeStart": "2021-01-15 22:21:13",          添加时间起始日期
 *      "addTimeEnd": "2021-03-15 22:21:13",            添加时间截止日期
 *      "tags":[5,8,9],                                 客户标签id（传数组，如[5,8,9]）
 *      "excludeContacts":   [xx,xx,xx],                排除客户ID 传数组，如[xx,xx,xx]）
 *  }
 *  @apiParamExample {json}  content  消息内容
 *  [
 *      {
 *          "msgType: "image",      消息类型
 *          "text: {
 *              "content": "",
 *          },       文本消息内容
 *          "image": {
 *              "media_id": "MEDIA_ID",
 *              "pic_url":"http://p.qpic.cn/pic_wework/3474110808/7a6344sdadfwehe42060/0"
 *          },                      类型为image时，需传入。图片的media_id，图片链接二选一
 *          "link": {
 *              "title": "消息标题",
 *              "picurl": "https://example.pic.com/path",
 *              "desc": "消息描述",
 *              "url": "https://example.link.com/path"
 *          },                      类型为link时，需传入。
 *          "miniprogram": {
 *              "title": "消息标题",
 *              "pic_media_id": "MEDIA_ID",
 *              "appid": "wx8bd80126147dfAAA",
 *              "page": "/path/index.html"
 *          }                       类型为miniprogram时，需传入。
 *      }
 * ]
 * @apiParam {Number} sendWay 发送方式
 * @apiParam {String} definiteTime  定时发送时间
 *
 * @apiSuccessExample [json-app]
 * {
 * }
 *
 */
