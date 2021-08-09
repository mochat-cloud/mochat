<template>
  <div class="details">
    <a-modal
      v-model="modalShow"
      :width="680"
      :footer="false"
      centered
    >
      <template slot="title">
        分享
      </template>
      <div class="mode-one">
        <div style="width: 80px">
          方式一：
        </div>
        <div class="content">
          <p style="color:#000000">
            通过「客户群发」、「客户群群发」、「欢迎语」、「渠道码欢迎语」，选择发送抽奖活动链接发送至客户
          </p>
          <div class="preview-box">
            <span>
              {{ data.info.name }}
            </span>
            <div class="preview-bottom mt16">
              <div>
                {{ data.info.description }}
              </div>
              <img
                src="../../../assets/lottery-default-cover.png"
                style="position: absolute;width: 40px;height: 40px;right: 10px;bottom: 10px">
            </div>
          </div>
        </div>
      </div>
      <div class="mode-two mt30">
        <div style="width: 80px">
          方式二：
        </div>
        <div class="content">
          <p style="color: #000">
            下载抽奖活动二维码或复制链接
          </p>
          <p style="color: #8d8d8d;font-size: 10px">
            可以将二维码或链接放置在海报、宣传单、朋友圈、线下等方式发送给客户
          </p>
          <div class="mode-bottom">
            <div class="qr-code">
              <div ref="qrCode"></div>
              <a class="download" @click="downQrcode">下载二维码</a>
            </div>
            <div class="link-box ml20">
              <div class="link">
                {{ data.link }}
              </div>
              <a class="copy" @click="copyLink">复制链接</a>
            </div>
          </div>
        </div>
      </div>
    </a-modal>

    <input type="text" class="copy-input" ref="copyInput">
  </div>
</template>
<script>
import { share } from '@/api/lottery'
import QRCode from 'qrcodejs2'

export default {
  data () {
    return {
      modalShow: false,
      data: {
        info: {
          name: '',
          description: ''
        }
      }
    }
  },
  methods: {
    getData (id) {
      share({
        id
      }).then(res => {
        this.modalShow = true
        this.data = res.data

        this.initQrcode()
      })
    },

    copyLink () {
      const inputElement = this.$refs.copyInput

      inputElement.value = this.data.link

      inputElement.select()

      document.execCommand('Copy')

      this.$message.success('复制成功')
    },

    downQrcode () {
      const img = this.$refs.qrCode.childNodes[1]

      const a = document.createElement('a')

      const event = new MouseEvent('click')

      a.download = 'qrcode'

      a.href = img.src
      a.dispatchEvent(event)
    },

    initQrcode () {
      this.$refs.qrCode.innerHTML = ''

      // eslint-disable-next-line no-new
      new QRCode(this.$refs.qrCode, {
        text: this.data.link,
        width: 100,
        height: 100
      })
    },

    show (id) {
      this.modalShow = true
      this.getData(id)
    },

    hide () {
      this.modalShow = false
    }
  }
}
</script>

<style lang='less' scoped>
/deep/ .ant-modal-title {
  text-align: center;
  font-size: 17px;
  font-weight: 600;
  letter-spacing: 1px;
}

/deep/ .ant-modal-body {
  height: 577px;
  overflow: auto;
}

.copy-input {
  position: absolute;
  top: 0;
  left: 0;
  opacity: 0;
  z-index: -10;
}

.elastic {
  margin-top: 14px;
  display: flex;
  align-items: center;
}

.shop-address {
  display: flex;
  margin-top: 14px;
}

.maps-bottom {
  width: 540px;
  height: 260px;
  background-color: #8d8d8d;
}

/deep/ .ant-modal-body {
  height: 300px;
}

.preview-box {
  width: 200px;
  height: 90px;
  border: 1px solid #e7e7e7;
  padding: 6px;
  background-color: #fff;
  position: relative;
}

.mode-one,
.mode-two {
  display: flex;
  background-color: #f6f6f6;
  padding: 14px;
}

.preview-bottom {
  display: flex;

  div {
    overflow: auto;
    width: 132px;
    height: 36px;
  }
}

.download {
  display: block;
  font-size: 10px;
  margin-left: 24px;
  margin-top: 6px;
}

.mode-bottom {
  display: flex;
}

.link {
  background-color: #ffffff;
  width: 260px;
  height: 110px;
  padding: 2px 10px;
}

.copy {
  display: block;
  margin-left: 212px;
  margin-top: 6px;
  font-size: 10px;
}

/deep/ .ant-modal-body {
  height: 500px;
}
</style>
