<?php

/**
 * @api {get} /workMessage/fromUsers 会话员工下拉
 * @apiName workMessage.fromUsers
 * @apiDescription [完成]
 * @apiGroup 会话内容存档
 *
 * @apiParam {String} [name]  搜索名称
 * @apiParamExample {json} Success-Request:
 *    {
 *          "name": "小明"
 *     }
 *
 * @apiSuccess {Number} id      员工ID
 * @apiSuccess {String} name    员工名称
 * @apiSuccess {String} avatar  员工头像
 *
 * @apiSuccessExample {json} Success-Response:
 *    [{
 *          "id": 22,
 *          "name": "名称",
 *          "avatar": "http://a.jpg",
 *     }]
 * @apiUse CommonError
 */