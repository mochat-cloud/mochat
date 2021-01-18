<?php

/**
 * @api {get} /channelCode/index 渠道码列表
 * @apiName channelCode.index
 * @apiDescription [已完成]
 * @apiGroup 渠道码
 *
 * @apiParam {String}        [name]        活码名称
 * @apiParam {Number=0,1,2}  [type]        活码类型（0-全部 1-单人 2-多人）
 * @apiParam {Number}        [groupId]     分组id
 * @apiParam {Number}        [page=1]      页码
 * @apiParam {Number}        [perPage=20]  每页显示数
 *
 * @apiSuccessExample [json-app]
 *  {
 *     "page":{
 *               "perPage" : "20",    //每页显示数
 *               "total" : "1",       //总条数
 *               "totalPage" : "1",   //总页数
 *            },
 *     "list":[
 *        {
 *          "channelCodeId": "1",         //渠道码id
 *          "qrcodeUrl":"http://",        //二维码地址
 *          "name": "1",                  //名称
 *          "type": "单人",                //活码类型
 *          "groupName": "啊啊啊",         //分组名称
 *          "contactNum": "3",            //客户数
 *          "tag":[                       //标签
 *                    "待开发客户",
 *                    "优质客户",
 *                    ......
 *                ],
 *          "autoAddFriend": "自动通过",    //自动添加好友
 *        },
 *        .....
 *      ]
 *  }
 */
