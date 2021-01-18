<?php

/**
 * @api {post} /workRoomAutoPull/store 创建提交
 * @apiName workRoomAutoPull.store
 * @apiDescription [已完成]
 * @apiGroup 自动拉群管理
 *
 * @apiParam {Number} corpId             企业微信授信ID
 * @apiParam {String} qrcodeName         扫码名称
 * @apiParam {Number=1,2} isVerified     添加验证(1-需验证2-直接通过)
 * @apiParam {String} employees          使用成员ID(多个英文半角逗号连接)
 * @apiParam {String} tags               客户标签ID(多个英文半角逗号连接)
 * @apiParam {String} leadingWords       入群引导语
 * @apiParam {Json} rooms                群聊ID(多个英文半角逗号连接)
 * @apiParamExample {json} rooms
 *                                [
 *                                    {
 *                                         'roomId' : 1,
 *                                         'maxNum' : 100,
 *                                         'roomQrcodeUrl' : 'http://www.weixin.com/1.jpg',
 *                                     },
 *                                    {
 *                                         'roomId' : 2,
 *                                         'maxNum' : 150,
 *                                         'roomQrcodeUrl' : 'http://www.weixin.com/12jpg',
 *                                     },
 *                                ]
 * @apiUse CommonPost
 */
