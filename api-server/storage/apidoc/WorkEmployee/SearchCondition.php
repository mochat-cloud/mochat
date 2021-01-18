<?php

/**
 * @api {get} /workEmployee/searchCondition 成员列表搜索条件数据
 * @apiName workEmployee.searchCondition
 * @apiDescription [已完成]
 * @apiGroup 成员
 *
 * @apiSuccess {string[]} status       状态
 * @apiSuccess {String} syncTime       最新同步时间
 * @apiSuccess {string[]} contactAuth  外部联系人
 *
 * @apiSuccessExample {json} Success-Response:
 *  {
 *     "status":[
 *        {
 *          "id": "1",
 *          "name": "已激活"
 *        }
 *      ]
 *   "contactAuth":[
 *        {
 *          "id": "1",
 *          "name": "是"
 *        }
 *      ]
 *      "syncTime": "2020-09-19"
 *  }
 *
 * @apiSuccessExample {json} [status状态说明]:
 *    {
 *          "id": "编号"，
 *          "name": "成员状态名称"
 *     }
 * @apiUse CommonError
 */
