<template>
  <div class="room-fission-create">
    <div class="steps">
      <a-card>
        <a-steps :current="step">
          <a-step title="活动基本信息"/>
          <a-step title="设置活动海报"/>
          <a-step title="群聊和欢迎语素材设置"/>
          <a-step title="邀请客户参与"/>
        </a-steps>
      </a-card>
    </div>
    <a-card>
      <step1 v-show="step === 0" ref="step1"></step1>
      <step2 v-show="step === 1" ref="step2"></step2>
      <step3 v-show="step === 2" ref="step3"></step3>
      <step4 v-show="step === 3" ref="step4"></step4>
      <a-button class="mr" size="large" v-if="step !== 0" @click="lastStep">
        上一步
      </a-button>
      <a-button type="primary" size="large" @click="nextStep">
        <span v-if="step < 3">下一步</span>
        <span v-if="step === 3">完成</span>
      </a-button>
    </a-card>
  </div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import { storeApi } from '@/api/roomFission'

import step1 from './components/create/step/step1'
import step2 from './components/create/step/step2'
import step3 from './components/create/step/step3'
import step4 from './components/create/step/step4'

export default {
  components: { step1, step2, step3, step4 },
  data () {
    return {
      step: 0,
      activityAskData: {
        // 活动基本信息
        fission: {},
        // 欢迎语
        welcome: {},
        // 设置活动海报
        poster: {},
        // 群聊
        rooms: {},
        // 邀请客户
        invite: {}
      }
    }
  },
  methods: {
    // 下一步
    nextStep () {
      // 活动基本信息
      if (this.step == 0) {
        if (this.$refs.step1.outputStep1() == false) {
          return false
        } else {
          this.activityAskData.fission = this.$refs.step1.outputStep1()
        }
      }
      // 设置活动海报
      if (this.step == 1) {
        if (this.$refs.step2.outputStep2() == false) {
          return false
        } else {
          this.activityAskData.poster = this.$refs.step2.outputStep2()
        }
      }
      // 设置群聊和欢迎语
      if (this.step == 2) {
        if (this.$refs.step3.outputStep3() == false) {
          return false
        } else {
          const setp3News = this.$refs.step3.outputStep3()
          this.activityAskData.rooms = setp3News.rooms
          this.activityAskData.welcome = setp3News.welcome
        }
      }
      // 邀请客户参与
      if (this.step == 3) {
        if (this.$refs.step4.outputStep4() == false) {
          return false
        } else {
          this.activityAskData.invite = this.$refs.step4.outputStep4()
          storeApi(this.activityAskData).then((res) => {
            // console.log('成功')
            this.$message.success('创建成功')
            this.$router.push({ path: '/roomFission/index' })
          })
          return false
        }
      }
      this.step++
    // 欢迎语
    },
    //  上一步
    lastStep () {
      this.step--
    }
  }
}
</script>

<style lang="less" scoped>
.steps {
  margin-bottom: 16px;
}

.mr {
  margin-right: 16px;
}
</style>
