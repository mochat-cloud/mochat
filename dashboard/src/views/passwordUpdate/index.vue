<template>
  <div class="pass-word">
    <a-card>
      <a-form-model
        class="pass-model"
        ref="ruleForm"
        :model="form"
        :label-col="{ span: 5 }"
        :wrapper-col="{ span: 12 }">
        <a-form-model-item label="手机号码：">
          <h3>{{ userPhone }}</h3>
        </a-form-model-item>
        <a-form-model-item label="旧密码：">
          <a-input v-model="form.oldPassword" />
        </a-form-model-item>
        <a-form-model-item label="新密码：">
          <a-input v-model="form.newPassword" />
        </a-form-model-item>
        <a-form-model-item label="确认新密码：">
          <a-input v-model="form.againNewPassword" />
        </a-form-model-item>
        <div class="footer">
          <a-button v-permission="'/passwordUpdate/index@save'" type="primary" @click="updatePassWord">保存</a-button>
        </div>
      </a-form-model>
    </a-card>
  </div>
</template>
<script>
import { passWordUpdate } from '@/api/passWordUpdate'
import { mapGetters } from 'vuex'
export default {
  computed: {
    ...mapGetters(['userInfo'])
  },
  data () {
    return {
      userPhone: '',
      form: {}
    }
  },
  created () {
    const time = this.userInfo ? 0 : 2000
    setTimeout(() => {
      this.userPhone = this.userInfo.userPhone
    }, time)
  },
  methods: {
    updatePassWord () {
      passWordUpdate(this.form).then(res => {
        this.$store.dispatch('Logout').then(() => {
          this.$router.push({ name: 'login' })
        })
      })
    }
  }
}
</script>
<style lang='less' scoped>
.pass-model {
  width:500px;
  margin: 0 auto;
  .footer {
    width: 23%;
    margin: 0 auto;
    .ant-btn {
      width: 100px;
    }
  }
}

</style>
