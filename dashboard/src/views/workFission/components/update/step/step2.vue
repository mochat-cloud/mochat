<template>
  <div class="word-fission-step2">
    <div class="block">
      <div class="title">
        裂变海报设置
      </div>
      <div class="item">
        <span class="label">裂变海报：</span>
        <div class="input">
          <a-radio-group
            :options="posterRadio.list"
            v-model="posterRadio.value"
          />
        </div>
      </div>
      <div class="content" v-show="posterRadio.value === '0'">
        <div class="poster-preview">
          <img
            :src="form.poster.imageUrl"
            class="bg">
          <div class="user-info">
            <div class="avatar">
              <img
                v-if="form.poster.avatar"
                src="../../../../../assets/mission-create-default-avatar.jpg">
            </div>
            <div
              class="name"
              :style="{'color':form.poster.nicknameColor}"
              v-if="form.poster.nickname"
            >
              用户昵称
            </div>
          </div>
          <vue-drag-resize
            :w="82"
            :h="82"
            :x="120"
            :y="300"
            :sticks="['br','tl','tr','bl']"
            :aspectRatio="true"
            @resizing="qrcodeZoom"
            @dragging="qrcodeZoom"
          >
            <div class="qr-code">
              <img src="../../../../../assets/qr-preview.png">
            </div>
          </vue-drag-resize>
        </div>
        <div class="setup">
          <div class="setup-title">
            海报设置：
          </div>
          <div class="row">
            <div class="switch">
              <span>用户头像</span>
              <a-switch size="small" v-model="form.poster.avatar" default-checked/>
            </div>
            <div class="switch">
              <span>用户昵称</span>
              <a-switch size="small" v-model="form.poster.nickname" default-checked/>
            </div>
          </div>
          <div class="row">
            昵称颜色：
            <colorPicker v-model="form.poster.nicknameColor"/>
          </div>
          <div class="row">
            <m-upload ref="posterImage" v-model="form.poster.imageUrl" type="btn" :preview="false"/>
          </div>
          <div class="tips">
            <p>裂变海报设计须知：</p>
            <p>（1）尺寸：720px*1280px，分辨率72</p>
            <p>（2）[用户头像] [用户昵称] [裂变带参二维码] 这三个元素需要空出</p>
            <p>（3）裂变海报其他部分皆可自定义设计</p>
            <p>（4）裂变海报大小不超过2M</p>
          </div>
        </div>
      </div>
      <div class="content" v-show="posterRadio.value === '1'">
        <div class="card-preview">
          <div class="company-info">
            <div class="avatar">
              <img :src="form.card.logoUrl">
            </div>
            <div class="name">
              {{ form.card.nickname }}
              <div class="desc">
                {{ form.card.name }}
              </div>
            </div>
          </div>
          <div class="qrcode">
            <img src="../../../../../assets/qr-preview.png">
            <img class="logo" :src="form.card.logoUrl">
          </div>
          <div class="qrcode-tips">
            扫一扫上面的二维码图案<br>
            加我企业微信
          </div>
        </div>
        <div class="setup">
          <div class="mb20">
            <div class="mb5">企业形象名称：</div>
            <a-input v-model="form.card.nickname" placeholder="请输入"/>
          </div>
          <div class="mb20">
            <div class="mb5">企业名称：</div>
            <a-input v-model="form.card.name" placeholder="请输入"/>
          </div>
          <div>
            <div class="mb5">上传企业LOGO：</div>
            <m-upload ref="cardLogo" v-model="form.card.logoUrl" type="btn" :preview="false"/>
          </div>
        </div>
      </div>
      <div class="item mt20">
        <span class="label">裂变海报：</span>
        <div class="input flex">
          <a-switch size="small" v-model="posterTextSwitch"/>
          <span class="ml6">
            设置海报转发话术，开启后客户可直接复制文案分享
          </span>
        </div>
      </div>
      <div class="item" v-show="posterTextSwitch" style="width: 800px;">
        <m-enter-text ref="fissionText" v-model="form.shareText"/>
      </div>
    </div>
    <div class="welcome-form-box flex">
      <div class="form mr70" style="width: 63%;">
        <div class="block">
          <div class="title">
            欢迎语设置
          </div>
          <div class="welcome-box ml30">
            <div class="welcome-text">
              <div class="item mb0">
                <span class="label required">欢迎语：</span>
                <div class="text-1">
                  <div class="input">
                    <div class="insert-btn-group" @click="$refs.welcomeText.addUserName('[用户昵称]')">
                      <span>[插入客户名称]</span>
                    </div>
                    <m-enter-text ref="welcomeText" v-model="form.welcome.text"/>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="block">
          <div class="title">
            链接设置
          </div>
          <div class="text-2 ml30">
            <div class="link-form">
              <div class="item">
                <span class="label required">链接标题：</span>
                <div class="input">
                  <a-input v-model="form.welcome.link.title"/>
                </div>
              </div>
              <div class="item">
                <span class="label required">链接摘要：</span>
                <div class="input">
                  <a-input v-model="form.welcome.link.desc"/>
                </div>
              </div>
              <div class="item">
                <span>链接封面：</span>
                <div class="input">
                  <m-upload ref="linkImage" v-model="form.welcome.link.imageUrl"/>
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
</template>

