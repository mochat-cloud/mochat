<template>
  <div class="box">
    <a-card class="mb16">
      <div class="steps">
        <a-steps :current="step">
          <a-step title="抽奖活动设置"/>
          <a-step title="奖品设置"/>
        </a-steps>
      </div>
    </a-card>

    <a-card>
      <step3 v-show="step === 0" ref="step3"></step3>
      <step4 v-show="step === 1" ref="step4"></step4>
      <a-button class="mt20 mr20" size="large" v-if="step !== 0" @click="step--">
        上一步
      </a-button>
      <a-button class="mt20" type="primary" size="large" v-if="step !== 1" @click="step++">
        下一步
      </a-button>
      <a-button class="mt20" type="primary" size="large" v-if="step === 1" @click="editClick">
        完成
      </a-button>
    </a-card>
  </div>
</template>
<script>
import step3 from '@/views/lottery/components/step/step3'
import step4 from '@/views/lottery/components/step/step4'
import { modify, update } from '@/api/lottery'

export default {
  data () {
    return {
      step: 0
    }
  },
  mounted () {
    this.getData()
  },
  methods: {
    editClick () {
      const msg = this.$refs.step4.getErrMsg()

      if (msg) {
        this.$message.error(msg)

        return false
      }

      update({
        lottery: {
          id: this.$route.query.id,
          ...this.$refs.step3.getParams()
        },
        prize: this.$refs.step4.getParams()
      }).then(res => {
        this.$message.success('修改成功')
        this.$router.push('/lottery/index')
      })
    },

    getData () {
      modify({
        id: this.$route.query.id
      }).then(res => {
        this.$refs.step3.setParams(res.data)
        this.$refs.step4.setParams(res.data)
      })
    }
  },
  components: { step3, step4 }
}
</script>

<style lang="less" scoped>

</style>
