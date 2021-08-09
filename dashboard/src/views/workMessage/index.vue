<template>
  <div class="chat-record">
    <div v-if="loading" class="loading">
      <a-spin size="large" />
    </div>
    <a-card v-else :bordered="false">
      <h2>完成以下步骤就可以成功开通啦！</h2>
      <a-steps class="steps" :current="currentStep">
        <a-step title="填写企业信息" />
        <a-step title="添加客服提交资料" />
        <a-step title="配置后台" />
      </a-steps>
      <a-form-model
        v-if="currentStep === 0"
        class="information"
        :label-col="{ span: 7 }"
        :wrapper-col="{ span: 10}"
        :model="formData"
        :rules="rules"
        ref="ruleForm"
      >
        <a-form-model-item label="企业名称:">
          <a-input
            disabled
            v-model="info.corpName"
          />
        </a-form-model-item>
        <a-form-model-item label="企业ID:">
          <a-input
            disabled
            v-model="info.wxCorpId"/>
        </a-form-model-item>
        <a-form-model-item label="企业代码:" prop="socialCode">
          <a-input
            v-model="info.socialCode"
            :maxLength="18"
            placeholder="请输入统一社会信用代码"/>
        </a-form-model-item>
        <a-form-model-item label="企业负责人:" prop="chatAdmin">
          <a-input
            v-model="info.chatAdmin"
            placeholder="请输入企业负责人姓名"/>
        </a-form-model-item>
        <a-form-model-item label="企业负责人身份证:" prop="chatAdminIdcard">
          <a-input
            v-model="info.chatAdminIdcard"
            :maxLength="18"
            placeholder="请输入企业负责人身份证号"/>
        </a-form-model-item>
        <a-form-model-item label="企业负责人电话:" prop="chatAdminPhone">
          <a-input
            v-model="info.chatAdminPhone"
            placeholder="请输入企业负责人电话"/>
        </a-form-model-item>
        <a-form-model-item :wrapper-col="{ span: 12, offset: 10 }">
          <a-button type="primary" @click="handleSubmit">
            保存
          </a-button>
        </a-form-model-item>
      </a-form-model>
      <div v-if="currentStep == 1">
        <div class="qr-wrapper">
          <img class="img" :src="serviceContactUrl" alt="">
        </div>
        <div class="btn-wrapper">
          <a-button class="next" type="primary" @click="next">下一步</a-button>
        </div>
      </div>
      <div
        class="configuration"
        v-if="currentStep == 2">
        <div class="label-wrapper">
          <span class="text">可信IP地址:</span>
          <div class="containt">
            <div
              class="ip"
              v-for="(item, index) in chatWhitelistIp"
              :key="index">
              {{ item }}
            </div>
          </div>
        </div>
        <div class="label-wrapper">
          <span class="text">信息机密公钥:</span>
          <ul class="containt">
            <li class="key">
              <span class="key-name">公钥</span>
              <a-textarea class="input" disabled v-model="chatRsaKey.publicKey"></a-textarea>
              <a-button
                class="copy"
                type="primary"
                size="small"
                @click="copy(chatRsaKey.publicKey)">复制</a-button>
            </li>
            <li class="key">
              <span class="key-name">私钥</span>
              <a-textarea class="input" disabled v-model="chatRsaKey.privateKey"></a-textarea>
              <a-button
                class="copy"
                type="primary"
                size="small"
                @click="copy(chatRsaKey.privateKey)">复制</a-button>
            </li>
          </ul>
        </div>
        <div class="label-wrapper">
          <span class="text">会话存档Secret:</span>
          <div class="containt">
            <a-input class="input" v-model="chatSecret"></a-input>
          </div>
        </div>
        <div class="label-wrapper">
          <span class="text">公钥版本号:</span>
          <div class="containt">
            <a-input class="input" v-model="chatRsaKey.version"></a-input>
          </div>
        </div>
        <div class="label-wrapper">
          <span class="text">会话存档状态:</span>
          <div class="containt">
            <a-switch v-model="chatStatus" @change="statusChange" />
          </div>
        </div>
        <div class="btn-wrapper">
          <a-button class="btn" type="primary" @click="save">
            保存
          </a-button>
        </div>

      </div>
    </a-card>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import { corpStore, stepCreate, stepUpdate } from '@/api/workMessage'
