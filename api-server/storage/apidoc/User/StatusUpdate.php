<?php

/**
 * @api {put} /user/statusUpdate 禁用|启动
 * @apiName user.statusUpdate
 * @apiDescription [已完成]
 * @apiGroup 子账户管理
 *
 * @apiParam {String}     userId             子账户ID(多个用英文半角逗号连接)
 * @apiParam {Number=1,2} status             账户状态(1-启用2-禁用)
 *
 * @apiUse CommonPost
 */
