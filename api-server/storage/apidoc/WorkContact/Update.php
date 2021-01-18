<?php

/**
 * @api {put} /workContact/update 修改客户详情基本信息
 * @apiName workContact.update
 * @apiDescription [已完成]
 * @apiGroup 客户
 *
 * @apiParam {Number}     contactId       客户id
 * @apiParam {Number}     employeeId      员工id
 * @apiParam {String}     [remark]        备注名称
 * @apiParam {String[]}   [tag]           标签id（数组）
 * @apiParam {String}     [description]   描述
 * @apiParam {String}     [businessNo]    客户编号
 *
 * @apiSuccessExample [json-app]
 *  {
 *  }
 *
 * @apiUse CommonPost
 */
