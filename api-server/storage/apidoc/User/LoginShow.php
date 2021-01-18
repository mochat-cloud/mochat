<?php

/**
 * @api {get} /user/loginShow 登录用户信息详情
 * @apiName user.loginShow
 * @apiDescription [已完成]
 * @apiGroup 子账户管理

 *
 * @apiSuccessExample [json-app]
 * {
 *      "userId":"1",                                   // 系统用户属性-子账户ID
 *      "userPhone":"13131988888",                      // 系统用户属性-手机号
 *      "userName":"张三",                               // 系统用户属性-姓名
 *      "userGender": 1,                                // 系统用户属性-性别
 *      "userDepartment":'技术部',                       // 系统用户属性-部门
 *      "userPosition":"员工",                           // 系统用户属性-职称
 *      "userLoginTime":2020-10-10 10:00:00,            // 系统用户属性-登录系统时间
 *      "userStatus": 1,                                // 系统用户属性-用户状态
 *      "employeeId" : 1,                               // 绑定企业员工属性-通讯录ID
 *      "employeeName" : "张三",                        // 绑定企业员工属性-姓名
 *      "employeeMobile" : 13131988888,                 // 绑定企业员工属性-手机号
 *      "employeePosition" : 员工,                       // 绑定企业员工属性-职称
 *      "employeeGender" : 1,                           // 绑定企业员工属性-性别
 *      "employeeEmail" : '123@email',                  // 绑定企业员工属性-邮箱
 *      "employeeAvatar" : 'http://www',                // 绑定企业员工属性-头像URL
 *      "employeeThumbAvatar" : 'http://www',           // 绑定企业员工属性-头像缩略图URL
 *      "employeeTelephone" : 123456,                   // 绑定企业员工属性-座机
 *      "employeeAlias" : '小李',                       // 绑定企业员工属性-别称
 *      "employeeStatus" : 1,                           // 绑定企业员工属性-状态
 *      "employeeQrCode" : 'http://www',                // 绑定企业员工属性-员工二维码
 *      "employeeExternalPosition" : '员工',            // 绑定企业员工属性-员工对外职位
 *      "employeeAddress" : '北京',                     // 绑定企业员工属性-地址
 *      "corpId" : 1,                                   // 绑定企业属性-企业ID
 *      "corpName" : '立讯精密',                         // 绑定企业属性-企业名称
 *      "roleId" : '1',                                // 绑定角色属性-角色ID
 *      "roleName" : '技术员',                          // 绑定角色属性-角色名称
 * }
 */
