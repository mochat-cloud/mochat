<?php

/**
 * @api {get} /workContact/index 客户列表
 * @apiName workContact.index
 * @apiDescription [已完成]
 * @apiGroup 客户
 *
 * @apiParam {String}  [keyWords]        关键词（客户姓名、昵称）
 * @apiParam {String}  [remark]          备注
 * @apiParam {String}  [fieldId]         用户画像属性id
 * @apiParam {Number}  [fieldType]       属性类型（有用户画像属性id时必填）
 * @apiParam {String}  [fieldValue]      用户画像筛选值值（有用户画像属性id时必填）
 * @apiParam {Number}  [gender]          客户性别（0-未知 1-男性 2-女性 3-全部）
 * @apiParam {Number}  [addWay]          客户来源
 * @apiParam {String}  [roomId]          群聊id（逗号分隔的字符串 如1,2,3）
 * @apiParam {String}  [groupNum]        客户持群数（0-无群 1-一个 2-多个 3-全部）
 * @apiParam {String}  [employeeId]      部门成员id（逗号分隔的字符串 如1,2,3）
 * @apiParam {String}  [startTime]       开始时间（如：2020-09-21 11:05:23）
 * @apiParam {String}  [endTime]         结束时间（如：2020-09-21 11:05:23）
 * @apiParam {String}  [businessNo]      客户编码
 * @apiParam {Number}  [page=1]          页码
 * @apiParam {Number}  [perPage=20]      每页显示数
 *
 * @apiSuccessExample [json-app]
 *  {
 *     "page":{
 *               "perPage" : "20",    //每页显示数
 *               "total" : "1",       //总条数
 *               "totalPage" : "1",   //总页数
 *            },
 *     "syncContactTime":"2020-09-23 15:42:43",    //最后一次同步客户时间
 *     "list":[
 *        {
 *          "id": "1",                      //唯一标识
 *          "contactId": "1",               //客户id
 *          "employeeId": "1",              //所属成员id
 *          "isContact": "1",               //是否是当前登录人的客户（1-是 2-否）
 *          "avatar": "http://imao.net",    //头像链接
 *          "gender": "1",                  //性别（0-未知 1-男性 2-女性）
 *          "genderText": "男",             //性别
 *          "name": "啊啊啊",                //名称
 *          "remark": "啊啊啊",              //备注
 *          "businessNo": "1233333",       //客户编号
 *          "roomName": [                  //所在群
 *                          "北京地区一群",
 *                          "北京地区二群",
 *                          ......
 *                      ],
 *          "addWay": "1",                //来源标识
 *          "addWayText": "名片分享",       //来源
 *          "tag":[                       //标签
 *                    "待开发客户",
 *                    "优质客户",
 *                    ......
 *                ],
 *          "employeeName": "MoChat科技--张子阔",     //归属成员
 *          "createTime":"2020-08-11 17:00:09",   //添加时间
 *        },
 *        .....
 *      ]
 *  }
 */
