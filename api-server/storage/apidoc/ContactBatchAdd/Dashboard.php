<?php

/**
 * @api {get} /contactBatchAdd/dashboard 数据统计
 * @apiName contactBatchAdd.dashboard
 * @apiDescription [已完成]
 * @apiGroup 批量添加客户
 * 
 * @apiSuccessExample [json-app]
 *      {
 *          "code": 200,
 *          "msg": "",
 *          "data": {
 *              "employees": {
 *                  "current_page": 1,
 *                  "data": [
 *                      {
 *                          "id": 11,               // 员工ID
 *                          "name": "员工1",         // 员工名
 *                          "allotNum": 1,          // 分配客户数
 *                          "toAddNum": 2,          // 待添加客户数
 *                          "pendingNum": 3,        // 待通过客户数
 *                          "passedNum": 4,         // 已通过客户数
 *                          "recycleNum": 5,        // 回收客户数（按次）
 *                          "completion": 23.42     // 添加完成率数
 *                      }
 *                  ],
 *                  "first_page_url": "http://127.0.0.1:9510/contactBatchAdd/dashboard?page=1",
 *                  "from": 1,
 *                  "last_page": 2,
 *                  "last_page_url": "http://127.0.0.1:9510/contactBatchAdd/dashboard?page=2",
 *                  "next_page_url": "http://127.0.0.1:9510/contactBatchAdd/dashboard?page=2",
 *                  "path": "http://127.0.0.1:9510/contactBatchAdd/dashboard",
 *                  "per_page": 15,
 *                  "prev_page_url": null,
 *                  "to": 15,
 *                  "total": 20
 *              },
 *              "dashboard": {          // 仪表盘统计数据
 *                  "contactNum": 3,    // 导入客户数量
 *                  "pendingNum": 1,    // 待分配客户数
 *                  "toAddNum": 1,      // 待添加客户数
 *                  "passedNum": 0,     // 已添加客户数
 *                  "completion": 0     // 完成率
 *              }
 *          }
 *      }
 */
