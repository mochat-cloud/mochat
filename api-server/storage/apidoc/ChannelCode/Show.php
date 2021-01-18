<?php

/**
 * @api {get} /channelCode/show 渠道码详情
 * @apiName channelCode.show
 * @apiDescription [已完成]
 * @apiGroup 渠道码
 *
 * @apiParam {Number}  channelCodeId  渠道码id
 *
 * @apiSuccessExample [json-app]
 *  {
 *      "baseInfo":{  //基础设置
 *                    "groupId":"1",        分组id（未分组为0）
 *                    "groupName":"测试",    分组名称
 *                    "name":"1",           活码名称
 *                    "autoAddFriend":"1",  自动添加好友（1-开启（直接通过），2-关闭（需验证））
 *                    "tags":[              标签
 *                              {
 *                                 "groupId":1,               标签分组ID
 *                                 "groupName": '',           标签分组名称
 *                                 "list":[                   标签列表
 *                                           {
 *                                              "tagId": 1,       客户标签ID
 *                                              "tagName": '',    标签名称
 *                                              "isSelected": 1,  是否选中[1-选中2-未选中]
 *                                            },
 *                                            .....
 *                                         ]
 *                               },
 *                              ......
 *                           ],
 *                     "selectedTags":['1', '2'],          选中标签ID数组
 *                 },
 *      "drainageEmployee":{  //引流成员设置
 *                            "type":"1",   类型（1.单人，2.多人）
 *                            "employees":[  企业成员
 *                                           {
 *                                              "week":"1"         周期（0为周日）
 *                                              "weekText":"周一"   周期文本
 *                                              "timeSlot":[  时间段设置
 *                                                     {
 *                                                        "employeeId":[1],         固定时段成员id
 *                                                        "selectMembers":["王慧"],  固定时段成员名称
 *                                                        "departmentId":[1],      固定时段部门id
 *                                                        "departmentName":[技术],  固定时段部门名称
 *                                                        "startTime":"00:00",  开始时间
 *                                                        "endTime":"00:00",    结束时间
 *                                                      }
 *                                                      ......
 *                                                   ]
 *                                            }
 *                                            ......
 *                                         ],
 *                             "specialPeriod":{  特殊时期设置
 *                                                "status":"1",  状态（1.开启，2.关闭）
 *                                                "detail":[     特殊时期
 *                                                            {
 *                                                               "startDate":"2020-10-01",  开始日期
 *                                                               "endDate":"2020-11-01",    结束日期
 *                                                               "timeSlot":[               时间段设置
 *                                                                      {
 *                                                                         "employeeId":[1],         成员id
 *                                                                         "selectMembers":["王慧"],  成员名称
 *                                                                         "startTime":"01:00",     开始时间
 *                                                                         "endTime":"03:00",       结束时间
 *                                                                       }
 *                                                                       ......
 *                                                                    ]
 *                                                             }
 *                                                             ......
 *                                                         ]
 *                                             },
 *                             "addMax":{  员工添加上限
 *                                         "status":"1",  状态（1.开启，2.关闭）
 *                                         "employees":[
 *                                                        {
 *                                                           "employeeId":"1",      成员id
 *                                                           "employeeName":"王慧",  成员名称
 *                                                           "max":"100",           上限
 *                                                        }
 *                                                        ......
 *                                                     ],
 *                                         "spareEmployeeIds": [1,2]       备用员工id
 *                                         "spareEmployeeName": [王慧,子阔]  备用员工名称
 *                                      }
 *                       },
 *       "welcomeMessage":{  //欢迎语设置
 *                           "scanCodePush":"1",  扫码是否推送欢迎语（1.开启，2.关闭）
 *                           "messageDetail":[
 *                                              {
 *                                                 "type":"1",                欢迎语类型 （1.通用，2.周期，3.特殊时期）
 *                                                 "welcomeContent":"哈哈哈",  欢迎语内容
 *                                                 "mediumId":"1",            素材库ID
 *                                                 "content":[],              素材库内容
 *                                              },
 *                                              {
 *                                                 "type":"2",    欢迎语类型 （1.通用，2.周期，3.特殊时期）
 *                                                 "status":"1",  状态（1.开启，2.关闭）
 *                                                 "detail":[
 *                                                             {
 *                                                                "chooseCycle": [1,2,3]     周期（0为周日）
 *                                                                "timeSlot":[  时间段
 *                                                                        {
 *                                                                           "welcomeContent":"哈哈",  欢迎语内容
 *                                                                           "mediumId":"1",       素材库ID
 *                                                                           "content":[],         素材库内容
 *                                                                           "startTime":"01:00",  开始时间
 *                                                                           "endTime":"03:00",    结束时间
 *                                                                         }
 *                                                                         ......
 *                                                                      ]
 *                                                              }
 *                                                              ......
 *                                                           ]
 *                                                },
 *                                                {
 *                                                   "type":"3",    欢迎语类型 （1.通用，2.周期，3.特殊时期）
 *                                                   "status":"1",  状态（1.开启，2.关闭）
 *                                                   "detail":[
 *                                                               {
 *                                                                  "startDate":"2020-10-01",  开始日期
 *                                                                  "endDate":"2020-11-01",    结束日期
 *                                                                  "timeSlot":[  //时间段
 *                                                                          {
 *                                                                             "welcomeContent": "哈哈",  欢迎语内容
 *                                                                             "mediumId":"1",      素材库ID
 *                                                                             "content":[],        素材库内容
 *                                                                             "startTime":"01:00", 开始时间
 *                                                                             "endTime":"03:00",   结束时间
 *                                                                           }
 *                                                                           ......
 *                                                                        ]
 *                                                                }
 *                                                                ......
 *                                                             ]
 *                                                 }
 *                                             ]
 *                       }
 *  }
 * @apiUse MediumContentResponse
 */
