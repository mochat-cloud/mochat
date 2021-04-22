<?php

/**
 * @api {post} /user/auth 登陆
 * @apiName user.auth
 * @apiDescription [完成]
 * @apiGroup 公共说明
 *
 * @apiParam {String} phone  手机号
 * @apiParam {String} password  密码
 *
 * @apiParamExample {json} Success-Request:
 *  {
 *      "phone": "17601023260",
 *      "password": "1234567"
 *  }
 *
 * @apiSuccess {String} token   用户token
 * @apiSuccess {String} expire  token失效时间(分钟)
 *
 * @apiSuccessExample {json} Success-Response:
 *  {
 *      "code": "200",
 *      "msg": "登陆成功",
 *      "data": {
 *          "token": "asdfasdfasdfasdfasdf",
 *          "expire": "3600"
 *      }
 *  }
 * @apiErrorExample {json} Error-Response:
 *  {
 *      "code": "100001",
 *      "msg": "token失效",
 *      "data": []
 *  }
 */
