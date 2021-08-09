<?php

/**
 * @api {post} /contactBatchAdd/settingUpdate 修改设置
 * @apiName contactBatchAdd.settingUpdate
 * @apiDescription [已完成]
 * @apiGroup 批量添加客户
 * 
 * @apiParam {Number} pendingStatus 待处理客户提醒开关0关1开
 * @apiParam {Number} pendingTimeOut 待处理客户提醒超时天数
 * @apiParam {Time} pendingReminderTime 待处理客户提醒时间 示例（13:01:01）
 * @apiParam {Number} [pendingLeaderId] 待处理客户提醒管理员ID
 * @apiParam {Number} undoneStatus 成员未添加客户提醒开关0关1开
 * @apiParam {Number} undoneTimeOut 成员未添加客户提醒超时天数
 * @apiParam {Time} undoneReminderTime 成员未添加客户提醒时间 示例（13:01:01）
 * @apiParam {Number} recycleStatus 回收客户开关0关1开
 * @apiParam {Number} recycleTimeOut 客户超过天数回收
 * 
 * @apiSuccessExample [json-app]
 *      {
 *          "code": 200,
 *          "msg": "",
 *          "data": {
 *              "status": 1 // 保存成功1失败0
 *          }
 *      }
 */
