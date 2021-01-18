<?php

/**
 * @api {get} /role/permissionShow 角色权限查看
 * @apiName role.permissionShow
 * @apiDescription [完成]
 * @apiGroup  角色管理
 *
 * @apiParam {Number}       [roleId]            角色ID
 *
 *
 *
 *
 * @apiSuccessExample {json} Success-Response:
 *
 *      [
 *          {
 *              "id"            :  1,                       // 菜单id
 *              "name"              :  "系统设置",               // 菜单名称
 *              "level"             :  "1",                     // 菜单级别 1-一级, 2-二级,3-三级,4-四级,5-五级
 *              "checked"           :  "1"                      // 是否选中 1-未选中, 2-选中, 3-半选
 *              "isPageMenu": "是否为前端菜单",
 *              "children"          :  [                        // 子级数据
 *                  {
 *                      "id"            :  1,                       // 菜单id
 *                      "name"              :  "系统设置",               // 菜单名称
 *                      "isPageMenu": "是否为前端菜单",
 *                      "level"             :  "1",                     // 菜单级别 1-一级, 2-二级,3-三级,4-四级,5-五级
 *                      "checked"           :  "1"                      // 是否选中 1-未选中, 2-选中, 3-半选
 *                      "children"          : []                        // 子级数组
 *                  }
 *                  ......
 *              ]
 *
 *          }
 *      ]
 *
 * @apiUse CommonError
 */
