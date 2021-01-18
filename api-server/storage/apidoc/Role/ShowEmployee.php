<?php

/**
 * @api {get} /role/showEmployee 角色查看人员
 * @apiName role.showEmployee
 * @apiDescription [已完成]
 * @apiGroup 角色管理
 *
 * @apiParam {Number}       [roleId]        角色ID
 * @apiParam {Number}       [page=1]        页码
 * @apiParam {Number}       [perPage=10]    每页条数
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
 *              "employeeId"    : 11,               //人员id
 *              "employeeName"  : "张小刚",          //人员姓名
 *              "phone"         : "188888888888",   //手机号码
 *              "email"         : "122@qqq.com",    //邮箱地址
 *              "department"    : "技术部",          //部门名称
 *          }
 *          ......
 *      ]
 * }
 * @apiUse CommonError
 * @apiUse MediumContentResponse
 */
