<?php

/**
 * @api {get} /workEmployee/index 成员列表
 * @apiName workEmployee.index
 * @apiDescription [已完成]
 * @apiGroup 成员
 *
 * @apiParam {Number}  corpId             企业id
 * @apiParam {Number}  status             成员状态（可选）（1=已激活，2=已禁用，4=未激活，5=退出企业）
 * @apiParam {String}  name               成员名称（可选）
 * @apiParam {String}  contactAuth        外部联系人权限（可选）
 *
 * @apiParamExample {json} Success-Request:
 *    {
 *          "corpId": 1,
 *          "status": 1,
 *          "name": "赵四"
 *     }
 *
 * @apiSuccess {string} employeeId        成员id
 * @apiSuccess {String} thumbAvatar       头像
 * @apiSuccess {String} name              名称
 * @apiSuccess {String} gender            性别（0表示未定义，1表示男性，2表示女性）
 * @apiSuccess {String} contactAuthName   外部联系人权限
 * @apiSuccess {string} applyNums         发起申请数
 * @apiSuccess {string} statusName        状态
 * @apiSuccess {Number} addNums           新增客户数
 * @apiSuccess {string} messageNums       聊天数
 * @apiSuccess {String} sendMessageNums   发送消息数
 * @apiSuccess {Number} replyMessageRatio 已回复聊天占比
 * @apiSuccess {string} averageReply      平均首次回复时长
 * @apiSuccess {String} invalidContact    删除/拉黑客户数
 *
 *
 * @apiSuccessExample {json} Success-Response:
 *  {
 *     "page":{
 *           "page"   : "1",
 *           "perPage" : "20",
 *           "total" : "1",
 *           "totalPage" : "1"
 *          },
 *     "list":[
 *        {
 *          "employeeId": "1",
 *          "thumbAvatar":"http://"
 *          "name":"名称",
 *          "statusName": "已激活"
 *          "gender": "1",
 *          "applyNums": "5",
 *          "addNums": "10",
 *          "messageNums": "12",
 *          "sendMessageNums": "13",
 *          "replyMessageRatio":"23%",
 *          "averageReply":"237.8分钟",
 *          "invalidContact":"10"
 *        }
 *      ]
 *  }
 *
 * @apiUse CommonError
 */
