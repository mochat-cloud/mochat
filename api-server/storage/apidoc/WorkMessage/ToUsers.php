<?php

/**
 * @api {get} /workMessage/toUsers 会话对象列表
 * @apiName GetWorkMessageToUsers
 * @apiDescription [完成]
 * @apiGroup 会话内容存档
 *
 * @apiParam {Number} workEmployeeId 员工ID
 * @apiParam {Number} [toUsertype=0]  类型 0内部员工 1外部客户 2群
 * @apiParam {String} [name]  搜索名称/备注
 * @apiParamExample {json} Success-Request:
 *    {
 *          "workEmployeeId": 22,
 *          "toUsertype": 1,
 *          "name": "小明"
 *     }
 *
 * @apiSuccess {Number} workEmployeeId  员工ID
 * @apiSuccess {Number} toUsertype  类型 0内部员工 1外部客户 2群
 * @apiSuccess {Number} toUserId  聊天对象的id(员工ID/客户ID/群ID)
 * @apiSuccess {String} name    对方名称
 * @apiSuccess {String} avatar  对方头像
 * @apiSuccess {String} content  消息
 * @apiSuccess {String} msgDataTime  最近一条消息时间
 *
 * @apiSuccessExample {json} Success-Response:
 *    [{
 *          "workEmployeeId": 22,
 *          "toUsertype": 1,
 *          "toUserId": 1,
 *          "name": "名称",
 *          "alias": "别名",
 *          "avatar": "昵称",
 *          "content": "....",
 *          "msgDataTime": "2018-09-18 20:20:30"
 *     }]
 * @apiUse CommonError
 */
