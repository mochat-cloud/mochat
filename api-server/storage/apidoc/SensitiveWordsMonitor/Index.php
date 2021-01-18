<?php

/**
 * @api {get} /sensitiveWordsMonitor/index 列表
 * @apiName SensitiveWordsMonitor.index
 * @apiDescription [已完成]
 * @apiGroup 敏感词监控
 *
 * @apiParam {Number} [employeeId]             成员通讯录ID(多个用英文半角逗号连接)
 * @apiParam {Number} [workRoomId]             客户群ID
 * @apiParam {Number} [intelligentGroupId]     分组ID
 * @apiParam {String} [triggerStart]           触发时间-开始
 * @apiParam {String} [triggerEnd]             触发时间-结束
 * @apiParam {Number} [page=1]                 页码
 * @apiParam {Number} [perPage=10]             每页条数
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
 *              "sensitiveWordMonitorId":"1",                     // 敏感词监控ID
 *              "sensitiveWordName":"滚蛋",                        // 敏感词
 *              "source":"1",                                     // 触发来源枚举(1-客户2-员工)
 *              "sourceText":"客户",                               // 触发来源文本
 *              "triggerName":"客户",                              // 触发人
 *              "triggerScenario":"客户",                          // 触发场景
 *              "triggerTime":"2018-11-09 16:52:59",              // 触发时间
 *          }
 *          ......
 *      ]
 * }
 */

