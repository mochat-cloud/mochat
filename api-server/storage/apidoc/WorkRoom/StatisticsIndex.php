<?php

/**
 * @api {get} /workRoom/statisticsIndex 统计分页数据
 * @apiName workRoom.statisticsIndex
 * @apiDescription [已完成]
 * @apiGroup 客户群管理
 *
 * @apiParam {Number} workRoomId           客户群ID
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
 *              "time":"11",                             // 时间坐标
 *              "addNum":"10",                           // 新增成员数量
 *              "outNum":"11",                           // 退群成员数量
 *              "total": 2,                              // 当前群成员数
 *              "outTotal":2,                            // 累计退群成员数
 *          }
 *          ......
 *      ]
 * }
 */
