<?php

/**
 * @api {put} /corp/update 更新提交
 * @apiName corp.update
 * @apiDescription [已完成]
 * @apiGroup 企业微信授权
 *
 * @apiParam {Number} corpId                企业微信授权ID
 * @apiParam {String} corpName              企业名称
 * @apiParam {String} wxCorpId              企业微信ID
 * @apiParam {String} employeeSecret        通讯录管理secret
 * @apiParam {String} contactSecret         外部联系人管理secret
 *
 * @apiUse CommonPost
 */