<?php

/**
 * @api {get} /workContactTag/index  列表
 * @apiName workContactTag.index
 * @apiDescription  [已完成]
 * @apiGroup 客户标签管理
 *
 * @apiParam {Number}  [page=1       页码
 * @apiParam {Number}  [perPage=20]  每页显示数
 * @apiParam {Number}  [groupId]     分组id
 *
 * @apiSuccessExample [json-app]
 *  {
 *     "page":{
 *               "perPage" : "20",    //每页显示数
 *               "total" : "1",       //总条数
 *               "totalPage" : "1",   //总页数
 *            },
 *     "syncTagTime":"2020-09-23 15:42:43",    //最后一次同步标签时间
 *     "list":[
 *               {
 *                  "id":"11"      //标签id
 *                  "name":"优质"   //标签名称
 *                  "contactNum":"3"  //客户数
 *               },
 *               ......
 *            ]
 * }
 */
