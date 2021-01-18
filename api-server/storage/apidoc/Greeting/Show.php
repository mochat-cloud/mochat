<?php

/**
 * @api {get} /greeting/show 详情
 * @apiName greeting.show
 * @apiDescription [已完成]
 * @apiGroup 欢迎语
 *
 * @apiParam {Number} greetingId        欢迎语ID
 *
 * @apiSuccessExample [json-app]
 * {
 *      "greetingId":"1",                          // 欢迎语ID
 *      "rangeType":"1",                           // 适用成员类型
 *      "employees":                               // 适用成员
 *      [
 *          {
 *              "employeeId":"1",
 *              "employeeName":"张三",
 *          }
 *          ...
 *      ],
 *      "words":"欢迎",                             // 欢迎语文本
 *      "mediumId":"1",                            // 素材库ID
 *      "mediumContent":{},                        // 素材库内容
 * }
 * @apiUse MediumContentResponse
 */