<?php

/**
 * @api {get} /role/index 角色列表
 * @apiName role.index
 * @apiDescription [已完成]
 * @apiGroup 角色管理
 *
 * @apiParam {String}       [name]                  角色名称
 * @apiParam {Number}       [page=1]                页码
 * @apiParam {Number}       [perPage=10]            每页条数
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
 *              "roleId"        :  1,                        // 角色id
 *              "name"          :  "技术主管",                // 角色名称
 *              "employeeNum"   :  "10",                     // 角色人员数量
 *              "remarks"       :  "公安部受到粉丝的",         // 角色描述
 *              "updatedAt"     :  "2018-08-25 14:54",       // 更新时间
 *              "status"        :  "1",                      // 启用状态 1-启用 -2禁用
 *          }
 *          ......
 *      ]
 * }
 */
