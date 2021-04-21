<?php

/**
 * @api {post} /contactBatchAdd/importStore 导入客户
 * @apiName contactBatchAdd.importStore
 * @apiDescription [已完成]
 * @apiGroup 批量添加客户
 * 
 * @apiParam {Number[]} [tags] 标签ID
 * @apiParam {String} title 标题.
 * @apiParam {Number[]} allotEmployee 分配员工ID
 * @apiParam {File} file 文件
 * 
 * @apiSuccessExample [json-app]
 *      {
 *          "code": 200,
 *          "msg": "",
 *          "data": {
 *              "successNum": 3     // 导入成功数量
 *           }
 *      }
 */
