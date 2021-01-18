<?php

/**
 * @api {get} /workContact/show 查看客户详情
 * @apiName workContact.show
 * @apiDescription [已完成]
 * @apiGroup 客户
 *
 * @apiParam {Number}  contactId     客户id
 * @apiParam {Number}  employeeId    员工id
 *
 * @apiSuccessExample [json-app]
 *  {
 *      "name": "啊啊啊",          //名称
 *      "gender": "1",            //性别（0-未知 1-男性 2-女性）
 *      "genderText": "男",       //性别
 *      "remark":"哈哈哈",         //备注名称
 *      "tag":[                  //标签
 *               {
 *                  "tagId":"1",   //标签id
 *                  "tagName":"优质客户",   //标签名称
 *               }
 *               ......
 *            ],
 *      "description":"暂无",       //描述
 *      "businessNo": "1233333",   //客户编号
 *      "roomName": [              //所在群
 *                      "北京地区一群",
 *                      "北京地区二群",
 *                      ......
 *                  ],
 *      "lastContact":"暂无",       //上次联系（本期暂不做）
 *      "contactTimes":"0",        //联系次数（本期暂不做）
 *      "employeeName":[          //归属企业成员
 *                         "MoChat科技--张子阔",
 *                         ......
 *                     ]
 *  }
 */
