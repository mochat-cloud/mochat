
export function timeFix () {
  const time = new Date()
  const hour = time.getHours()
  return hour < 9 ? '早上好' : hour <= 11 ? '上午好' : hour <= 13 ? '中午好' : hour < 20 ? '下午好' : '晚上好'
}

export function isIE () {
  const bw = window.navigator.userAgent
  const compare = (s) => bw.indexOf(s) >= 0
  const ie11 = (() => 'ActiveXObject' in window)()
  return compare('MSIE') || ie11
}
export const createValidate = (callback, value, message) => {
  if (!value) {
    return callback(new Error(message))
  } else {
    callback()
  }
}
export const createFunc = (func, change) => {
  return {
    validator: func,
    trigger: change || 'blur'
  }
}
