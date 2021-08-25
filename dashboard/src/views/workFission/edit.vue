<template>
  <div class="work-fission-create">
    <div class="steps mb20">
      <a-card>
        <a-steps :current="step">
          <a-step title="活动基本信息"/>
          <a-step title="设置欢迎语和海报"/>
          <a-step title="设置推送消息"/>
        </a-steps>
      </a-card>
    </div>
    <a-card>
      <div class="mb20">
        <step1 ref="step1" v-show="step === 0"></step1>
        <step2 ref="step2" v-show="step === 1"></step2>
        <step3 ref="step3" v-show="step === 2"></step3>
      </div>
      <a-button
        class="mr"
        size="large"
        v-if="step !== 0"
        @click="step--"
      >
        上一步
      </a-button>
      <a-button
        type="primary"
        size="large"
        v-if="step !== 2"
        @click="step++">
        下一步
      </a-button>
      <a-button
        type="primary"
        size="large"
        v-if="step === 2"
        @click="update"
      >
        修改
      </a-button>
    </a-card>
  </div>
</template>

<script>
import step1 from './components/update/step/step1'
import step2 from './components/update/step/step2'
import step3 from './components/update/step/step3'

import { getInfo, update } from '@/api/workFission'

export default {
  data () {
    return {
      step: 0,
      data: {}
    }
  },
  mounted () {
    const id = this.$route.query.id

    this.getUpdateInfo(id)
  },
  methods: {
    getUpdateInfo (id) {
      getInfo({ id }).then(res => {
        this.$refs.step1.setOrgData(res.data)
        this.$refs.step2.setOrgData(res.data)
        this.$refs.step3.setOrgData(res.data)
      })
    },

    getFissionData () {
      const data = this.$refs.step1.getFormData()
      const params = {
        active_name: data.name,
        service_employees: data.services,
        auto_pass: data.autoPass,
        auto_add_tag: data.autoAddTag,
        end_time: data.endTime.format('YYYY-MM-DD hh:ss'),
        qr_code_invalid: data.expireDay,
        tasks: data.tasks,
        receive_prize: data.receivePrizeType,
        receive_prize_employees: data.receivePrizeServices,
        receive_links: data.receivePrizeLinks,
        contact_tags: []
      }
      return params
    },
    getPosterData () {
      const data = this.$refs.step2.getFormData()
      const params = {
        poster_type: data.posterType,
        foward_text: data.shareText,
        avatar_show: data.poster.avatar,
        nickname_show: data.poster.nickname,
        nickname_color: data.poster.nicknameColor,
        card_corp_image_name: data.card.nickname,
        card_corp_name: data.card.name,
        card_corp_logo: data.card.logoUrl,
        cover_pic: data.poster.imageUrl,
        qrcode_w: data.poster.imageW,
        qrcode_h: data.poster.imageH,
        qrcode_x: data.poster.imageX,
        qrcode_y: data.poster.imageY
      }

      return params
    },

    getWelcomeData () {
      const data = this.$refs.step2.getFormData()

      const params = {
        msg_text: data.welcome.text,
        link_title: data.welcome.link.title,
        link_desc: data.welcome.link.desc,
        link_cover_url: data.welcome.link.imageUrl
      }

      return params
    },
    // 步骤3传值
    getPushData () {
      const data = this.$refs.step3.getFormData()
      const params = {
        push_employee: data.pushEmployee,
        push_contact: data.pushContact,
        msg_text: data.msgText,
        msg_complex: data.msgComplex,
        msg_complex_type: data.msgComplexType
      }
      return params
    },

    update () {
      const params = {
        fission: {
          id: this.$route.query.id,
          ...this.getFissionData()
        },
        poster: this.getPosterData(),
        welcome: this.getWelcomeData(),
        push: this.getPushData()
      }

      const loading = this.$message.loading('修改中', 1.5)

      update(params).then(res => {
        if (res.code === 200) {
          loading.then(() => {
            this.$message.success('修改成功', 2.5)
            this.$router.push('/workFission/taskpage')
          }).then(() => {

          })
        }
      })
    }
  },
  components: { step1, step2, step3 }
}
</script>

<style lang="less" scoped>
.mr {
  margin-right: 16px;
}
</style>
