<?php

/**
 * @api {post} /menu/store 添加菜单
 * @apiName menu.store
 * @apiDescription [已完成]
 * @apiGroup 菜单管理
 *
 * @apiParam {Number} level             所填菜单级别 1-一级 2-二级 3-三级 4-四级 5-四级的操作
 * @apiParam {Number} firstMenuId       一级菜单id
 * @apiParam {Number} secondMenuId      二级菜单id
 * @apiParam {Number} thirdMenuId       三级菜单id
 * @apiParam {Number} fourthMenuId      四级菜单id
 * @apiParam {String} name              菜单名称
 * @apiParam {String} icon              图标
 * @apiParam {Number} isPageMenu        是否为页面菜单 1-是 2-否
 * @apiParam {String} linkUrl           地址
 * @apiParam {String} linkType          地址类型
 * @apiParam {Number} dataPermission    数据权限 1-启用, 2-不启用（查看企业下数据）
 *
 * @apiSuccessExample [json-app]
 * {
 * }
 *
 * @apiUse CommonPost
 */