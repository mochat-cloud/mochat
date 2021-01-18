<?php

/**
 * @api {get} /channelCode/statisticsIndex 统计分页数据
 * @apiName channelCode.statisticsIndex
 * @apiDescription [已完成]
 * @apiGroup 渠道码
 *
 * @apiParam {Number} channelCodeId        渠道码ID
 * @apiParam {Number=1,2,3} type           统计类型(1-日期2-周3-月)
 * @apiParam {String} startTime            开始时间[非必填,type=1必填]
 * @apiParam {String} endTime              结束时间[非必填,type=1必填]
 * @apiParam {Number} [page=1]             页码
 * @apiParam {Number} [perPage=10]         每页条数
 *
 * @apiSuccessExample [json-app]
 * {
 *     "page":{
 *         "perPage" : "10",    //每页显示数
 *         "total" : "1",       //总条数
 *         "totalPage" : "3",   //总页数
 *       },
 *     "list":[
 *          {
 *              "time":"11",                 // 时间坐标
 *              "addNumRange": 2,            // 新增客户数
 *              "defriendNumRange": 2,       // 被客户删除/拉黑的人数
 *              "deleteNumRange": 2,         // 删除人数
 *              "netNumRange":2,             // 净增人数
 *          }
 *          ......
 *      ]
 * }
 */
