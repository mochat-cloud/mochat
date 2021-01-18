<?php

## ==================== 公共配置定义 ==================== ##

/**
 * @apiDefine PostSuccessful
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       "msg": "成功",
 *       "data": []
 *     }
 */

/**
 * @apiDefine CommonError
 * @apiError code.[403].[100100] 该用户无此权限，请联系管理员
 * @apiError code.[401].[100001] token失效
 * @apiError code.[401].[100002] 用户或密码错误
 * @apiError code.[401].[100003] 非法token
 * @apiError code.[401].[100004] token过期
 * @apiError code.[401].[100005] 未认证,没有token
 * @apiError code.[401].[100006] 认证失败
 * @apiError code.[403].[100007] 没有权限
 * @apiError code.[403].[100008] 拒绝客户端请求
 * @apiError code.[403].[100009] 禁止重复操作
 * @apiError code.[400].[100010] 客户端错误
 * @apiError code.[401].[100011] 非法的Content-Type头
 * @apiError code.[404].[100012] 资源未找到
 * @apiError code.[422].[100013] 非法的参数
 * @apiError code.[500].[100014] 服务器异常
 * @apiError code.[500].[100015] 服务器异常(third-party-api)
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "100014",
 *       "msg": "服务异常",
 *       "data": []
 *     }
 */

/**
 * @apiDefine CommonPost
 * @apiSuccessExample {json} Success-Response:
 *     {
 *       "code": "200",
 *       "msg": "成功",
 *       "data": []
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     {
 *       "code": "100014",
 *       "msg": "服务异常",
 *       "data": []
 *     }
 */


/**
 * @apiDefine CommonRequestHeader
 * @apiHeader {String} Authorization 用户token
 * @apiHeader {String} Accept 请求格式
 * @apiHeaderExample {json} Request-Example:
 *     {
 *          "Authorization": "Bearer header.payload.signature",
 *          "Accept": "application/json"
 *     }
 */



## ==================== 公共说明 ==================== ##

/**
 * @api {METHOD} / 请求参数
 * @apiName RequestCommon
 * @apiDescription 【除GET请求之外】所有的请求参数，应为json的格式数据
 * @apiGroup 公共说明
 *
 * @apiUse CommonRequestHeader
 */

/**
 * @api {METHOD} / 响应参数
 * @apiName ResponseCommon
 * @apiDescription 之后所有的接口参数，将只针对 data 进行具体说明
 * @apiGroup 公共说明
 *
 * @apiSuccess {Number}   code  错误码
 * @apiSuccess {String}   msg   具体描述
 * @apiSuccess {String[]} data  数据
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *          "code": 200,
 *          "msg": "成功",
 *          "data": []
 *     }
 *
 * @apiUse CommonError
 */

/**
 * @api {GET} / 分页参数
 * @apiName ResponsePage
 * @apiDescription 之后所有的列表接口参数，将只针对 list 进行具体说明
 * @apiGroup 公共说明
 *
 * @apiParam {Number}   page     页码
 * @apiParam {Number}   perPage  每页显示条数
 *
 * @apiSuccess {Object}   page  页码
 * @apiSuccess {Object}   page.perPage  每页条数
 * @apiSuccess {Number}   page.total   总条数
 * @apiSuccess {Number}   page.totalPage  总页数
 * @apiSuccess {String[]}   list  分页数据
 *
 * @apiSuccessExample {json} Success-Response:
 *     {
 *          "page": {
 *              "perPage": 10,
 *              "total": 100,
 *              "totalPage": 10
 *          },
 *          "list": []
 *     }
 *
 * @apiUse CommonError
 */
