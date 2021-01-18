<?php

/**
 * @api {get} /sensitiveWordsMonitor/show 触发敏感词对话详情.
 * @apiName SensitiveWordsMonitor.show
 * @apiDescription [已完成]
 * @apiGroup 敏感词监控
 *
 * @apiParam {Number} sensitiveWordsMonitorId              敏感词监控ID
 *
 * @apiSuccess {String} sender         发送人名称
 * @apiSuccess {Number} msgType        类型 1文本、2图片、3图文、4音频、5视频、6小程序、7文件 100其它
 * @apiSuccess {String} sendTime       消息时间
 * @apiSuccess {Number} isTrigger      是否触发敏感词
 * @apiSuccess {String[]} msgContent   消息内容，详情见下面例子
 *
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
