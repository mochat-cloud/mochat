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
      <step1 v-show="step === 0" ref="step1"></step1>
      <step2 v-show="step === 1" ref="step2"></step2>
      <a-button class="mt20 mr20" size="large" v-if="step !== 0" @click="step--">
        上一步
      </a-button>
      <a-button class="mt20" type="primary" size="large" v-if="step !== 1" @click="next">
        下一步
      </a-button>
      <a-button
        class="mt20"
        type="primary"
        size="large"
        v-if="step === 1"
        @click="addClick"
        :loading="loading"
      >
        完成
      </a-button>
    </a-card>
  </div>
</template>
<script>
import step1 from '@/views/lottery/components/step/step1'
import step2 from '@/views/lottery/components/step/step2'

import { addActivity } from '@/api/lottery'

export default {
  data () {
    return {
      step: 0,
      loading: false
    }
  },
  methods: {
    next () {
      const msg = this.$refs.step1.getErrMsg()

      if (msg) {
        this.$message.error(msg)

        return false
      }

      this.step++
    },

    addClick () {
      const msg = this.$refs.step2.getErrMsg()

      if (msg) {
        this.$message.error(msg)

        return false
      }

      this.loading = true

      addActivity({
        lottery: this.$refs.step1.getParams(),
        prize: this.$refs.step2.getParams()
      }).then(res => {
        this.$message.success('添加成功')
        this.loading = false
        this.$router.push('/lottery/index')
      })
    }

  },
  components: { step1, step2 }

}
</script>

<style lang="less" scoped>

</style>
