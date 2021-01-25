- 安装依赖
```
yarn install
```

- 开发模式运行
```
yarn run dev
```

- 编译项目
```
yarn run build
```


- Git 提交规范
```
git add .
git cz
git push
```

- 项目目录
```
├── public
|   └── index.html           # Vue 入口模板
├── src
│   ├── api                  # Api 等
│   ├── assets               # 本地静态资源
│   ├── components           # 业务通用组件
│   ├── core                 # 项目引导, 全局配置初始化，依赖包引入等
│   ├── router               # Vue-Router
│   ├── store                # Vuex
│   ├── utils                # 工具库
│   ├── views                # 业务页面入口和常用模板
│   ├── App.vue              # Vue 模板入口
│   └── main.js              # Vue 入口 JS
│   └── global.less          # 全局样式
├── .cz-config.js          # git 提交规范
├── README.md
└── package.json
```

- 项目技术栈

vue   
https://cn.vuejs.org/

vue-router    
https://router.vuejs.org/zh/

vuex
https://vuex.vuejs.org/zh/

UI框架
Ant Design of Vue
https://www.antdv.com/docs/vue/introduce-cn/

Ant Design Pro 
https://pro.antdv.com/

