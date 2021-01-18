<?php

/**
 * @api {get} /workRoom/index 列表
 * @apiName workRoom.index
 * @apiDescription [已完成]
 * @apiGroup 客户群管理
 *
 * @apiParam {Number} [roomGroupId]                 客户群分组ID(选择-未分组-传0)
 * @apiParam {String} [workRoomName]                客户群名称
 * @apiParam {Number} [workRoomOwnerId]             群主ID(多个用英文半角逗号连接)
 * @apiParam {Number=0,1,2,3} [workRoomStatus]      群状态(0-正常1-跟进人离职2-离职继承中3-离职继承完成)
 * @apiParam {String} [startTime]                   开始时间(例：2020-09-01)
 * @apiParam {String} [endTime]                     结束时间(例：2020-10-01)
 * @apiParam {Number} [page=1]                      页码
 * @apiParam {Number} [perPage=10]                  每页条数
 *
 * @apiSuccessExample [json-app]
 * {
 *     "page":{
 *         "perPage" : "10",    //每页显示数
 *         "total" : "1",       //总条数
 *         "totalPage" : "3",   //总页数
 *       },
 *      "list":[
 *          {
 *              "workRoomId":"1",                        // 客户群ID
 *              "memberNum":"10",                        // 客户群成员数量
 *              "roomName":"客户群",                     // 客户群名称
 *              "ownerName":"张三",                      // 群主姓名
 *              "roomGroup":"潜在客户",                  // 所属分组
 *              "status":"1",                            // 状态枚举
 *              "statusText":"正常",                     // 状态文本
 *              "inRoomNum":1,                           // 今日入群数量
 *              "outRoomNum":20,                         // 今日退群数量
 *              "notice":"欢迎大家！",                   // 群公告
 *              "createTime":"2018-11-09 16:52:59",      // 创建时间
 *          }
 *          ......
 *      ]
 * }
 */