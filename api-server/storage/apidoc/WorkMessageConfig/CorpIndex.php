<?php

/**
 * @api {get} /workMessageConfig/corpIndex 列表
 * @apiName workMessageConfig.corpIndex
 * @apiDescription [已完成]
 * @apiGroup 会话内容存档配置
 *
 * @apiParam {String} [corpName]               企业名称
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
 *              "corpId":"1",                                   // 企业微信授权ID
 *              "corpName":"华泰汽车",                           // 企业名称
 *              "wxCorpId":"SX000005",                          // 企业微信ID
 *              "createdAt":"2018-11-09 16:52:59",              // 创建时间
 *              "chatApplyStatus":0,                            // 会话存档申请进度
 *              "chatStatus":1,                                 // 聊天记录存档状态
 *              "messageCreatedAt":"2018-11-09 16:52:59",       // 聊天记录申请时间
 *          }
 *          ......
 *      ]
 * }
 */
