<template>
  <div class="work-fission-step3">
    <div class="block">
      <div class="title">
        企微推送
      </div>
      <div class="item">
        <span class="label">给员工推送：</span>
        <div class="input flex">
          <a-switch size="small" v-model="form.pushEmployee"/>
          <span class="ml6">开启后，若客户完成裂变任务，MoChat会给所属员工发送通知提醒</span>
        </div>
      </div>
      <div class="item">
        <span class="label">给客户推送：</span>
        <div class="input flex">
          <a-switch size="small" v-model="form.pushContact"/>
          <span class="ml6">客户裂变成功后，给客户发送裂变成功的提醒通知</span>
        </div>
      </div>
      <div class="preview-form-box flex" v-show="form.pushContact">
        <div class="form mr80">
          <div class="tips mb20">
            提示：<br>
            1、本次推送将占用1次「企业群发」机会，每个客户每月只可收到4次企业群发<br>
            2、如完成任务的客户本月已收次4次群发消息本条邀请将不会成功发送
          </div>
          <div class="item">
            <span class="label" style="width: 70px;">消息1：</span>
            <m-enter-text v-model="form.msgText"/>
          </div>
          <div class="item">
            <span class="label" style="width: 70px;">消息2：</span>
            <div class="content">
              <div class="mb20">
                选择消息类型：
                <a-radio-group
                  @change="selectChange"
                  :options="msgType.option"
                  v-model="msgType.value"
                  default-value="图片"
                />
              </div>
              <div class="operating">
                <div class="image" v-if="msgType.value === 'image'">
                  <m-upload v-model="form.msgComplex.image"></m-upload>
                </div>
                <div class="link" v-if="msgType.value === 'link'">
                  <div class="item url">
                    <a-input v-model="form.msgComplex.link.url" placeholder="链接地址请以http 或https开头"/>
                  </div>
                  <div class="item">
                    <span>链接标题：</span>
                    <div class="input">
                      <a-input v-model="form.msgComplex.link.title"/>
                    </div>
                  </div>
                  <div class="item">
                    <span>链接摘要：</span>
                    <div class="input">
                      <a-input v-model="form.msgComplex.link.desc"/>
                    </div>
                  </div>
                  <div class="item">
                    <span>链接封面：</span>
                    <div class="input">
                      <m-upload v-model="form.msgComplex.link.image"/>
                    </div>
                  </div>
                </div>
                <div class="applets" v-if="msgType.value === 'miniprogram'">
                  <a-alert
                    type="info"
                    show-icon
                    class="mb16"
                  >
                    <span slot="message">
                      只有在企业微信后台绑定的小程序才可在此添加哦 查看如何绑定
                    </span>
                  </a-alert>
                  <a-button @click="$refs.selectApplets.show()" v-if="!form.msgComplex.applets.title">添加小程序</a-button>
                  <div v-else>
                    小程序：{{ form.msgComplex.applets.title }}
                    <a-button @click="$refs.selectApplets.show()">重新添加</a-button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="preview">
          <m-preview ref="preview"/>
        </div>
      </div>
    </div>

    <selectApplets ref="selectApplets" @change="appletsChange"/>
  </div>
</template>

<script>
import selectApplets from '@/components/Select/applets'

export default {
  data () {
    return {
      form: {
        pushEmployee: false,
        pushContact: false,
        msgText: '',
        msgComplex: {
          link: {
            url: '',
            title: '',
            desc: '',
            image: ''
          },
          applets: {
            title: '',
            appid: '',
            path: '',
            image: ''
          },
          image: ''
        }
      },
      msgType: {
        option: [
          { label: '图片', value: 'image' },
          { label: '链接', value: 'link' },
          { label: '小程序', value: 'miniprogram' }
        ],
        value: 'image'
      }
    }
  },
  methods: {
    getVerify () {
      if (this.form.pushContact) {
        if (!this.form.msgText) return '消息1未填写'

        if (this.msgType.value === 'image' && !this.form.msgComplex.image) return '图片未上传'
        if (this.msgType.value === 'link' && !this.form.msgComplex.link.url) return '链接未填写'
        if (this.msgType.value === 'link' && !this.form.msgComplex.link.title) return '链接标题未填写'
        if (this.msgType.value === 'link' && !this.form.msgComplex.link.desc) return '链接描述未填写'
        if (this.msgType.value === 'link' && !this.form.msgComplex.link.image) return '链接封面图未上传'
      }
    },

    getFormData () {
      return {
        ...this.form,
        msgComplexType: this.msgType.value
      }
    },

    selectChange () {
      this.form.msgComplex = {
        link: {
          url: '',
          title: '',
          desc: '',
          image: ''
        },
        applets: {
          title: '',
          appid: '',
          path: '',
          image: ''
        },
        image: ''
      }
    },

    appletsChange (e) {
      this.form.msgComplex.applets = e
    }
  },
  watch: {
    'form.msgText': {
      handler () {
        this.$refs.preview.setText(this.form.msgText)
      },
      deep: true
    },
    'form.msgComplex': {
      handler () {
        this.$refs.preview.setImage(this.form.msgComplex.image)
        this.$refs.preview.setLink(this.form.msgComplex.link.title, this.form.msgComplex.link.desc, this.form.msgComplex.link.image)
        this.$refs.preview.setApplets(this.form.msgComplex.applets.title, this.form.msgComplex.applets.image)
      },
      deep: true
    }
  },
  components: { selectApplets }
}
</script>

<style lang="less" scoped>
.block {
  margin-bottom: 60px;

  .title {
    font-size: 15px;
    line-height: 21px;
    color: rgba(0, 0, 0, .85);
    border-bottom: 1px solid #e9ebf3;
    padding-bottom: 16px;
    margin-bottom: 16px;
    position: relative;

    span {
      font-size: 13px;
      margin-left: 11px;
      color: rgba(0, 0, 0, .45);
      font-weight: 400;
    }
  }

  .required:after {
    content: "*";
    display: inline-block;
    margin-right: 4px;
    color: #f5222d;
    font-size: 14px;
    line-height: 1;
    position: absolute;
    left: -10px;
    top: 6px;
  }

  .item {
    display: flex;
    align-items: center;
    margin-bottom: 23px;

    .label {
      color: rgba(0, 0, 0, .85);
      position: relative;
    }
  }
}

.content {
  width: 100%;
  border: 1px solid #eee;
  background: #fbfbfb;
  border-radius: 2px;
  padding: 20px;
}

.tips {
  padding: 13px 16px;
  background: #fff7f0;
  border-radius: 3px;
  font-size: 13px;
  line-height: 18px;
  color: #bb5223;
  margin-top: 22px;
}
</style>
