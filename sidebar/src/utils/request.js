import { Toast } from 'vant'
import axios from 'axios'
import router from '@/router'
import { getCookie } from '@/utils'

// 创建 axios 实例
const request = axios.create({
  // API 请求的默认前缀
  baseURL: process.env.VUE_APP_API_BASE_URL,
  timeout: 10000 // 请求超时时间
})
const errorMessage = (message) => {
  Toast({ position: 'top', message })
}
// 异常拦截处理器
const errorHandler = (error) => {
  if (error.response) {
    const data = error.response.data
    const status = error.response.status
    if (status === 401) {
      errorMessage(data.msg)
      const agentId = getCookie('agentId')
      router.push({ path: '/codeAuth', query: { agentId, pageFlag: 'customer' } })
    } else {
      errorMessage(`${status || ''}  ${data.msg || 'error'}`)
    }
  } else {
    errorMessage(error.message || '请求出错，请稍后重试！')
  }
  return Promise.reject(error)
}

// request interceptor
request.interceptors.request.use(config => {
  config.headers['MoChat-Source-Type'] = 'wechat-app'
  config.headers['MoChat-Corp-Id'] = getCookie('corpId')
  const token = getCookie('token')
  // 如果 token 存在
  if (token) {
    config.headers.Accept = `application/json`
    config.headers.Authorization = token
  }
  return config
}, errorHandler)

// response interceptor
request.interceptors.response.use((response) => {
  return response.data
}, errorHandler)

export default request
