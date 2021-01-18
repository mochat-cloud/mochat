<template>
  <div>
    <van-loading v-if="loading" type="spinner" vertical color="#1989fa" class="loading" >
    </van-loading>
    <div v-else class="message">
      <div>{{ message }}</div>
      <van-button v-if="btnShow" type="primary" @click="agent" class="button">点击重试</van-button>
    </div>
  </div>
</template>

<script>
// import axios from 'axios'
import { getCookie, setCookie, base64Decode } from '@/utils'
import { wxConfig, agentConfig, getContext } from '@/utils/wxCodeAuth'
import { getAgentInfo } from '@/api/wxconfig'

export default {
  data () {
    return {
      query: {},
      loading: true,
      btnShow: true,
      message: '',
      corpId: '',
      agentId: '',
      pageFlag: '',
      callValues: ''
    }
  },
  created () {
    this.dataInit()
  },
  methods: {
    dataInit () {
      const { callValues, agentId, pageFlag } = this.$route.query
      const token = getCookie('token')
      if (agentId && pageFlag) {
        this.agentId = agentId
        this.pageFlag = pageFlag
        setCookie('agentId', agentId)
        this.corpId = getCookie('corpId')
        if (!token) {
          this.agent()
        } else {
          this.login()
        }
      } else if (callValues) {
        this.getToken(callValues)
      }
    },
    getToken (callValues) {
      const {
        code, msg, data: {
          act,
          corpId,
          agentId,
          token,
          expire
        }
      } = JSON.parse(base64Decode(callValues))
      Object.assign(this, {
        corpId,
        agentId,
        pageFlag: act
      })
      if (code == 200 && token && expire) {
        const value = `Bearer ${token}`
        setCookie('token', value, expire)
        setCookie('agentId', agentId, expire)
        setCookie('corpId', corpId, expire)
        this.login()
      } else {
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
            message = msg
            break
          default:
            message = `登录失败请重新登录`
        }
        this.message = message
        this.loading = false
      }
    },
    async login () {
      const uriPath = this.$route.fullPath
      try {
        await wxConfig(this.corpId, uriPath)
        await agentConfig(this.corpId, uriPath, this.agentId)
        const entry = await getContext()
        if (entry == 'normal' || entry == 'group_chat_tools') {
          this.message = '请从个人聊天工具栏进入'
          this.loading = false
          this.btnShow = false
          return
        }
        this.$router.push({ path: `/${this.pageFlag}` })
      } catch (e) {
        console.log(e)
      }
    },
    async agent () {
      try {
        this.loading = true
        const { data } = await getAgentInfo({ agentId: this.agentId, isJsRedirect: 1, act: this.pageFlag })
        const { url } = data
        if (url) {
          window.location.href = url
        }
      } catch (e) {
        console.log(e)
      }
    }
  }
}
</script>

<style lang="less" scoped>
.message,.loading {
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
