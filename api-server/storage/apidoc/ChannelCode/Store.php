<?php

/**
 * @api {post} /channelCode/store 新建渠道码
 * @apiName channelCode.store
 * @apiDescription [已完成]
 * @apiGroup 渠道码
 *
 * @apiParamExample {Json}  baseInfo  基础设置
 *                                 {
 *                                    "groupId":"1",         分组id（未分组传0）
 *                                    "name":"1",            活码名称
 *                                    "autoAddFriend":"1",   自动添加好友（1-开启（直接通过），2-关闭（需验证））
 *                                    "tags":[5,8,9],        客户标签id（传数组，如[5,8,9]）
 *                                 }
 * @apiParamExample {Json}  drainageEmployee  引流成员设置
 *                                  {
 *                                     "type":"1",    类型（1.单人，2.多人）
 *                                     "employees":[  企业成员
 *                                                    {
 *                                                       "week":"1"    周期（周日传0）
 *                                                       "timeSlot":[  时间段设置
 *                                                                     {
 *                                                                        "employeeId":[1,3],   固定时段成员id
 *                                                                        "departmentId":[1],   固定时段部门id（类型为多人时选部门时传该字段）
 *                                                                        "startTime":"00:00",  开始时间
 *                                                                        "endTime":"00:00",    结束时间
 *                                                                      }
 *                                                                      ......
 *                                                                   ]
 *                                                     }
 *                                                     ......
 *                                                 ],
 *                                     "specialPeriod":{  特殊时期设置
 *                                                        "status":"1",  状态（1.开启，2.关闭）
 *                                                        "detail":[    特殊时期
 *                                                                    {
 *                                                                       "startDate":"2020-10-01",  开始日期
 *                                                                       "endDate":"2020-11-01",    结束日期
 *                                                                       "timeSlot":[               时间段设置
 *                                                                                     {
 *                                                                                        "employeeId":[1,3],   成员id
 *                                                                                        "startTime":"01:00",  开始时间
 *                                                                                        "endTime":"03:00",    结束时间
 *                                                                                     }
 *                                                                                     ......
 *                                                                                  ]
 *                                                                     }
 *                                                                     ......
 *                                                                  ]
 *                                                     },
 *                                      "addMax":{ 员工添加上限
 *                                                  "status":"1",  状态（1.开启，2.关闭）
 *                                                  "employees":[
 *                                                                 {
 *                                                                    "employeeId":"1", 成员id
 *                                                                    "max":"100",      上限
 *                                                                 }
 *                                                                 ......
 *                                                              ],
 *                                                  "spareEmployeeIds": [1,2]  备用员工
 *                                                }
 *                                   }
 * @apiParamExample {Json}  welcomeMessage  欢迎语设置
 *                                {
 *                                   "scanCodePush":"1",  扫码是否推送欢迎语（1.开启，2.关闭）
 *                                   "messageDetail":[
 *                                               {
 *                                                  "type":"1",                欢迎语类型 （1.通用，2.周期，3.特殊时期）
 *                                                  "welcomeContent":"哈哈哈",  欢迎语内容
 *                                                  "mediumId":"1",            素材库ID
 *                                               },
 *                                               {
 *                                                  "type":"2",    欢迎语类型 （1.通用，2.周期，3.特殊时期）
 *                                                  "status":"1",  状态（1.开启，2.关闭）
 *                                                  "detail":[
 *                                                              {
 *                                                                 "chooseCycle": [1,2,3]   选择周期（周日传0）
 *                                                                 "timeSlot":[  时间段
 *                                                                               {
 *                                                                                  "welcomeContent":"哈哈",  欢迎语内容
 *                                                                                  "mediumId":"1",          素材库ID
 *                                                                                  "startTime":"01:00",     开始时间
 *                                                                                  "endTime":"03:00",       结束时间
 *                                                                                }
 *                                                                                ......
 *                                                                             ]
 *                                                               }
 *                                                               ......
 *                                                            ]
 *                                                },
 *                                                {
 *                                                   "type":"3",    欢迎语类型 （1.通用，2.周期，3.特殊时期）
 *                                                   "status":"1",  状态（1.开启，2.关闭）
 *                                                   "detail":[
 *                                                               {
 *                                                                  "startDate":"2020-10-01",  开始日期
 *                                                                  "endDate":"2020-11-01",    结束日期
 *                                                                  "timeSlot":[  //时间段
 *                                                                                {
 *                                                                                   "welcomeContent": "哈哈",   欢迎语内容
 *                                                                                   "mediumId":"1",            素材库ID
 *                                                                                   "startTime":"01:00",       开始时间
 *                                                                                   "endTime":"03:00",         结束时间
 *                                                                                }
 *                                                                                ......
 *                                                                             ]
 *                                                                }
 *                                                                ......
 *                                                             ]
 *                                                 }
 *                                             ]
 *                                }
 *
 * @apiSuccessExample [json-app]
 * {
 * }
 *
 * @apiUse CommonPost
 */
