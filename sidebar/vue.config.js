// vue.config.js
const path = require('path')// 引入path模块
function resolve (dir) {
  return path.join(__dirname, dir)// path.join(__dirname)设置绝对路径
}
const vueConfig = {
  css: {
    loaderOptions: {
      less: {
        javascriptEnabled: true,
        modifyVars: {
          // 直接覆盖变量
          '@nav-bar-text-color': '#000000',
          '@nav-bar-icon-color': '#000000'
        }
      }
    }
  },

  devServer: {
    // development server port 8000
    port: 9000,
    open: true,
    overlay: {
      warnings: false,
      errors: true
    },
    clientLogLevel: 'warning'
  },
  productionSourceMap: false,
  configureWebpack: config => {
    if (process.env.NODE_ENV !== 'production') { // 开发环境配置
      config.devtool = 'source-map'
    }
  },
  chainWebpack: (config) => {
    config
      .plugin('html')
      .tap(args => {
        args[0].title = 'MoChat'
        return args
      })
    config.resolve.alias
      .set('@', resolve('./src'))
      .set('components', resolve('./src/components'))
      .set('views', resolve('./src/views'))
      .set('assets', resolve('./src/assets'))
      .set('utils', resolve('./src/utils'))
    // set第一个参数：设置的别名，第二个参数：设置的路径
  }
}


module.exports = vueConfig
