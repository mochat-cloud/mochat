<?php

/**
 * @api {get} /contactMessageBatchSend/index 消息列表
 * @apiName contactMessageBatchSend.index
 * @apiDescription [已完成]
 * @apiGroup 客户群发管理
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
 *              "sendWay": 1,   // 群发类型
 *              "sendTime": "2021-03-22 02:13:50",  // 发送时间
 *              "sendTotal": 1, // 已发送数量
 *              "notSendTotal": 0,  // 未发送数量
 *              "receivedTotal": 2, // 送达客户
 *              "notReceivedTotal": 0,  // 未送达客户
 *              "definiteTime": "2021-03-22 02:02:00",  // 定时发送时间
 *              "sendStatus": 1,        // 发送状态
 *              "createdAt": "2021-03-22 02:13:49"      // 创建时间
 *          },
 *          ......
 *      ]
 */
