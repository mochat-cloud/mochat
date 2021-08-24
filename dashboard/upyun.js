module.exports = {
  operator: 'gewu',
  password: 'V7r0ix2bXoRgnqAmCmxrOBtWXA3lziji',
  tasks: [{
    bucket: 'img-gewu',
    prefix: 'dashboard_images/', // upload path prefix
    // endpoint: '', //  API接入点
    directory: 'dist', // 图片文件目录
    rename: (origin) => {
      return origin
    }
  }]
}
