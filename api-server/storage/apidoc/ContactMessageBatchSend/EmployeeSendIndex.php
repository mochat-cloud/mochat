<?php

/**
 * @api {get} /contactMessageBatchSend/employeeSendIndex 消息详情-成员详情
 * @apiName contactMessageBatchSend.employeeSendIndex
 * @apiDescription [已完成]
 * @apiGroup 客户群发管理
 *
 * @apiParam {Number}   batchId             群发ID
 * @apiParam {Number}  [sendStatus]         发送状态（0.未发送成员,1.已发送成员,2.发送失败）
 * @apiParam {String}  [keyWords]           关键词（成员昵称）
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
 *              "id": 1,                            // ID
 *              "employeeId": 1,                    // 成员Id
 *              "employeeName": "王朔",              // 成员名称
 *              "employeeAlias": "王硕",             // 成员别名
 *              "employeeAvatar": "xxx",             // 成员头像
 *              "employeeThumbAvatar": "xxx",       // 成员头像缩略图
 *              "sendContactTotal": 12,             // 发送客户数量
 *              "status": 1,                        // 发送状态（0.未发送,1.已发送,2.发送失败）
 *              "sendTime": "2021-03-15 18:29:11", // 发送时间
 *          }，
 *         .....
 *      ]
 * }
 *
 */
