<?php

/**
 * @api {get} /workMessage/index 列表
 * @apiName workMessage.index
 * @apiDescription [已完成] (分页参数见公共部分)
 * @apiGroup 会话内容存档
 *
 * @apiParam {Number} workEmployeeId 员工ID
 * @apiParam {Number} [type=0]  类型 0所有 1文本、2图片、3图文、4音频、5视频、6小程序、7文件 100其它
 * @apiParam {Number} toUserType  类型 0内部员工 1外部客户 2群
 * @apiParam {Number} toUserId  聊天对象的id(员工ID/客户ID/群ID)
 * @apiParam {Number} [content]  聊天内容
 * @apiParam {Number} [dateTimeStart]  聊天内容开始时间
 * @apiParam {Number} [dateTimeEnd]  聊天内容结束时间
 * @apiParamExample {json} Success-Request:
 *    {
 *          "workEmployeeId": 22,
 *          "type": 1,
 *          "toUserType": 0,
 *          "toUserId": 44
 *     }
 *
 * @apiSuccess {Number} action  消息动作 0.send(发送消息) 1.recall(撤回消息) 2.switch(切换企业日志)
 * @apiSuccess {String} name    发送人名称
 * @apiSuccess {String} avatar  发送人头像
 * @apiSuccess {Number} isCurrentUser  是否为聊天当时人 0否 1是
 * @apiSuccess {Number} type    类型 1文本、2图片、3图文、4音频、5视频、6小程序、7文件 100其它
 * @apiSuccess {String[]} content  消息内容，详情见下面例子
 * @apiSuccess {String} msgDataTime  消息时间
 *
 * @apiSuccessExample {json} Success-Response:
 *    [{
 *          "action": 1,
 *          "name": "昵称",
 *          "avatar": "http://path/a.jpg",
 *          "isCurrentUser": 1,
 *          "type": 1,
 *          "content": {},
 *          "msgDataTime": "2018-09-18 20:20:30"
 *     }]
 * @apiUse CommonError
 * @apiSuccessExample {json} [content.文字]:
 *    {
 *          "content": "你好啊"
 *     }
 * @apiSuccessExample {json} content.图片:
 *  {
 *      "ossFullPath": "https://path/a.jpg"
 *  }
 *
 * @apiSuccessExample {json} content.图文:
 *  {
 *      "info": "图文消息的内容",
 *      "item": [
 *          {
 *              "title": "图文消息标题",
 *              "description": "图文消息描述",
 *              "url": "图文消息点击跳转地址",
 *              "picurl": "图文消息配图的url"
 *          }
 *      ],
 *  }
 *
 * @apiSuccessExample {json} content.音频:
 *  {
 *      "ossFullPath": "https://path/a.mp3",
 *      "playLength": "播放长度-int"
 *  }
 *
 * @apiSuccessExample {json} content.视频:
 *  {
 *      "ossFullPath": "https://path/a.mp4",
 *      "playLength": "播放长度-int"
 *  }
 *
 * @apiSuccessExample {json} content.小程序:
 *  {
 *      "title": "标题",
 *      "description": "消息描述",
 *      "username": "用户名称",
 *      "displayname": "小程序名称"
 *  }
 *
 * @apiSuccessExample {json} content.文件:
 *  {
 *      "ossFullPath": "https://path/a.xls"
 *  }
 *
 * @apiSuccessExample {json} content.其它待定:
 *  {
 *      "content": "https://path/a.xls",
 *      "ossFullPath": "https://path/a.xls",
 *      "item": [
 *          {
 *              "content": "https://path/a.xls",
 *              "ossFullPath": "https://path/a.xls"
 *          }
 *      ]
 *  }
 */
