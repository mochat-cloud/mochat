<?php

/**
 * @api {get} /greeting/index 列表
 * @apiName greeting.index
 * @apiDescription [已完成]
 * @apiGroup 欢迎语
 *
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
 *      "hadGeneral" : 1,       // 是否存在通用欢迎语(0-不存在1-存在)
 *      "hadEmployees" :        // 已存在特定欢迎语的成员
 *      [
 *          2,3,4
 *      ],
 *      "list":[
 *          {
 *              "greetingId":"1",                               // 欢迎语ID
 *              "typeText":"文本",                               // 欢迎语内容类型文本
 *              "rangeType":1,                                  // 适用成员类型枚举(1-全部成员2-部分成员)
 *              "rangeTypeText":"全部成员",                      // 适用成员类型文本
 *              "employees":                                    // 适用成员
 *               [
 *                   '张三',‘李四’
 *               ]
 *              "words": "欢迎",                                // 文本内容
 *              "mediumId":1,                                   // 素材ID
 *              "mediumContent":{},                             // 素材内容
 *              "createdAt":"2018-11-09 16:52:59",              // 创建时间
 *          }
 *          ......
 *      ]
 * }
 * @apiUse MediumContentResponse
 */
