<p></p>
<p></p>

<p align="center">
  <img alt="logo" src="https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/logo.png">
</p>
<h2 align="center">MoChat —— 让企业微信开发更简单</h2>

<div align="center">
<a href="https://www.php.net"><img src="https://img.shields.io/badge/php-%3E=7.4-brightgreen.svg?maxAge=2592000" alt="Php Version"></a>
  <a href="https://github.com/swoole/swoole-src"><img src="https://img.shields.io/badge/swoole-%3E=4.5-brightgreen.svg?maxAge=2592000" alt="Swoole Version"></a>
  <a href="https://github.com/mochat-cloud/mochat/blob/master/LICENSE"><img src="https://img.shields.io/github/license/mochat-cloud/mochat.svg?maxAge=2592000" alt="MoChat License"></a>

</div>

<p></p>
<p></p>

![输入图片说明](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/header.png)

中文 | [Java](https://github.com/mochat-cloud/mochat-java)

[文档](https://mochat.wiki) | [安装](https://mochat.wiki/quick-start/install-bt.html) | [截图](#部分演示图持续更新) | [架构](#业务架构) | [数据库字典](https://mochat.wiki/database/md/tables.html) | [API文档](https://mochat.wiki/api/) | [快速开始](https://mochat.wiki/wework/how-to-authorize.html)

### 项目简介

> MoChat, easy way to WeWork

MoChat 是开源的企业微信应用开发框架&引擎，是一套通用的企业微信多租户SaaS管理系统，得益于 `Swoole` 和 `Hyperf` 框架的优秀，MoChat 可提供超高性能的同时，也保持着极其灵活的可扩展性。

#### 应用场景

可用于电商、金融、零售、餐饮服装等服务行业的企业微信用户，通过简单的分流、引流转化微信客户为企业客户，结合强大的后台支持，灵活的运营模式，建立企业与客户的强联系，让企业的盈利模式有了多种不同的选择。

#### 功能特性

六大模块助力企业营销能力升级：

* 引流获取：通过多渠道活码获取客户，条理有序分类
* 客户转化：素材库、欢迎语互动客户，加强与客户联系
* 客户管理：精准定位客户，一对一标签编辑，自定义跟踪轨迹，流失客户提醒与反馈
* 客户群管理：于客户的基础，进一步获取客户裂变，自动拉群。集中管理，快速群发
* 聊天侧边栏：提高企业员工沟通效率，精准服务
* 企业风控：客户聊天记录存档，并设立敏感词库、敏感词报警，多方位跟进管理员工服务

#### 业务架构
严格的分层来保证架构的灵活性

![架构](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/framework2.png "mochat微信.png")

#### 核心技术
* 前端技术栈: `Vue`、`Vuex`、`Vant`、`Ant Design of Vue`
* 后端技术栈: `PHP`、`MySQL`、`Redis`、 `Swoole`、`Hyperf`

### 特此鸣谢
MoChat 的诞生离不开社区其他优秀的开源项目，在此特别鸣谢：

[![Swoole](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/Swoole-mini.png)](https://www.swoole.com/)
[![Hyperf](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/Hyperf-mini.png)](https://www.hyperf.io/)
[![EasyWechat](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/EasyWeChat-mini.png)](https://www.easywechat.com)
[![Vue](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/Vue-mini.png)](https://cn.vuejs.org)
[![Vant](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/Vant-mini.png)](https://vant-contrib.gitee.io/vant/#/zh-CN/)
[![Ant](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/Antdv-mini.png)](https://antdv.com)


### 环境部署
#### 准备工作

```
PHP >= 7.4 (推荐7.4版本)
Swoole >= 4.5
Wxwork_finance_sdk(so扩展)
pcntl扩展
Composer
MySQL >= 5.7
Redis
FFmpeg
Node.js >= 10
```

#### 运行系统

##### 后端运行

```bash
# 目录
git clone https://github.com/mochat-cloud/mochat.git

cd /path/to/mochat/api-server

# 安装依赖
composer install

# 运行PHP服务
php bin/hyperf.php start
```

##### 前端运行


```bash
# 进入项目目录
cd /path/to/mochat/dashboard

# 安装依赖
yarn install

# 编译生成dist
yarn run build
```

##### 必要配置

1、后端配置运行脚本

- `php bin/hyperf.php mc:init`，根据提示完成配置

2、前端配置
- 修改 .env 中的配置 VUE_APP_API_BASE_URL= 接口地址

#### 部署系统

##### 后端部署


- Docker：推荐根据`api-server/Dockerfile`使用K8S部署
- Shell： 使用`linux-install.sh`安装必要依赖
```shell script
cd /usr/local/src && wget https://mochatcloud.oss-cn-beijing.aliyuncs.com/deploy/CentOS-install.sh && ./CentOS-install.sh
```
- Nginx 配置：具体参考开发文档

##### 前端部署

当项目开发完毕，只需要运行一行命令就可以打包你的应用

```bash
# dashboard 打包正式环境
yarn run build

# sidebar 打包正式环境
yarn run build
```

构建打包成功之后，会在根目录生成 `dist` 文件夹，里面就是构建打包好的文件，通常是 `.js` 、`.css`、`index.html` 等静态文件。

通常情况下 `dist` 文件夹的静态文件发布到你的 `nginx` 或者静态服务器即可，其中的 `index.html` 是后台服务的入口页面。

##### 更详细安装见: [传送门](https://mochat.wiki/quick-start/install.html)

### 项目介绍

#### 文件结构
```
.
├── api-server------------------------------------------ 后端接口代码
├── dashboard------------------------------------------- 商户后台前端代码
├── sidebar--------------------------------------------- 聊天侧边栏前端代码
└── workbench------------------------------------------- 工作台前端代码
└── operation------------------------------------------- 运营工具前端代码
```

##### 后端结构

```
.
├── app
│   ├── core-------------------------------------------- 核心应用目录
│   │   ├── chat-tool----------------------------------- 聊天侧边栏
│   │   ├── common-------------------------------------- 公共
│   │   ├── corp---------------------------------------- 企业
│   │   ├── index--------------------------------------- 首页
│   │   ├── install------------------------------------- 安装
│   │   ├── medium-------------------------------------- 媒体库
│   │   ├── official-account---------------------------- 公众号
│   │   ├── rbac---------------------------------------- RBAC权限
│   │   ├── tenant-------------------------------------- 租户
│   │   ├── user---------------------------------------- 用户
│   │   ├── work-agent---------------------------------- 企微应用
│   │   ├── work-contact-------------------------------- 客户
│   │   ├── work-department----------------------------- 部门
│   │   ├── work-employee------------------------------- 员工
│   │   ├── work-message-------------------------------- 消息
│   │   └── work-room----------------------------------- 客户群
│   └── utils------------------------------------------- 工具类
├── plugin
│   └── mochat------------------------------------------ 官方插件目录
│       ├── auto-tag------------------------------------ 自动打标签插件
│       ├── channel-code-------------------------------- 渠道活码插件
│       ├── contact-batch-add--------------------------- 批量添加好友插件
│       ├── contact-message-batch-send------------------ 批量发送客户消息插件
│       ├── contact-sop--------------------------------- 个人SOP插件
│       ├── contact-transfer---------------------------- 在职转接&离职继承插件
│       ├── greeting------------------------------------ 个人欢迎语插件
│       ├── lottery------------------------------------- 抽奖活动插件
│       ├── radar--------------------------------------- 雷达插件
│       ├── room-auto-pull------------------------------ 自动拉群插件
│       ├── room-calendar------------------------------- 群日历插件
│       ├── room-clock-in------------------------------- 群打卡插件
│       ├── room-fission-------------------------------- 群裂变插件
│       ├── room-infinite-pull-------------------------- 无限拉群插件
│       ├── room-message-batch-send--------------------- 批量发送群消息插件
│       ├── room-quality-------------------------------- 群聊质检
│       ├── room-remind--------------------------------- 群提醒
│       ├── room-sop------------------------------------ 群SOP
│       ├── room-tag-pull------------------------------- 标签拉群
│       ├── room-welcome-------------------------------- 群欢迎语插件
│       ├── sensitive-word------------------------------ 敏感词管理&监控插件
│       ├── shop-code----------------------------------- 门店活码
│       ├── statistic----------------------------------- 统计分析插件
│       └── work-fission-------------------------------- 企微里裂变
├── public
├── bin
├── composer.json
├── composer.lock
├── config
├── docker-compose.sample.yml
├── docker-entrypoint.sh
├── Dockerfile
├── migrations
├── package.json
├── phpstan.neon
├── phpunit.xml
├── README.MD
├── runtime
├── seeders
├── storage
├── test
└── vendor
```

##### 前端结构

```
dashboard 和 sidebar 项目结构类似
.
├── README.md------------------------------------------- 项目说明
├── babel.config.js------------------------------------- babel配置文件
├── config
│   ├── plugin.config.js-------------------------------- 插件配置文件
│   └── themePluginConfig.js---------------------------- 主题配置文件
├── jest.config.js
├── jsconfig.json
├── package.json
├── postcss.config.js
├── public
│   ├── favicon.ico------------------------------------- 浏览器icon
│   └── index.html-------------------------------------- Vue 入口模板
├── src
│   ├── App.vue----------------------------------------- Vue 模板入口
│   ├── api--------------------------------------------- Api ajax 等
│   ├── assets------------------------------------------ 本地静态资源
│   ├── components-------------------------------------- 业务通用组件
│   ├── core-------------------------------------------- 项目引导, 全局配置初始化，依赖包引入等
│   ├── global.less------------------------------------- 全局样式
│   ├── layouts----------------------------------------- 控制器
│   ├── main.js----------------------------------------- Vue 入口 JS
│   ├── router------------------------------------------ Vue-Router
│   ├── store------------------------------------------- Vuex
│   ├── utils------------------------------------------- 工具库
│   └── views------------------------------------------- 业务页面入口和常用模板
├── vue.config.js--------------------------------------- Vue主配置
└── webstorm.config.js---------------------------------- ide配置文件
```

### 联系作者加入群

![输入图片说明](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/contact-qr3.png "mochat微信.png")

### 部分演示图，持续更新

![输入图片说明](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/demo-1.png "demo演示.png")
![输入图片说明](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/demo-2.png "demo演示.png")
![输入图片说明](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/demo-3.png "demo演示.png")
![输入图片说明](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/demo-4.png "demo演示.png")
![输入图片说明](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/demo-5.png "demo演示.png")
![输入图片说明](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/demo-6.png "demo演示.png")
![输入图片说明](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/demo-7.png "demo演示.png")
![输入图片说明](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/demo-8.png "demo演示.png")
![输入图片说明](https://mochatcloud.oss-cn-beijing.aliyuncs.com/github/demo-9.png "demo演示.png")


### 版权声明

MoChat 开源版遵循 [`GPL-3.0`](https://github.com/mochat-cloud/mochat/blob/main/LICENSE "GPL-3.0") 开源协议发布，并提供免费研究使用，但绝不允许修改后和衍生的代码做为闭源的商业软件发布和销售！
