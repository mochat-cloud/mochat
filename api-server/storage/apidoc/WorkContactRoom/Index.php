<?php

/**
 * @api {get} /workContactRoom/index 客户群成员管理-列表
 * @apiName workContactRoom.index
 * @apiDescription [已完成]
 * @apiGroup 客户群成员管理
 *
 * @apiParam {Number} workRoomId               客户群ID
 * @apiParam {Number=1,2} [status]             成员状态(1-正常2-退群)
 * @apiParam {String} [name]                   成员名称
 * @apiParam {String} [startTime]              开始时间
 * @apiParam {String} [endTime]                结束时间
 * @apiParam {Number} [page=1]                 页码
 * @apiParam {Number} [perPage=10]             每页条数
 *
 * @apiSuccessExample [json-app]
 * {
 *     "inRoomNum" : 15,        // 当前群成员数量
 *     "outRoomNum" : 15,       // 累计退群成员数量
 *     "page":{
 *         "perPage" : "10",    //每页显示数
 *         "total" : "1",       //总条数
 *         "totalPage" : "3",   //总页数
 *       },
 *      "list":[
 *          {
 *              "workContactRoomId":"1",                   // 客户群成员ID
 *              "name": "我是谁",                          // 成员名称
 *              "avatar": "我是谁",                        // 成员头像地址
 *              "isOwner":1,                               // 是否是群主(1-是2-否)
 *              "joinTime":"2018-11-09 16:52:59",          // 入群时间
 *              "otherRooms": [ '开发群', '产品群'],       // 所在其它群
 *              "outRoomTime":"2018-11-09 16:52:59",       // 退群时间
 *              "joinScene":1,                             // 入群方式枚举
 *              "joinSceneText":"其它",                    // 入群方式文本
 *          }
 *          ......
 *      ]
 * }
 *
 */
