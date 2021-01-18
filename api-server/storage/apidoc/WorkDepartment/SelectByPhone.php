<?php

/**
 * @api {get} /workDepartment/selectByPhone 根据手机号匹配成员部门下拉列表.
 * @apiName workDepartment.selectByPhone
 * @apiDescription [已完成]
 * @apiGroup 部门管理
 *
 * @apiParam {String} phone                手机号
 * @apiParam {Number} [type]               获取数据类型(1-全部2-当前企业,默认:1)
 *
 * @apiSuccessExample [json-app]
 * {
 *      [
 *          {
 *              "corpId":"1",                             // 企业微信授权ID
 *              "workDepartmentId":"1",                   // 成员部门ID
 *              "workDepartmentName":"技术部",             // 成员部门名称
 *          }
 *          ......
 *      ]
 * }
 */
