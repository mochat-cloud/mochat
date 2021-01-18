<?php

/**
 * @api {get} /workContact/detail 客户详情
 * @apiName workContact.detail
 * @apiDescription [已完成]
 * @apiGroup 客户
 *
 * @apiParam {string}  wxExternalUserid  客户微信userid
 *
 * @apiSuccess {Number} id    id
 * @apiSuccess {String} name    名称
 * @apiSuccess {String} avatar    头像
 * @apiSuccess {Number} corpId    企业ID
 *
 * @apiSuccessExample [json-app]
 *  {
 *      "id": 11,
 *      "name": "aaa",
 *      "avatar": "http://....",
 *      "corpId": 1
 *  }
 */
