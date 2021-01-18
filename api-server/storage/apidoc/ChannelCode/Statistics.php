<?php

/**
 * @api {get} /channelCode/statistics 统计折线图.
 * @apiName channelCode.statistics
 * @apiDescription [已完成]
 * @apiGroup 渠道码
 *
 * @apiParam {Number} channelCodeId        渠道码ID
 * @apiParam {Number=1,2,3} type           统计类型(1-日期2-周3-月)
 * @apiParam {String} startTime            开始时间[非必填,type=1必填]
 * @apiParam {String} endTime              结束时间[非必填,type=1必填]
 *
 * @apiSuccessExample [json-app]
 * {
 *     "addNum": 2,            // 今日新增客户数
 *     "defriendNum": 2,       // 今日被客户删除/拉黑的人数
 *     "deleteNum": 2,         // 今日删除人数
 *     "netNum":2,             // 今日净增人数
 *
 *     "addNumLong": 2,            // 时间段-新增客户数
 *     "defriendNumLong": 2,       // 时间段-被客户删除/拉黑的人数
 *     "deleteNumLong": 2,         // 时间段-删除人数
 *     "netNumLong":2,             // 时间段-净增人数
 *
 *     "list":[
 *          {
 *             "time":"11",                 // 时间坐标
 *             "addNumRange": 2,            // 新增客户数
 *             "defriendNumRange": 2,       // 被客户删除/拉黑的人数
 *             "deleteNumRange": 2,         // 删除人数
 *             "netNumRange":2,             // 净增人数
 *          }
 *          ......
 *      ]
 * }
 */
