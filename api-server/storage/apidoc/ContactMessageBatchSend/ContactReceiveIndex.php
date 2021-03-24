<?php

/**
 * @api {get} /contactMessageBatchSend/contactReceiveIndex 消息详情-客户详情
 * @apiName contactMessageBatchSend.contactReceiveIndex
 * @apiDescription [已完成]
 * @apiGroup 客户群发管理
 *
 * @apiParam {Number}   batchId             群发ID
 * @apiParam {Number}  [sendStatus]         发送状态（1.已送达,2.未送达客户,3.客户接收达上限,4.已不是好友客户）
 * @apiParam {String}  [keyWords]           关键词（客户昵称）
 * @apiParam {Number}   page                页码
 * @apiParam {Number}   perPage             每页显示条数
 *
 * @apiSuccessExample [json-app]
 * {
 *      "page":{
 *          "perPage" : "10",    //每页显示数
 *          "total" : "1",       //总条数
 *          "totalPage" : "3",   //总页数
 *      },
 *      "list": [
 *          {
 *              "id": 1,                             // ID
 *              "contactId": 1,                      // 客户Id
 *              "contactName": "王朔",               // 姓名
 *              "contactNickName": "",              // 客户昵称
 *              "contactAvatar": "xxx",             // 客户头像
 *              "status": 1,                         // 发送状态（发送状态 0-未发送 1-已发送 2-因客户不是好友导致发送失败 3-因客户已经收到其他群发消息导致发送失败）
 *              "sendTime": "2021-03-15 18:29:11",   // 发送时间
 *              "employeeId": 1,                     // 成员ID
 *              "employeeName": "王硕"                // 成员昵称
 *              "employeeAlias": "别名"               // 成员别名
 *          }，
 *         .....
 *      ]
 * }
 *
 */