export default {
  data () {
    const createValidate = (callback, value, message) => {
      if (!value) {
        return callback(new Error(message))
      } else {
        callback()
      }
    }
    const createFunc = (func, change) => {
      return {
        validator: func,
        trigger: change || 'blur'
      }
    }
    const vsocialCode = (rule, value, callback) => {
      const reg = /^[A-Za-z0-9]{18}$/
      value = reg.test(this.info.socialCode)
      createValidate(callback, value, '请输入企业代码')
    }
    const vchatAdmin = (rule, value, callback) => {
      value = this.info.chatAdmin
      createValidate(callback, value, '请输入企业负责人姓名')
    }
    const vchatAdminIdcard = (rule, value, callback) => {
      const reg = /^\d{17}(\d|X|x)$/
      value = reg.test(this.info.chatAdminIdcard)
      createValidate(callback, value, '请输入企业负责人身份证')
    }
    const vchatAdminPhone = (rule, value, callback) => {
      const reg = /^1[3-9][0-9]\d{8}$/
      value = reg.test(this.info.chatAdminPhone)
      createValidate(callback, value, '请输入企业负责人电话')
    }
    return {
      loading: true,
      currentStep: 0,
      // 接口返回 当前进度 第一步:0、1 第二步:2 第三步:3  完成:4
      chatApplyStatus: 0,
      // 第一步
      info: {
        id: '',
        wxCorpId: '',
        socialCode: '',
        chatAdmin: '',
        chatAdminPhone: '',
        chatAdminIdcard: ''
      },
      formData: {},
      rules: {
        socialCode: createFunc(vsocialCode),
        chatAdmin: createFunc(vchatAdmin),
        chatAdminPhone: createFunc(vchatAdminPhone),
        chatAdminIdcard: createFunc(vchatAdminIdcard)
      },
      // 二维码
      serviceContactUrl: '',
      id: '',
      chatWhitelistIp: [],
      chatRsaKey: {},
      chatSecret: '',
      chatStatus: false
    }
  },
  computed: {
    ...mapGetters(['corpId', 'corpName'])
  },
  created () {
    this.getwxData()
  },
  methods: {
    handleSubmit () {
      this.$refs.ruleForm.validate(valid => {
        if (valid) {
          // 添加
          delete this.info.id
          const params = {
            ...this.info,
            chatApplyStatus: 1
          }
          corpStore(params).then(res => {
            this.currentStep = 1
          })
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    statusChange (value) {
      this.chatStatus = value
    },
    async getwxData () {
      try {
        const { data: { id, corpName, chatApplyStatus, wxCorpId, serviceContactUrl, chatWhitelistIp, rsaPublicKey, rsaPrivateKey } } = await stepCreate()
        this.info.corpName = corpName
        this.info.wxCorpId = wxCorpId
        this.id = id
        this.serviceContactUrl = serviceContactUrl
        this.chatWhitelistIp = chatWhitelistIp
        this.chatRsaKey = {
          publicKey: rsaPublicKey,
          privateKey: rsaPrivateKey,
          version: ''
        }
        this.chatApplyStatus = chatApplyStatus
        if (chatApplyStatus != 0) {
          if (chatApplyStatus == 4) {
            this.$router.push({ path: '/workMessage/toUsers' })
            return
          }
          this.currentStep = chatApplyStatus - 1
        }
        this.loading = false
      } catch (e) {

      }
    },
    next () {
      this.currentStep = 2
      const params = {
        chatApplyStatus: 2
      }
      stepUpdate(params)
    },
    copy (key) {
      this.$copyText(key).then(message => {
        console.log('copy', message)
        this.$message.success('复制完毕')
      }).catch(err => {
        console.log('copy.err', err)
        this.$message.error('复制失败')
      })
    },
    async save () {
      const params = {
        chatApplyStatus: 3,
        chatWhitelistIp: this.chatWhitelistIp,
        chatRsaKey: this.chatRsaKey,
        chatSecret: this.chatSecret,
        chatStatus: this.chatStatus ? 1 : 0
      }
      try {
        await stepUpdate(params)
        this.$router.push({ path: '/workMessage/toUsers' })
      } catch (e) {
        console.log(e)
      }
    }
  }
}
</script>

<style lang="less" scoped>
.chat-record {
  .loading {
    height: 500px;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  h2 {
    width: 340px;
    margin: 0 auto 40px;
  }
  .information {
    margin-top: 40px;
  }
  .configuration{
    margin-top: 40px;
  }
  .label-wrapper {
    display: flex;
    margin-bottom: 20px;
    .text {
      flex: 0 0 120px;
      text-align: right;
      margin-right: 10px;
    }
    .containt {
      flex: 1;
      padding: 0;
      .key {
        display: flex;
        align-items: center;
      }
      .key-name {
        margin-right: 10px
      }
      .copy {
        margin-left: 10px
      }
      .select,.input {
        width: 100%;
        max-width: 400px;
      }
      .ip,.key {
        margin-bottom: 10px;
      }
    }
  }
  .btn-wrapper {
    margin: 0 auto;
    width: 150px;
    text-align: center;
  }
  .qr-wrapper {
    width: 200px;
    margin: 20px auto;
    .img {
      width: 200px;
    }
  }
}
</style>
