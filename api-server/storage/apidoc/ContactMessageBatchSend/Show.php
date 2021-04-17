<?php

/**
 * @api {get} /contactMessageBatchSend/show 消息详情-基础信息、数据统计
 * @apiName contactMessageBatchSend.show
 * @apiDescription [已完成]
 * @apiGroup 客户群发管理
 *
 * @apiParam {Number}   batchId     群发ID
 *
 * @apiSuccessExample [json-app]
 * {
 *     "id": 1,                // ID
 *     "creator": "王朔",       // 创建者
 *     "createAt": "2021-01-15", // 创建时间
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
 *      ],                             // 群发消息
 *     "sendTime": "2021-03-15 18:12:12",         // 发送时间
 *     "filterParams": {
 *          "gender": 1,                                       性别（0.未知,1.男,2.女）
 *          "rooms":[5,8,9],                                客户群ID（传数组，如[5,8,9]）
 *          "addTimeStart": "2021-01-15 22:21:13",          添加时间起始日期
 *          "addTimeEnd": "2021-03-15 22:21:13",            添加时间截止日期
 *          "tags":[5,8,9],                                 客户标签id（传数组，如[5,8,9]）
 *          "excludeContacts":   [xx,xx,xx],                排除客户ID 传数组，如[xx,xx,xx]）
 *      },
 *     "filterParamsDetail": {
 *          "gender": 0,                        // 性别
 *          "rooms": [
 *              {
 *                  "id":   "1",                // 客户群ID
 *                  "name": "php交流群",        // 客户群名称
 *              }
 *              .....
 *          ],                                  // 客户群
 *          "addTimeStart": "2021-01-15 22:21:13",          // 添加时间起始日期
 *          "addTimeEnd": "2021-03-15 22:21:13",            // 添加时间截止日期
 *          "tags":[
 *              {
 *                  "id":"1",           //标签id
 *                  "name":"优质客户",   //标签名称
 *              }
 *              .....
 *          ],                                 // 客户标签
 *          "excludeContacts":   [
 *              {
 *                  "id":"1",               //成员id
 *                  "name":"产品技术部",    //成员名称
 *              }
 *          ],               // 排除客户
 *      },
 *      "sendEmployeeTotal": 0,         // 发送成员数量
 *      "sendContactTotal": 0,          // 发送客户数量
 *      "sendTotal": 0,       // 已发送成员
 *      "receivedTotal": 0,         // 送达客户
 *      "notSendTotal": 0,           // 未发送成员
 *      "notReceivedTotal": 0,      // 未送达客户
 *      "receiveLimitTotal": 0,     // 客户接收已达上限
 *      "notFriendTotal": 0,        // 因不是好友发送失败
 * }
 *
 */