<script>
export default {
  data () {
    return {
      posterRadio: {
        list: [
          { label: '上传海报', value: '0' },
          { label: '个人名片', value: '1' }
        ],
        value: '0'
      },
      posterTextSwitch: false,
      form: {
        poster: {
          avatar: true,
          nickname: true,
          nicknameColor: '#000',
          imageUrl: '',
          imageX: 120,
          imageY: 300,
          imageW: 82,
          imageH: 82
        },
        shareText: '',
        card: {
          nickname: '企业昵称',
          name: '企业名',
          logoUrl: ''
        },
        welcome: {
          text: '',
          link: {
            title: '链接标题',
            desc: '链接摘要',
            imageUrl: ''
          }
        }
      }
    }
  },
  methods: {
    setOrgData (data) {
      const poster = data.poster
      const welcome = data.welcome

      this.posterRadio.value = poster.poster_type.toString()

      this.form.poster.nickname = poster.nickname_show === 'true'
      this.form.poster.avatar = poster.avatar_show === 'true'
      this.form.poster.nicknameColor = poster.nickname_color
      this.$refs.posterImage.setUrl(poster.cover_pic)

      if (poster.foward_text) {
        this.posterTextSwitch = true
        this.$refs.fissionText.addUserName(poster.foward_text)
      }

      this.form.card.nickname = poster.card_corp_image_name
      this.form.card.name = poster.card_corp_name
      this.$refs.cardLogo.setUrl(poster.card_corp_logo)
      this.$refs.welcomeText.addUserName(welcome.msg_text)
      this.form.welcome.link.title = welcome.link_title
      this.form.welcome.link.desc = welcome.link_desc
      this.$refs.linkImage.setUrl(welcome.link_cover_url)
    },

    getFormData () {
      return {
        ...this.form,
        posterType: this.posterRadio.value
      }
    },

    qrcodeZoom (e) {
      this.form.poster.imageW = e.width
      this.form.poster.imageH = e.height
      this.form.poster.imageX = e.left
      this.form.poster.imageY = e.top
    }
  },
  watch: {
    'form.welcome.text': {
      handler () {
        this.$refs.preview.setText(this.form.welcome.text)
      },
      deep: true
    },
    'form.welcome.link': {
      handler () {
        const data = this.form.welcome.link
        this.$refs.preview.setLink(data.title, data.desc, data.imageUrl)
      },
      deep: true
    }
  }
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
  background: #fbfbfb;
  border: 1px solid #eee;
  border-radius: 2px;
  padding: 19px 27px 17px 24px;
  margin-top: 15px;
  display: flex;
  max-width: 800px;

  .poster-preview {
    width: 224px;
    height: 398px;
    background: #fff;
    border: 1px solid #ededed;
    box-shadow: 0 1px 3px rgb(43 43 43 / 6%);
    position: relative;
    overflow: hidden;

    .bg {
      width: 100%;
      height: 100%;
      position: absolute;
      top: 0;
      left: 0;
    }
  }

  .card-preview {
    width: 224px;
    height: 280px;
    background: #fbfbfb;
    border: 1px solid #ededed;
    box-shadow: 0 1px 3px rgb(43 43 43 / 6%);
    position: relative;
    padding: 14px;

    .qrcode {
      text-align: center;
      position: relative;

      img {
        width: 157px;
        height: 157px;
        margin: 16px auto 0;
      }

      .logo {
        width: 42px;
        height: 42px;
        position: absolute;
        top: 44%;
        left: 50%;
        transform: translate(-50%);
        margin: 0;
      }
    }

    .company-info {
      display: flex;
      align-items: center;

      .avatar img {
        width: 25px;
        height: 25px;
        margin-right: 10px;
        border-radius: 3px;
      }

      .name {
        font-size: 12px;
        line-height: 17px;
        color: #000;
      }

      .desc {
        color: #949494;
      }
    }

    .qrcode-tips {
      margin-top: 10px;
      text-align: center;
      font-size: 12px;
      color: rgba(0, 0, 0, .45);
      line-height: 17px;
    }
  }

  .user-info {
    position: absolute;
    z-index: 10;
    top: 10px;
    left: 10px;
    display: flex;
    align-items: center;

    .avatar img {
      width: 25px;
      height: 25px;
      margin-right: 6px;
      border-radius: 3px;
    }

    .name {
      font-size: 12px;
      line-height: 17px;
      color: #000;
    }
  }

  .qr-code img {
    width: 100%;
    height: auto;
    z-index: 10;
    min-height: 60px;
    min-width: 60px;
  }

  .setup {
    padding-left: 35px;

    .row {
      display: flex;
      align-items: center;
      margin-bottom: 16px;
      font-size: 14px;
      line-height: 22px;
      color: rgba(0, 0, 0, .85);

      .switch {
        margin-right: 32px;

        span {
          margin-right: 15px;
        }
      }
    }

    .setup-title {
      margin-bottom: 16px;
      font-size: 14px;
      line-height: 22px;
      color: rgba(0, 0, 0, .85);
    }
  }

  .tips {
    margin-top: 40px;
    background: #fff7f0;
    border: 1px solid #ffdcc9;
    border-radius: 3px;
    font-size: 13px;
    padding: 12px 21px;
    color: #bb5223;
    margin-bottom: 33px;
    line-height: 22px;

    p {
      margin-bottom: 5px;
    }
  }
}

