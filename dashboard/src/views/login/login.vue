<template>
  <div class="login-wrapper">
    <div class="contant">
      <div class="img-wrapper">
        <img class="img" :src="require('@/assets/title.png')" alt="">
      </div>
      <a-form
        id="formLogin"
        class="user-layout-login"
        ref="formLogin"
        :form="form"
        @submit="handleSubmit"
      >

        <div v-if="customActiveKey == 'tab1'">
          <a-form-item>
            <a-input
              class="input"
              size="large"
              type="text"
              placeholder="帐号"
              v-decorator="[
                'phone',
                {rules: [{ required: true, pattern: /^1[34578]\d{9}$/, message: '请输入帐号' }], validateTrigger: 'change'}
              ]"
            >
              <img slot="prefix" :src="require('@/assets/user.png')" alt="">
            </a-input>
          </a-form-item>

          <a-form-item>
            <a-input-password
              class="input"
              size="large"
              placeholder="密码"
              v-decorator="[
                'password',
                {rules: [{ required: true, message: '请输入密码' }], validateTrigger: 'blur'}
              ]"
            >
              <img slot="prefix" :src="require('@/assets/lock.png')" alt="">
            </a-input-password>
          </a-form-item>
          <a-alert v-if="isLoginError" type="error" show-icon message="登录失败" />
        </div>
        <div v-if="customActiveKey == 'tab2'">
          <a-form-item>
            <a-input class="input" size="large" type="text" placeholder="手机号" v-decorator="['mobile', {rules: [{ required: true, pattern: /^1[34578]\d{9}$/, message: '请输入正确的手机号' }], validateTrigger: 'change'}]">
              <a-icon slot="prefix" type="mobile" :style="{ color: 'rgba(0,0,0,.25)' }" />
            </a-input>
          </a-form-item>
          <a-row :gutter="16">
            <a-col class="gutter-row" :span="16">
              <a-form-item>
                <a-input class="input" size="large" type="text" placeholder="验证码" v-decorator="['captcha', {rules: [{ required: true, message: '请输入验证码' }], validateTrigger: 'blur'}]">
                  <a-icon slot="prefix" type="mail" :style="{ color: 'rgba(0,0,0,.25)' }" />
                </a-input>
              </a-form-item>
            </a-col>
            <a-col class="gutter-row" :span="8">
              <a-button
                class="getCaptcha"
                tabindex="-1"
                :disabled="state.smsSendBtn"
                @click.stop.prevent="getCaptcha"
                v-text="!state.smsSendBtn && '获取验证码' || (state.time+' s')"
              />
            </a-col>
          </a-row>
          <a-input
            class="input"
            size="large"
            placeholder="设置一个新的密码"
            v-decorator="[
              'newPassword',
              {rules: [{ required: true, message: '请输入密码' }], validateTrigger: 'blur'}
            ]"
          >
            <a-icon slot="prefix" type="lock" :style="{ color: 'rgba(0,0,0,.25)' }" />
          </a-input>
        </div>
        <a-form-item>
          <div class="btn-wrapper" v-if="customActiveKey == 'tab1'">
            <a-button
              type="primary"
              html-type="submit"
              class="login-button"
              :loading="state.loginBtn"
              :disabled="state.loginBtn"
            >
              登录
            </a-button>
            <a-button
              v-if="false"
              class="login-button"
              @click="handleTabClick"
              :loading="state.loginBtn"
              :disabled="state.loginBtn">
              忘记密码
            </a-button>
          </div>
          <div class="confirm-wrapper" v-if="customActiveKey == 'tab2'">
            <a-button
              type="primary"
              html-type="submit"
              class="login-button"
              :loading="state.loginBtn"
              :disabled="state.loginBtn"
            >
              确定
            </a-button>
          </div>
        </a-form-item>
      </a-form>
    </div>
    <div class="footer">Powered by <a class="mochat" href="https://mo.chat/" target="_blank">MoChat</a></div>
  </div>
</template>

<script>
// import md5 from 'md5'
import { mapActions } from 'vuex'
import store from '@/store'
import { resetRoutes } from '@/utils/menu'

export default {
  data () {
    return {
      customActiveKey: 'tab1',
      loginBtn: false,
      // login type: 0 email, 1 phone, 2 telephone
      loginType: 0,
      isLoginError: false,
      form: this.$form.createForm(this),
      state: {
        time: 60,
        loginBtn: false,
        // login type: 0 email, 1 phone, 2 telephone
        loginType: 0,
        smsSendBtn: false
      },
      background: require('../../assets/background.png')
    }
  },
  created () {

  },
  methods: {
    ...mapActions(['Login', 'Logout']),

    handleTabClick () {
      this.customActiveKey = 'tab2'
    },
    handleSubmit (e) {
      e.preventDefault()
      const {
        form: { validateFields },
        state,
        customActiveKey,
        Login
      } = this

      state.loginBtn = true

      const validateFieldsKey = customActiveKey === 'tab1' ? ['phone', 'password'] : ['mobile', 'captcha', 'newPassword']

      validateFields(validateFieldsKey, { force: true }, (err, values) => {
        if (!err) {
          const loginParams = { ...values }
          Login(loginParams)
            .then((res) => {
              this.loginSuccess(res)
            })
            .catch(err => this.requestFailed(err))
            .finally(() => {
              state.loginBtn = false
            })
        } else {
          setTimeout(() => {
            state.loginBtn = false
          }, 600)
        }
      })
    },
    getCaptcha (e) {
      e.preventDefault()
      const { form: { validateFields }, state } = this

      validateFields(['mobile'], { force: true }, (err, values) => {
        if (!err) {
          this.loginSuccess()
          state.smsSendBtn = true

          const interval = window.setInterval(() => {
            if (state.time-- <= 0) {
              state.time = 60
              state.smsSendBtn = false
              window.clearInterval(interval)
            }
          }, 1000)
        }
      })
    },
    async loginSuccess (res) {
      const data = await store.dispatch('getPermissionList')
      if (data) {
        resetRoutes()
        this.$router.push({ path: '/' })
        this.isLoginError = false
      } else {
        store.dispatch('Logout')
      }
    },
    requestFailed (err) {
      this.isLoginError = true
      console.log(err)
    }
  }
}
</script>

<style lang="less" scoped>
.login-wrapper {
  display: flex;
  height: 100%;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  background: url('../../assets/background.png') no-repeat center;
  .img-wrapper {
    width: 100%;
    width: 142px;
    margin: 0 auto;
    padding: 42px 0 45px 0;
    .img {
      width: 142px;
    }
  }
  .contant {
    width: 370px;
    height: 430px;
    margin-bottom: 25px;
    background: #293152;
    border-radius: 10px;
    box-shadow: 0px 11px 24px 0px rgba(18,21,40,0.58)
  }
  .input {
    height: 60px;
    font-size: 16px;
  }
  .footer {
    color: #e4eafa;
    font-size: 18px;
    font-weight: 400;
    margin-top: 80px;
  }
}
.user-layout-login {
  padding: 0 21px;
  label {
    font-size: 14px;
  }

  .getCaptcha {
    display: block;
    width: 100%;
    min-width: 70px;
    height: 40px;
  }

  .forge-password {
    font-size: 14px;
  }
  .btn-wrapper{
    width: 100%;
    margin-top: 25px;
    display: flex;
    justify-content: center;
  }
  .confirm-wrapper {
    width: 100%;
    margin-top: 40px;
    display: flex;
    justify-content: center;
  }
  button.login-button {
    padding: 0 15px;
    font-size: 20px;
    width: 100%;
    height: 60px;
    font-weight: 500;
  }
}
</style>
