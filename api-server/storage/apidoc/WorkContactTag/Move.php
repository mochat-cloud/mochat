<?php

/**
 * @api {put} /workContactTag/move  移动标签
 * @apiName workContactTag.move
 * @apiDescription [已完成]
 * @apiGroup 客户标签管理
 *
 * @apiParam {String}  tagId     标签id（逗号隔开的字符串 如1,2,3）
 * @apiParam {Number}  groupId   分组id（若移动到未分租 传0）
 *
 * @apiSuccessExample [json-app]
 * {
 * }
 *
 * @apiUse CommonPost
 */
