<?php

/**
 * @api {get} /workDepartment/showEmployee 查看人员
 * @apiName workDepartment.showEmployee
 * @apiDescription [完成]
 * @apiGroup 部门管理
 *
 * @apiParam {Number}       [departmentId]  组织ID
 * @apiParam {Number}       [page=1]        页码
 * @apiParam {Number}       [perPage=10]    每页条数
 *
 *
 *
 *
 * @apiSuccessExample {json} Success-Response:
 * {
 *  "page":{
 *          "perPage"   : "10",      //每页显示数
 *          "total"     : "1",       //总条数
 *          "totalPage" : "3",       //总页数
 *       },
 *
 *  "list":[
 *          {
 *              "employeeId"    : "11",             //人员id
 *              "employeeName"  : "张小刚",          //人员姓名
 *              "phone"         : "188888888888",   //手机号码
 *              "roleName"      : "技术部主管",      //角色名称
 *          }
 *          ......
 *      ]
 * }
 */
