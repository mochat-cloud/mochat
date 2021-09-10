<template>
  <div>
    <van-loading v-if="loading" type="spinner" vertical color="#1989fa" class="loading">
    </van-loading>
    <div v-else class="message">
      <div>{{ message }}</div>
      <van-button v-if="btnShow" type="primary" @click="retry" class="button">点击重试</van-button>
    </div>
  </div>
</template>

<script>
// import axios from 'axios'
import { setCookie, base64Decode } from '@/utils'
// import {wxConfig, agentConfig, getContext} from '@/utils/wxCodeAuth'
// import {getAgentInfo} from '@/api/wxconfig'

export default {
  data () {
    return {
      query: {},
      loading: true,
      btnShow: true,
      message: '',
      state: {},
      target: '',
      agentId: 0
    }
  },
  async created () {
    await this.dataInit()
  },
  methods: {
    async dataInit () {
      const { state, target, agentId } = this.$route.query
      this.agentId = agentId
      setCookie('agentId', agentId)
      this.state = JSON.parse(base64Decode(state))
      this.target = decodeURIComponent(target)
      await this.checkLoginResult()
    },

    /**
     * 检查登录结果
     *
     * @returns {boolean}
     */
    async checkLoginResult () {
      if (this.state.code === 200) {
        const token = this.state.data.token
        const expire = this.state.data.expire
        setCookie('token', token, expire - 120)
        this.loading = false
        this.btnShow = false
        await this.push()
        return true
      }

      this.showError(this.state.code, this.state.msg)
    },
    getLocation (url) {
      const location = new URL(url)
      const query = {}
      const index = url.indexOf('?')

      if (index !== -1) {
        const params = url.substr(index + 1)
        const parr = params.split('&')
        for (const i of parr) {
          const arr = i.split('=')
          query[arr[0]] = arr[1]
        }
      }

      return { path: location.pathname, query: query }
    },

    async push () {
      const location = this.getLocation(this.target)
      this.$router.push(location)
    },

    showError (code, msg) {
      let message
      switch (code) {
        case 100014:
          message = '应用的可信域名错误，请检查微信后台配置'
          break
        case 100001:
          message = '授权code码失效'
          break
        case 100008:
          message = '账户异常，无法登录'
          break
        case 100013:
        case 100012:
        case 100016:
          message = '参数错误'
          break
        default:
          message = `登录失败请重新登录`
      }
      console.error(msg)
      this.message = message
      this.loading = false
    },

    retry () {
      window.location.href = this.target
    }
  }
}
</script>

<style lang="less" scoped>
  .message, .loading {
    background: #f3f4f8;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 600px;
  }

  .message {
    flex-direction: column;
    font-size: 30px;

    .button {
      margin-top: 50px;
    }
  }

</style>
