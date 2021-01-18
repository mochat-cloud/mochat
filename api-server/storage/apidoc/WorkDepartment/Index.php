<?php

/**
 * @api {get} /workDepartment/index 部门、成员列表
 * @apiName workDepartment.index
 * @apiDescription [已完成]
 * @apiGroup 部门管理
 *
 * @apiParam {String}  [searchKeyWords]   搜索内容（部门或成员）
 *
 * @apiSuccessExample [json-app]
 * {
 *     "department":[  //所有部门信息
 *                       {
 *                           "id":"1",             //部门id
 *                           "name":"产品技术部",    //部门名称
 *                           "nameEn":"PTC",       //部门英文名称
 *                           "parentId":"0",       //部门父级id
 *                           "son":[    //子部门信息
 *                                    {
 *                                        "id":"5",              //部门id
 *                                        "name":"技术部",        //部门名称
 *                                        "nameEn":"technology", //部门英文名称
 *                                        "parentId":"1",        //部门父级id
 *                                        "order":"1",           //在父部门中的次序值,order值大的排序靠前
 *                                     }
 *                                     ......
 *                                  ]
 *                        },
 *                        ......
 *                   ],
 *      "employee":[   //所有成员信息
 *                       {
 *                           "employeeId":"1",      //成员id
 *                           "name":"产品技术部",    //成员名称
 *                       },
 *                       ......
 *                ]
 * }
 */
