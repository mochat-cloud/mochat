<?php

/**
 * @api {post} /workContact/batchLabeling 批量打标签
 * @apiName workContact.batchLabeling
 * @apiDescription [已完成]
 * @apiGroup 客户
 *
 * @apiParam {String}  contactId  客户id（以逗号隔开的字符串 如1,2,3）
 * @apiParam {String}  tagId      标签id（以逗号隔开的字符串 如1,2,3）
 *
 * @apiSuccessExample [json-app]
 * {
 * }
 *
 * @apiUse CommonPost
 */
