<?php

/**
 * @api {get} /roomMessageBatchSend/show 消息详情-基础信息、数据统计
 * @apiName roomMessageBatchSend.show
 * @apiDescription [已完成]
 * @apiGroup 客户群群发管理
 *
 * @apiParam {Number}   batchId     群发ID
 *
 * @apiSuccessExample [json-app]
 * {
 *     "id": 1,                // ID
 *     "creator": "王朔",       // 创建者
 *     "createAt": "2021-01-15", // 创建时间
 *     "seedRooms": {
            "total": 8, // 群聊总数
 *          "list": [
 *              {
 *                  "id":   "1",                // 客户群ID
 *                  "name": "php交流群",        // 客户群名称
 *              }
 *              .....
 *          ]
 *      },      // 群发对象
 *     "content": [
 *          {
 *              "msgType: "image",      消息类型
 *              "text: {
 *                  "content": "",
 *              },          传入时存在，文本消息内容
 *              "image": {
 *                  "media_id": "MEDIA_ID",
 *                  "pic_url":"http://p.qpic.cn/pic_wework/3474110808/7a6344sdadfwehe42060/0"
 *              },          传入时存在，类型为image时返回
 *              "link": {
 *                  "title": "消息标题",
 *                  "picurl": "https://example.pic.com/path",
 *                  "desc": "消息描述",
 *                  "url": "https://example.link.com/path"
 *              },          传入时存在，类型为link时返回
 *              "miniprogram": {
 *                  "title": "消息标题",
 *                  "pic_media_id": "MEDIA_ID",
 *                  "appid": "wx8bd80126147dfAAA",
 *                  "page": "/path/index.html"
 *              }           传入时存在，类型为miniprogram时返回
 *          }
 *      ],
 *      "sendTime": "2021-03-15 18:12:12",         // 发送时间
 *      "sendTotal": 0,       // 已发送群主
 *      "receivedTotal": 0,         // 送达群聊
 *      "notSendTotal": 0,           // 未发送群主
 *      "notReceivedTotal": 0,      // 未送达群聊
 * }
 */
