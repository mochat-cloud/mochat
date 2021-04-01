const path = require('path')
const webpack = require('webpack')
const { CleanWebpackPlugin } = require('clean-webpack-plugin')
const CompressionWebpackPlugin = require('compression-webpack-plugin')

module.exports = {
  entry: {
    antDesignVue: ['@ant-design/icons/lib/dist.js', 'moment/moment.js'],
    // antDesignVue: ['ant-design-vue'],
    echarts: ['echarts'],
    vueCropper: ['vue-cropper/dist/index.js'],
    corejs: ['core-js'],
    vueContainerQuery: ['vue-container-query/dist/VueContainerQuery.common.js']
  },
  output: {
    path: path.resolve(__dirname, '../public/dll'),
    filename: '[name].dll.js',
    library: '[name]_library'
  },
  plugins: [
    new CleanWebpackPlugin(),
    new webpack.DllPlugin({
      path: path.resolve(__dirname, '../public/dll', '[name]-manifest.json'),
      name: '[name]_library'
    }),
    new CompressionWebpackPlugin({
      filename: '[name].js.gz[query]',
      algorithm: 'gzip',
      test: new RegExp(
        'js$'
      ),
      threshold: 10240,
      minRatio: 0.8
    })
  ]
}
