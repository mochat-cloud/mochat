<?php

/**
 * @api {post} /role/store 角色添加
 * @apiName role.store
 * @apiDescription [已完成]
 * @apiGroup 角色管理
 *
 * @apiParam {Number} roleId            角色id     复制权限添加角色时 【传角色id】否则 不传
 * @apiParam {String} name              角色名称
 * @apiParam {String} remarks           角色描述
 * @apiParam {Number} dataPermission    部门数据权限 1-是（查看部门数据） 2-否（查看个人数据）
 *
 * @apiSuccessExample [json-app]
 * {
 * }
 *
 * @apiUse CommonPost
 */
