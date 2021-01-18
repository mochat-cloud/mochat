<?php

/**
 * @api {get} /workRoomGroup/index 列表
 * @apiName workRoomGroup.index
 * @apiDescription [已完成]
 * @apiGroup 客户群分组管理
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
 *      "list":[
 *          {
 *              "workRoomGroupId":"1",                   // 客户群分组ID
 *              "corpId": 10,                            // 企业微信授权ID
 *              "workRoomGroupName":"潜在组",            // 客户群分组名称
 *              "createdAt":"2018-11-09 16:52:59",       // 创建时间
 *          }
 *          ......
 *      ]
 * }
 */
