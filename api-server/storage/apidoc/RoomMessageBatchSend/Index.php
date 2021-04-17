<?php

/**
 * @api {get} /roomMessageBatchSend/index 消息列表
 * @apiName roomMessageBatchSend.index
 * @apiDescription [已完成]
 * @apiGroup 客户群群发管理
 *
 * @apiParam {Number}   page     页码
 * @apiParam {Number}   perPage  每页显示条数
 *
 * @apiSuccessExample [json-app]
 *      "page":{
 *          "perPage" : "10",    //每页显示数
 *          "total" : "1",       //总条数
 *          "totalPage" : "3",   //总页数
 *      },
 *      "list": [
 *          {
 *              "id": 1,
 *              "batchTitle": "的", // 群发名称
 *              "sendWay": 1, // 群发类型
 *              "content": [
 *                  {
 *                      "msgType: "image",      消息类型
 *                      "text: {
 *                          "content": "",
 *                      },          传入时存在，文本消息内容
 *                      "image": {
 *                          "media_id": "MEDIA_ID",
 *                          "pic_url":"http://p.qpic.cn/pic_wework/3474110808/7a6344sdadfwehe42060/0"
 *                      },          传入时存在，类型为image时返回
 *                      "link": {
 *                          "title": "消息标题",
 *                          "picurl": "https://example.pic.com/path",
 *                          "desc": "消息描述",
 *                          "url": "https://example.link.com/path"
 *                      },          传入时存在，类型为link时返回
 *                      "miniprogram": {
 *                          "title": "消息标题",
 *                          "pic_media_id": "MEDIA_ID",
 *                          "appid": "wx8bd80126147dfAAA",
 *                          "page": "/path/index.html"
 *                      }           传入时存在，类型为miniprogram时返回
 *                  }
 *              ],                             // 群发消息
 *              "sendTime": "2021-03-15 22:21:13", // 发送时间
 *              "sendTotal": 0, // 已发送成员
 *              "receivedTotal": 0, // 送达客户
 *              "notSendTotal": 0, // 未发送成员
 *              "notReceivedTotal": 0, // 未送达客户
 *          },
 *          .....
 *      ]
 */
