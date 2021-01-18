<?php

/**
 * @api {get} /workContact/lossContact 流失客户列表
 * @apiName workContact.lossContact
 * @apiDescription [已完成]
 * @apiGroup 客户
 *
 * @apiParam {String}  [employeeId]   部门成员id（逗号分隔的字符串 如1,2,3）
 * @apiParam {Number}  [page=1]       页码
 * @apiParam {Number}  [perPage=20]   每页显示数
 *
 * @apiSuccessExample [json-app]
 *  {
 *     "page":{
 *           "perPage" : "20",    //每页显示数
 *           "total" : "1",       //总条数
 *           "totalPage" : "1",   //总页数
 *          },
 *     "list":[
 *        {
 *          "id": "1",                      //唯一标识
 *          "contactId": "1",               //客户id
 *          "avatar": "http://imao.net",    //头像链接
 *          "nickName": "啊啊啊",            //昵称
 *          "tag":[                        //标签
 *                    "待开发客户",
 *                    "优质客户",
 *                    ......
 *                ],
 *          "employeeName":[               //归属成员
 *                             "MoChat科技--张子阔",
 *                             ......
 *                         ],
 *          "remark":"张子阔",             //备注(显示删除该客户的操作的姓名)
 *          "deletedAt":"2020-08-11 17:00:09",   //删除时间
 *        },
 *        .....
 *      ]
 *  }
 */
