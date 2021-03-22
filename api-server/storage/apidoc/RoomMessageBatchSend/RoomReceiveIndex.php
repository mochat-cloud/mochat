<?php

/**
 * @api {get} /roomMessageBatchSend/roomReceiveIndex 消息详情-客户群接收详情
 * @apiName roomMessageBatchSend.roomReceiveIndex
 * @apiDescription [已完成]
 * @apiGroup 客户群群发管理
 *
 * @apiParam {Number}   batchId             群发ID
 * @apiParam {Number}   [sendStatus]        发送状态（1.群主已送达,2.群主未发送）
 * @apiParam {String}   [keyWords]          关键词（搜素群聊）
 * @apiParam {String}   [employeeIds]       群主ID多个用英文逗号隔开
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
 *      "list": [                            // 群聊
 *          {
 *              "roomId":1,                     // 客户群ID
 *              "roomName":"北京地区一群",        // 客户群名称
 *              "roomMax":1,                    // 群聊最大成员数量
 *              "maxNum":1,                     // 拉群群成员最大限制数量
 *              "num":1,                        // 群成员数量
 *              "state":"1",                    // 群状态（1.未开始,2.拉人中,3.已拉满）
 *              "roomQrcodeUrl":"/www/xx.jpg",  // 群二维码路径
 *              "longRoomQrcodeUrl":"1",        // 群二维码展示链接
 *          }
 *          ......
 *      ],
 * }
 *
 */
