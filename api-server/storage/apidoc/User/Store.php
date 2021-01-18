<?php

/**
 * @api {post} /user/store 创建提交
 * @apiName user.store
 * @apiDescription [已完成]
 * @apiGroup 子账户管理
 *
 * @apiParam {String} userName           企业成员名称
 * @apiParam {String} phone              手机号(登录账号)
 * @apiParam {Number=1,2} gender         性别(1-男2-女)
 * @apiParam {Number} [roleId]           角色
 * @apiParam {Number=0,1,2} status       状态(0-未启用1-正常2-禁用)
 *
 * @apiUse CommonPost
 */
