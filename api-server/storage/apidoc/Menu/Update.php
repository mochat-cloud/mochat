<?php

/**
 * @api {put} /menu/update 修改菜单
 * @apiName menu.update
 * @apiDescription [已完成]
 * @apiGroup 菜单管理
 *
 * @apiParam {Number} menuId            当前菜单id
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
