import axios from 'axios'
import store from '@/store'
import storage from 'store'
import router from '@/router'
import message from 'ant-design-vue/es/message'
import { VueAxios } from './axios'

// 创建 axios 实例
const request = axios.create({
  // API 请求的默认前缀
  baseURL: process.env.VUE_APP_API_BASE_URL,
  timeout: 15000 // 请求超时时间
})

const newRequest = axios.create({
  // API 请求的默认前缀
  baseURL: process.env.VUE_APP_API_NEW_BASE_URL,
  timeout: 15000 // 请求超时时间
})

// 异常拦截处理器
const errorHandler = (error) => {
  if (error.response) {
    const data = error.response.data
    const status = error.response.status
    if (status === 401) {
      message.error(data.msg)
      store.dispatch('Logout').then(() => {
        router.push({ path: '/login' })
      })
    } else {
      message.error(`${status || ''}  ${data.msg || 'error'}`)
    }
  } else {
    message.error(error.message || '请求出错，请稍后重试！')
  }
  return Promise.reject(error)
}
const requestInterceptor = (config) => {
  const token = storage.get('ACCESS_TOKEN')
  // 如果 token 存在
  // 让每个请求携带自定义 token 请根据实际情况自行修改
  if (token) {
    config.headers['Accept'] = `application/json`
    config.headers.Authorization = token
  }
  return config
}
// request interceptor
request.interceptors.request.use(requestInterceptor, errorHandler)

// response interceptor
request.interceptors.response.use((response) => {
  return response.data
}, errorHandler)

newRequest.interceptors.request.use(requestInterceptor, errorHandler)
// response interceptor
newRequest.interceptors.response.use((response) => {
  return response.data
}, errorHandler)
const installer = {
  vm: {},
  install (Vue) {
    Vue.use(VueAxios, request)
  }
}

export default request

export {
  installer as VueAxios,
  request as axios,
  newRequest
}
