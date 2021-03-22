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
 *              "sendTime": "2021-03-15 22:21:13", // 发送时间
 *              "textContent": "去", // 群发内容
 *              "sendTotal": 0, // 已发送成员
 *              "receivedTotal": 0, // 送达客户
 *              "notSendTotal": 0, // 未发送成员
 *              "notReceivedTotal": 0, // 未送达客户
 *          },
 *          .....
 *      ]
 */
