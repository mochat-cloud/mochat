<?php

/**
 * @api {get} /menu/index 菜单列表
 * @apiName menu.index
 * @apiDescription [已完成]
 * @apiGroup 菜单管理
 *
 * @apiParam {String}       [name]                  菜单名称
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
 *              "menuPath"          :  "1-1",                   // 序号
 *              "menuId"            :  1,                       // 菜单id
 *              "name"              :  "系统设置",               // 菜单名称
 *              "level"             :  "1",                     // 菜单级别
 *              "icon"              :  "公安部受到粉丝的",        // 菜单图标
 *              "status"            :  "启用",                  // 状态  1-启用 2-禁用
 *              "operateName"       :  "张云",                  // 最后操作人
 *              "updatedAt"         :  "2020-09-11 10:59:16",  // 最后操作时间
 *              "children"          :  [                           // 子级数组
 *                  {
 *                      "menuPath"          :  "1-1",                   // 序号
 *                      "menuId"            :  1,                       // 菜单id
 *                      "name"              :  "系统设置",               // 菜单名称
 *                      "level"             :  "1",                     // 菜单级别
 *                      "icon"              :  "公安部受到粉丝的",        // 菜单图标
 *                      "status"            :  "启用",                  // 状态  1-启用 2-禁用
 *                      "operateName"       :  "张云",                  // 最后操作人
 *                      "updatedAt"         :  "2020-09-11 10:59:16",  // 最后操作时间
 *                      "children"      : []                           // 子级数组
 *                  }
 *                  ......
 *              ]
 *          }
 *          ......
 *      ]
 * }
 */
