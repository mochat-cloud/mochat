<?php

/**
 * @api {get} /channelCode/contact  渠道码客户
 * @apiName channelCode.contact
 * @apiDescription  [已完成]
 * @apiGroup 渠道码
 *
 * @apiParam {Number}  channelCodeId   渠道码id
 * @apiParam {Number}  [page=1]        页码
 * @apiParam {Number}  [perPage=15]    每页显示数
 *
 * @apiSuccessExample [json-app]
 *  {
 *     "page":{
 *               "perPage" : "20",    //每页显示数
 *               "total" : "1",       //总条数
 *               "totalPage" : "1",   //总页数
 *            },
 *     "list":[
 *               {
 *                  "contactId":"1"               //客户id
 *                  "name":"张子阔"                //客户名称
 *                  "employees": [                //归属成员
 *                                  "张子阔",
 *                                  "王慧"
 *                               ],
 *                  "createTime":"2020-11-20 16:49:50"   //添加时间
 *               }
 *               .....
 *            ]
 *  }
 */
