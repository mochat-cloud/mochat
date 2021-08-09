<?php

/**
 * @api {post} /common/upload 上传
 * @apiName PostCommonUpload
 * @apiDescription [完成]
 * @apiGroup 公共说明
 *
 * @apiParam {File} file  文件
 * @apiParam {File} [name]  文件名称
 * @apiParam {String} [path='/']  文件目录path
 *
 * @apiSuccess {String} name  文件名称
 * @apiSuccess {String} mimeType  文件mime类型
 * @apiSuccess {String} path  url.path
 * @apiSuccess {String} fullPath  url
 *
 * @apiSuccessExample {json} Success-Response:
 *  {
 *      "code": "200",
 *      "msg": "上传成功",
 *      "data": {
 *          "name": "a",
 *          "mimeType": "image/jpg",
 *          "path": "/path/a.jpg",
 *          "fullPath": "https://path/a.jpg"
 *      }
 *  }
 * @apiUse CommonError
 */
