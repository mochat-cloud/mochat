<?php

/**
 * @api {post} /roomMessageBatchSend/store 消息列表-消息创建提交
 * @apiName roomMessageBatchSend.store
 * @apiDescription [已完成]
 * @apiGroup 客户群群发管理
 *
 * @apiParam {String}   batchTitle     群发名称
 * @apiParam {String}   employeeIds    群主ID多个用英文逗号隔开
 * @apiParamExample {Json}  content  消息内容
 *  [
 *      {
 *          "msgType: "image",      消息类型
 *          "text": {
 *              "content": "",
 *          },     文本消息内容
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
 */
