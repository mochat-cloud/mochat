<?php

/**
 * @api {put} /workRoomAutoPull/update 更新提交.
 * @apiName workRoomAutoPull.update
 * @apiDescription [已完成]
 * @apiGroup 自动拉群管理
 *
 * @apiParam {Number} workRoomAutoPullId   自动拉群ID[必填]
 * @apiParam {Number=1,2} isVerified       添加验证(1-需验证2-直接通过)
 * @apiParam {String} employees            使用成员ID(多个英文半角逗号连接)
 * @apiParam {String} tags                 客户标签ID(多个英文半角逗号连接)
 * @apiParam {String} rooms                群聊ID(多个英文半角逗号连接)
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
