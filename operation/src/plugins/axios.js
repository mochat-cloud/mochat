import Vue from "vue";
import axios from "axios";
import message from 'ant-design-vue/es/message'

const request = axios.create({
  baseURL: process.env.VUE_APP_API_BASE_URL + '/operation',
  timeout: 15000
})

const errorHandler = (error) => {
  if (error.response) {
    const data = error.response.data
    const status = error.response.status
    message.error(`${status || ''}  ${data.msg || 'error'}`)
  } else {
    message.error(error.message || '请求出错，请稍后重试！')
  }
  return Promise.reject(error)
};

request.interceptors.request.use((config) => {
  return config
}, errorHandler)

request.interceptors.response.use((response) => {
  return response.data
}, errorHandler)

Vue.prototype.$http = request;

export default request;
