<?php

/**
 * @api {get} /user/show 详情
 * @apiName user.show
 * @apiDescription [已完成]
 * @apiGroup 子账户管理
 *
 * @apiParam {Number} userId        子账户ID[必填]
 *
 * @apiSuccessExample [json-app]
 * {
 *      "userId":"1",                              // 子账户ID
 *      "userName":"张三",                         // 企业员工姓名
 *      "phone":" 13988889999",                    // 手机号
 *      "gender":1,                                // 性别
 *      "department":                              // 所属部门
 *      [
 *          {
 *              'departmentId':1,
 *              'departmentName':"技术部",
 *          }
 *      ],
 *      "roleId":1,                                // 角色ID
 *      "roleName":1,                              // 角色名称
 *      "status": 1,                               // 状态
 * }
 */
