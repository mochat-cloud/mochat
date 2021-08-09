const autoprefixer = require('autoprefixer')
const pxtorem = require('postcss-pxtorem')
module.exports = ({ file }) => {
  let remUnit
  if (file && file.dirname && file.dirname.indexOf('vant') > -1) {
    remUnit = 37.5
  } else {
    remUnit = 75
  }
  return {
    plugins: [
      autoprefixer(),
      pxtorem({
        rootValue: remUnit,
        propList: ['*'],
        selectorBlackList: ['van-circle__layer']
      })
    ]
  }
}
