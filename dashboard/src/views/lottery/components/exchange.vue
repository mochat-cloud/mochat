<template>
  <div class="word-fission-details">
    <a-modal v-model="modalShow" @ok="ok" :width="650" centered>
      <template slot="title">兑换设置</template>
      <div class="content">
        <div class="mb16">
          <span>兑奖方式：</span>
          <a-radio-group name="radioGroup" :default-value="1" v-model="type">
            <a-radio :value="1">
              客服二维码
            </a-radio>
            <a-radio :value="2">
              兑换码
            </a-radio>
          </a-radio-group>
        </div>
        <div class="ml70" v-show="type === 1">
          <m-upload :def="false" text="请上传二维码" v-model="qrCode" ref="upload"></m-upload>
        </div>
        <div class="ml70" v-show="type === 2">
          <span>请输入兑换码：</span>
          <a-textarea v-model="code" class="mt6" placeholder="兑换码一行一个" :rows="6"/>
        </div>
        <div class="mt16">
          <span>兑换须知：</span>
          <a-textarea v-model="description" class="mt6" placeholder="请输入文字" :rows="4"/>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script>

export default {
  data () {
    return {
      modalShow: false,
      id: '',
      type: 1,
      qrCode: '',
      code: '',
      description: ''
    }
  },
  methods: {
    ok () {
      if (this.type === 1 && !this.qrCode) {
        this.$message.error('请上传客服二维码')

        return false
      }

      if (this.type === 2 && !this.code) {
        this.$message.error('请填写兑换码')

        return false
      }

      if (!this.description) {
        this.$message.error('请填写兑换须知')

        return false
      }

      const oldCode = this.code

      let code = JSON.parse(JSON.stringify(this.code)).split(/[(\r\n)\r\n]+/)

      code.forEach((item, index) => {
        if (!item) {
          code.splice(index, 1)
        }
      })

      code = Array.from(new Set(code))

      this.$emit('change', {
        id: this.id,
        type: this.type,
        qrCode: this.qrCode,
        code,
        description: this.description,
        exchange_code_old: oldCode
      })

      this.hide()
    },

    show (id, data) {
      if (data.type) {
        this.type = data.type

        if (data.employee_qr) this.$refs.upload.setUrl(data.employee_qr)
        if (data.description) this.description = data.description
        if (data.exchange_code_old) this.code = data.exchange_code_old
      }

      this.id = id

      this.modalShow = true
    },

    hide () {
      this.modalShow = false

      this.qrCode = ''
      this.code = ''
      this.id = ''
      this.description = ''
      this.type = 1

      this.$refs.upload.setUrl('')
    }
  }
}
</script>

<style lang="less" scoped>
/deep/ .ant-modal-title {
  text-align: center;
  font-size: 17px;
  font-weight: 600;
  letter-spacing: 1px;
}
</style>
