<?php

/**
 * @api {get} /workEmployeeDepartment/memberIndex 根据部门查成员
 * @apiName workEmployeeDepartment.memberIndex
 * @apiDescription [已完成]
 * @apiGroup 部门管理
 *
 * @apiParam {String}  departmentIds    部门id（逗号隔开的字符串 如1,2,3）
 *
 * @apiSuccessExample [json-app]
 * [
 *     {
 *          "departmentId":"1",             //部门id
 *          "departmentName":"产品技术部",    //部门名称
 *          "employeeId":"2",               //成员id
 *          "employeeName":"张三",           //成员名称
 *     },
 *     ......
 * ]
 */