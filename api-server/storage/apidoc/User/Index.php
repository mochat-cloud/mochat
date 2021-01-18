<?php

/**
 * @api {get} /user/index 列表
 * @apiName user.index
 * @apiDescription [已完成]
 * @apiGroup 子账户管理
 *
 * @apiParam {String}       [phone]                  账户手机号
 * @apiParam {Number=0,1,2} [status]                 账户状态(0-未启用1-正常2-禁用)
 * @apiParam {Number}       [page=1]                 页码
 * @apiParam {Number}       [perPage=10]             每页条数
 *
 * @apiSuccessExample [json-app]
 * {
 *     "notEnabledNum" : 10,          // 未启用数量
 *     "normalNum" : 10,              // 正常数量
 *     "disableNum" : 10,             // 禁用数量
 *     "page":{
 *         "perPage" : "10",    //每页显示数
 *         "total" : "1",       //总条数
 *         "totalPage" : "3",   //总页数
 *       },
 *     "list":[
 *          {
 *              "userId":1,                           // 账户ID
 *              "userName":"华泰汽车",                 // 企业成员名称
 *              "department":                         // 所属部门
 *              [
 *                  {
 *                      'departmentId':1,
 *                      'departmentName':"技术部",
 *                  }
 *              ],
 *              "roleName":"部长",                     // 角色
 *              "phone":"13962274931",                // 手机号[登录账号]
 *              "status":1,                           // 状态
 *              "statusText":"启用",                   // 状态文本
 *              "createdAt":"2018-11-09 16:52:59",    // 创建时间
 *          }
 *          ......
 *      ]
 * }
 */
