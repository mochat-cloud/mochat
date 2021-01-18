<?php

/**
 * @api {get} /workRoom/statistics 统计折线图.
 * @apiName workRoom.statistics
 * @apiDescription [已完成]
 * @apiGroup 客户群管理
 *
 * @apiParam {Number} workRoomId           客户群ID
 * @apiParam {Number=1,2,3} type           统计类型(1-日期2-周3-月)
 * @apiParam {String} startTime            开始时间[非必填,type=1必填]
 * @apiParam {String} endTime              结束时间[非必填,type=1必填]
 *
 * @apiSuccessExample [json-app]
 * {
 *     "addNum": 2,       // 今日新增成员数
 *     "outNum": 2,       // 今日退群成员数
 *     "total": 2,        // 当前群成员数
 *     "outTotal":2,      // 累计退群成员数
 *
 *     "addNumRange": 2,       // 当前时间段新增成员数
 *     "outNumRange": 2,       // 当前时间段退群成员数
 *
 *     "list":[
 *          {
 *              "time":"11",                             // 时间坐标
 *              "addNum":"10",                           // 新增成员数量
 *              "outNum":"11",                           // 退群成员数量
 *          }
 *          ......
 *      ]
 * }
 */
