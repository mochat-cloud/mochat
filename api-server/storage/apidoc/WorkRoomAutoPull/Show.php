<?php

/**
 * @api {get} /workRoomAutoPull/show 详情
 * @apiName workRoomAutoPull.show
 * @apiDescription [已完成]
 * @apiGroup 自动拉群管理
 *
 * @apiParam {Number}  workRoomAutoPullId     自动拉群ID
 *
 * @apiSuccessExample [json-app]
 *  {
 *      "workRoomAutoPullId":1,                  // 自动拉群ID
 *      "qrcodeName":"客户群",                   // 二维码名称
 *      "qrcodeUrl":"http://",                   // 二维码地址
 *      "isVerified":1,,                         // 是否需要验证
 *      "roomNum":"3",                           // 群聊数量
 *      "leadingWords":"你好",                   // 入群引导语
 *      "createdAt":"2018-11-09 16:52:59",       // 创建时间
 *      "employees":[                            // 使用成员
 *          {
 *              "employeeId' : 1,        // 成员通讯录ID
 *              "employeeName' : '',     // 成员通讯录名称
 *           },
 *           ......
 *       ],
 *       "tags":[                          // 标签
 *           {
 *               "groupId' : 1,           // 标签分组ID
 *               "groupName' : '',        // 标签分组名称
 *               "list": [                // 标签列表
 *                   {
 *                       "tagId": 1,     // 客户标签ID
 *                       "tagName": '',  // 标签名称
 *                       "isSelected": 1,// 是否选中[1-选中2-未选中]
 *                    },
 *                    .....
 *                ]
 *            },
 *            ......
 *       ],
 *       "selectedTags": ['1', '2'],            // 选中标签ID数组
 *       "rooms": [                            // 群聊
 *           {
 *               "roomId":1,                     // 客户群ID
 *               "roomName":"北京地区一群",      // 客户群名称
 *               "roomMax":1,                    // 群聊最大成员数量
 *               "maxNum":1,                     // 拉群群成员最大限制数量
 *               "num":1,                        // 群成员数量
 *               "state":"1",                    // 群状态（1.未开始,2.拉人中,3.已拉满）
 *               "roomQrcodeUrl":"/www/xx.jpg",  // 群二维码路径
 *               "longRoomQrcodeUrl":"1",        // 群二维码展示链接
 *            }
 *             ......
 *        ],
 *  }
 */
