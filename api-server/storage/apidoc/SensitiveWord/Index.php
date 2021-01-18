<?php
/**
 * @api {get} /sensitiveWord/index 敏感词列表
 * @apiName sensitiveWord.index
 * @apiDescription [已完成]
 * @apiGroup 敏感词库管理
 *
 * @apiParam {String}       [keyWords]             关键字
 * @apiParam {Number}       [groupId]              分组id
 * @apiParam {Number}       [page=1]               页码
 * @apiParam {Number}       [perPage=10]           每页条数
 *
 * @apiSuccessExample [json-app]
 * {
 *
 *     "page":{
 *         "perPage"   : "10",      //每页显示数
 *         "total"     : "1",       //总条数
 *         "totalPage" : "3",       //总页数
 *       },
 *     "list":[
 *          {
 *              "sensitiveWordId"   :  1,                       // 敏感词id
 *              "name"              :  "公安部受到粉丝的",        // 敏感词名称
 *              "employeeNum"       :  "技术主管",               // 员工触发次数
 *              "contactNum"        :  "10",                    // 客户触发次数
 *              "createdAt"         :  "2018-08-25 14:54",      // 创建时间
 *              "status"            :  "1",                     // 状态 1-开启,2-关闭
 *          }
 *          ......
 *      ]
 * }
 */
