<?php

/**
 * @api {put} /workRoom/roomIndex 群聊列表下拉框
 * @apiName workRoom.roomIndex
 * @apiDescription [已完成]
 * @apiGroup 客户
 *
 * @apiParam {String}  [name]            群聊名称
 * @apiParam {Number}  [roomGroupId]     群聊分组id
 *
 * @apiSuccessExample [json-app]
 * {
 *     "total":"10",         //总群聊数
 *     "list": [
 *                {
 *                   "roomId":"11"        //群id
 *                   "roomName":"群聊一"   //群名称
 *                   "currentNum":"3"    //当前群人数
 *                   "roomMax":"200"     //群上限
 *                },
 *                ......
 *             ]
 * }
 */
