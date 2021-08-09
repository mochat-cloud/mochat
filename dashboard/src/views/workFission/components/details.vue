<template>
  <div class="word-fission-details">
    <a-modal v-model="modalShow" on-ok="handleOk" :width="760" :footer="false" centered>
      <template slot="title">活动详情</template>
      <div class="content">
        <div class="left-content">
          <div class="qr-code" ref="qrCode"></div>
          <div class="name">{{ data.active_name }}</div>
          <div class="btn">
            <a-button type="primary" block @click="downQrcode">下载二维码</a-button>
          </div>
          <div class="btn">
            <a-button block @click="goUpdate">修改</a-button>
          </div>
        </div>
        <div class="right-content">
          <div class="block" style="align-items: end">
            <span class="title">链接详情：</span>
            <div class="block-content">
              <div class="flex">
                <span class="ellipses link-text">
                  {{ data.link }}
                </span>
                <span class="copy-text" @click="copyLink">复制</span>
              </div>
              <p class="link-desc">
                <span>
                  提示：因企业微信限制，如果客户修改了微信昵称，可能无法通过邀请链接参与活动
                </span>
              </p>
            </div>
          </div>
          <div class="block">
            <div class="title">
              活动时间：
            </div>
            <div class="block-content">
              <div class="time">
                {{ data.active_time }}
              </div>
            </div>
          </div>
          <div class="block">
            <div class="title">
              使用成员：
            </div>
            <div class="block-content">
              <div class="member flex">
                <div class="item flex" v-for="v in data.service_employees" :key="v.id">
                  <img :src="v.avatar">
                  <span>{{ v.name }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="block" v-if="data.contact_tags">
            <div class="title">
              客户标签：
            </div>
            <div class="block-content">
              <div class="member flex">
                <a-tag v-for="v in data.contact_tags" :key="v.id">
                  {{ v.name }}
                </a-tag>
              </div>
            </div>
          </div>
          <div class="block">
            <div class="title">
              欢迎语：
            </div>
            <div class="block-content">
              <pre class="welcome-text">
                {{ data.welcome_text }}
              </pre>
            </div>
          </div>
          <div class="block">
            <div class="title">
              欢迎语链接：
            </div>
            <div class="block-content">
              <div class="link-card">
                <div class="link-title">
                  {{ data.welcome_title }}
                </div>
                <div class="info flex">
                  <div class="desc">
                    {{ data.welcome_desc }}
                  </div>
                  <div class="img">
                    <img src="../../../assets/default-cover.png">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </a-modal>

    <input type="text" class="copy-input" ref="copyInput">
  </div>
</template>

<script>
import { getDetails } from '@/api/workFission'
import QRCode from 'qrcodejs2'

export default {
  data () {
    return {
      modalShow: false,
      loading: false,
      data: {},
      id: ''
    }
  },
  methods: {
    goUpdate () {
      this.$router.push({
        path: '/workFission/edit',
        query: {
          id: this.id
        }
      })
    },

    getData (id) {
      getDetails({ id: id }).then(res => {
        if (res.data.service_employees) res.data.service_employees = JSON.parse(res.data.service_employees)
        if (res.data.contact_tags) res.data.contact_tags = JSON.parse(res.data.contact_tags)
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
        width: 122,
        height: 122
      })
    },

    show (id) {
      this.getData(id)
      this.id = id
      this.modalShow = true
    },

    hide () {
      this.modalShow = false
    }
  }
}
</script>

<style lang="less" scoped>
.content {
  margin-top: 24px;
  padding-right: 26px;
  height: 500px;
  overflow-x: hidden;
  overflow-y: scroll;
  display: flex;
  justify-content: flex-start;

  .left-content {
    padding-right: 40px;
    width: 180px;

    .qr-code {
      margin-bottom: 20px;
      text-align: center;

      img {
        width: 114px;
        height: 114px;
        display: inline-block;
      }
    }

    .name, .btn {
      text-align: center;
      margin-bottom: 16px;
    }
  }

  .right-content {
    border-left: 1px solid #e8e8e8;

    .block {
      display: flex;
      align-items: center;
      margin-top: 16px;

      .title {
        font-size: 14px;
        min-width: 112px;
        text-align: right;
        color: rgba(0, 0, 0, .45);
      }

      .block-content {
        flex: 1;
      }
    }
  }
}

.block-content {
  .link-text {
    max-width: 350px;
  }

  .copy-text {
    color: #1890ff;
    cursor: pointer;
    word-break: keep-all;
  }

  .link-desc {
    padding: 10px;
    background: #f9f9f9;
    border-radius: 3px;
    margin-top: 7px;

    span, a {
      font-size: 12px;
    }
  }

  .account-box {
    width: 196px;
    height: 56px;
    border: 1px solid #dcdfe6;
    border-radius: 4px;
    padding-left: 13px;
    padding-right: 13px;

    img {
      width: 30px;
      height: 30px;
      border: 1px solid #007aff;
      border-radius: 50%;
      margin-right: 10px;
    }

    .info {
      .name {
        font-size: 13px;
      }

      .type {
        font-size: 12px;
      }
    }
  }

  .time {
    font-size: 14px;
    color: rgba(0, 0, 0, .45);
  }

  .member {
    flex-wrap: wrap;

    .item {
      min-width: 108px;
      max-width: 130px;
      height: 42px;
      background: #f7fbff;
      border-radius: 2px;
      border: 1px solid #b4cbf8;
      padding: 0 12px;
      margin-right: 10px;
      margin-bottom: 3px;

      img {
        width: 25px;
        height: 25px;
        margin-right: 6px;
      }

      span {
        font-size: 14px;
      }
    }
  }

  .group {
    .list {
      margin-top: 8px;
      background: #fbfbfb;
      border-radius: 2px;
      border: 1px solid #eee;
      padding: 16px;

      .item {
        margin-bottom: 10px;

        .info {
          background: #fff;
          border-radius: 2px;
          border: 1px solid #e6e7e8;
          padding: 0 9px;
          margin-right: 8px;
          width: 157px;
          height: 38px;
          align-items: center;

          img {
            width: 20px;
            height: 20px;
            margin-right: 7px;
          }

          .names {
            font-size: 13px;
            color: rgba(0, 0, 0, .85);
          }
        }

        .status {
          background: #f1f2f3;
          border: 1px solid #d0d1d2;
          height: 20px;
          padding: 1px 8px 0;
          border-radius: 2px;
          font-size: 12px;
          color: rgba(0, 0, 0, .65);
        }
      }
    }
  }

  .welcome-text {
    word-break: break-all;
    white-space: break-spaces;
    padding: 16px;
    background: #fbfbfb;
    border: 1px solid #eee;
  }

  .link-card {
    background: #fff;
    border: 1px solid #f0f0f0;
    width: 250px;
    height: 99px;
    padding: 10px 12px;

    .link-title {
      font-size: 13px;
      color: rgba(0, 0, 0, .85);
    }

    .info {
      margin-top: 6px;
      align-items: end;

      .desc {
        font-size: 13px;
        flex: 1;
      }

      img {
        width: 47px;
        height: 47px;
        border-radius: 2px;
        background-color: #f6f6f6;
        margin-left: 4px;
      }
    }
  }
}

.flex {
  display: flex;
  justify-content: flex-start;
  align-items: center;
}

.col {
  flex: 1;
}

.copy-input {
  position: absolute;
  top: 0;
  left: 0;
  opacity: 0;
  z-index: -10;
}

.ellipses {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  word-break: break-all;
  display: block;
}

/deep/ .ant-modal-title {
  text-align: center;
  font-size: 17px;
  font-weight: 600;
  letter-spacing: 1px;
}
</style>
