<?php

/**
 * @api {get} /corp/show 详情
 * @apiName corp.show
 * @apiDescription [已完成]
 * @apiGroup 企业微信授权
 *
 * @apiParam {Number} corpId        企业微信授权ID
 *
 * @apiSuccessExample [json-app]
 * {
 *      "corpId":"1",                              // 企业微信授权ID
 *      "corpName":"华泰汽车",                     // 企业名称
 *      "wxCorpId":" 14",                          // 企业微信ID
 *      "employeeSecret":"91110000101174712L",     // 通讯录管理secret
 *      "contactSecret":"1",                       // 外部联系人管理secret
 *      "event_callback":"http://www.xxx.com",    // 通讯录事件服务器URL
 *      "token":"200000",                          // Token
 *      "encodingAesKey":"2000000",                // EncodingAESKey
 * }
 */