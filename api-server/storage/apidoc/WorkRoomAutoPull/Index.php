<?php

/**
 * @api {get} /workRoomAutoPull/index 列表
 * @apiName workRoomAutoPull.index
 * @apiDescription [已完成]
 * @apiGroup 自动拉群管理
 *
 * @apiParam {String} qrcodeName      群活码名称[非必填]
 * @apiParam {Number} page            页码[非必填]
 * @apiParam {Number} perPage         每页条数[非必填]
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
 *              "workRoomAutoPullId":"1",                // 自动拉群ID
 *              "qrcodeUrl":"http://",                   // 二维码地址
 *              "qrcodeName":"客户群",                   // 群名称
 *              "contactNum":"1",                        // 客户数
 *              "createdAt":"2018-11-09 16:52:59",       // 创建时间
 *              "employees":[                            // 使用成员
 *                   "李三",
 *                   "李三",
 *              ],
 *              "tags":[                                 // 标签
 *                   "新标签",
 *                   "新标签",
 *               ],
 *              "rooms": [                                // 群聊
 *                  {
 *                      "roomName":"北京地区一群",   // 群名称
 *                      "stateText":"1",             // 群状态文本
 *                   }
 *                   ......
 *               ],
 *
 *            }
 *            ......
 *      ]
 * }
 */
