<?php

/**
 * @api {get} /contactBatchAdd/importIndex 导入记录
 * @apiName contactBatchAdd.importIndex
 * @apiDescription [已完成]
 * @apiGroup 批量添加客户
 * 
 * @apiSuccessExample [json-app]
 *      {
 *          "code": 200,
 *          "msg": "",
 *          "data": {
 *              "current_page": 1,
 *              "data": [
 *                  {
 *                      "id": 30,               // 导入ID
 *                      "corpId": 1,            // 企业ID
 *                      "title": "任务标题",    // 标题
 *                      "uploadAt": "2021-04-02 17:41:50",  // 上传时间
 *                      "allotEmployee": [  // 分配员工数组
 *                          {
 *                              "id": 11,           // 员工ID
 *                              "name": "员工一"    // 员工名
 *                          },
 *                          {
 *                              "id": 2,
 *                              "name": "员工二"
 *                          }
 *                      ],
 *                      "tags": [   // 标签数组
 *                          {
 *                              "id": 2,        // 标签ID
 *                              "name": "标签名"    // 标签名
 *                          }
 *                      ],
 *                      "importNum": 3, // 导入数量
 *                      "addNum": 0,    // 成功添加数量
 *                      "fileName": "10af4acb8ed2e9fd923570008b06b702.xlsx",    // 导入文件名
 *                      "fileUrl": "http://xxx.com/xx.xlsx" // 导入文件URL
 *                  }
 *              ],
 *              "first_page_url": "http://127.0.0.1:9510/contactBatchAdd/importIndex?page=1",
 *              "from": 1,
 *              "last_page": 2,
 *              "last_page_url": "http://127.0.0.1:9510/contactBatchAdd/importIndex?page=2",
 *              "next_page_url": "http://127.0.0.1:9510/contactBatchAdd/importIndex?page=2",
 *              "path": "http://127.0.0.1:9510/contactBatchAdd/importIndex",
 *              "per_page": 15,
 *              "prev_page_url": null,
 *              "to": 15,
 *              "total": 27
 *          }
 *      }
 */
