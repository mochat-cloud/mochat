<?php

/**
 * @api {post} /role/show 角色详情
 * @apiName role.show
 * @apiDescription [已完成]
 * @apiGroup 角色管理
 *
 * @apiParam {Number} roleId            角色id
 *
 * @apiSuccessExample [json-app]
 * {
 *      "roleId"                :   "1",                       // 角色id
 *      "name"                  :   "角色名称",                 // 角色名称
 *      "remarks"               :   "角色描述秒",               // 角色描述
 *      "dataPermission"        :   "1",                       // 部门数据权限 1-是（查看部门数据） 2-否（查看个人数据）
 * }
 *
 */
