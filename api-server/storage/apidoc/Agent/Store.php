<?php
/**
 * @api {post} /agent/store 应用添加
 * @apiName agent.store
 * @apiDescription 王培艳
 * @apiGroup 企业应用
 *
 * @apiParam   {String} wxAgentId  微信应用ID
 * @apiSuccess {String} wxSecret 微信应用secret
 * @apiSuccess {Number} type 企业应用类型 1-侧边栏 2-会话消息 3-工作台
 *
 * @apiSuccessExample {json} Success-Response:
 *    {
 *    }
 *
 * @apiUse CommonError
 */
