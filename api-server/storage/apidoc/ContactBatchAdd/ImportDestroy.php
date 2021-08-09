<?php

/**
 * @api {delete} /contactBatchAdd/importDestroy 删除客户导入
 * @apiName contactBatchAdd.importDestroy
 * @apiDescription [已完成]
 * @apiGroup 批量添加客户
 * 
 * @apiParam {Number} id 导入客户批次ID
 * 
 * @apiSuccessExample [json-app]
 *      {
 *          "code": 200,
 *          "msg": "",
 *          "data": {
 *              "delRecordNum": 1,  // 删除成功批次数量
 *              "delContactNum": 2  // 删除成功客户数量
 *           }
 *      }
 */
