<?php

/**
 * @api {get} /workDepartment/pageIndex 部门列表分页
 * @apiName workDepartment.pageIndex
 * @apiDescription [完成]
 * @apiGroup 部门管理
 *
 * @apiParam {String}       [name]                  组织名称
 * @apiParam {String}       [parentName]            上级组织名称
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
 *              "departmentPath":  "1-1",                    // 序号
 *              "departmentId"  :  1,                        // 组织id
 *              "name"          :  "公安部门",                // 组织架构名称
 *              "level"         :  "一级部门",                // 部门级别
 *              "parentId"      :  "0",                      // 父级id
 *              "children"      :  [                         // 子级数组
 *                  {
 *                      "departmentPath":  "1-1",                    // 序号
 *                      "departmentId"  :  1,                        // 组织id
 *                      "name"          :  1,                        // 组织架构名称
 *                      "level"         :  "二级部门",                // 部门级别
 *                      "parentId"      :  "1",                      // 父级id
 *                      "children"      : []                         // 子级数组
 *                  }
 *                  ......
 *              ]
 *
 *          }
 *          ......
 *      ]
 * }
 */