.welcome-box {
  display: flex;
  align-items: center;

  .preview {
    margin-left: 30px;

    .tips {
      text-align: center;
      margin-bottom: 16px;
    }
  }

  .welcome-text {
    width: 760px;

    .label {
      width: 70px;
    }
  }

  .text-1 {
    width: 100%;
    border: 1px solid #eee;
    background: #fbfbfb;
    border-radius: 2px;

    .insert-btn-group {
      width: 100%;
      flex: 1;
      border-bottom: 1px dashed #e9e9e9;
      padding: 6px 15px;
      color: #e8971d;
      cursor: pointer;
    }

    .textarea {
      overflow-y: auto;
      overflow-x: hidden;
      white-space: pre-wrap;
      word-break: break-all;

      textarea {
        width: 100%;
        height: 110px;
        padding: 6px 13px;
        border: none;
        background: #fbfbfb;
        outline: none;
        resize: none;
      }

      .word-count {
        font-size: 13px;
        color: rgba(0, 0, 0, .25);
        margin-left: 10px;
      }
    }
  }

  .text-2 {
    width: 100%;
    border: 1px solid #eee;
    background: #fbfbfb;
    border-radius: 2px;
    padding-left: 20px;
  }

  .link-form {
    margin-top: 16px;

    .item {
      display: flex;
      align-items: center;
      margin-bottom: 14px;

      .ant-input {
        width: 348px;
      }
    }
  }
}

</style>
