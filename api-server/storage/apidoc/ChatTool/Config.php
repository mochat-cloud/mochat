<?php

/**
 * @api {get} /chatTool/config 配置详情
 * @apiName GetChatToolConfig
 * @apiDescription
 * @apiGroup 侧边栏-JSSDK
 *
 * @apiSuccess {String[]} agents 应用
 * @apiSuccess {Number} agents.id 应用ID
 * @apiSuccess {String} agents.name 应用名称
 * @apiSuccess {String} agents.squareLogoUrl 企业应用方形头像
 *
 * @apiSuccess {String[]} agents.chatTools 应用侧边栏
 * @apiSuccess {String} agents.chatTools.pageName 应用侧边栏-页面名称
 * @apiSuccess {String} agents.chatTools.pageUrl 应用侧边栏-页面URL
 *
 * @apiSuccess {String[]} whiteDomains 可信域名
 *
 * @apiSuccessExample {json} Success-Response:
 *    {
 *        "agents": [
 *          {
 *              "id": 1,
 *              "name": "应用名称",
 *              "squareLogoUrl": "企业应用方形头像",
 *              chatTools: [
 *                  {
 *                      "pageName": "页面名称",
 *                      "pageUrl": "页面URL"
 *                  }
 *              ],
 *          },
 *        ],
 *        "whiteDomains": ["http://xx.com", "http://yy.com"]
 *    }
 *
 * @apiUse CommonError
 */
