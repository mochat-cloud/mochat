<template>
  <div class="word-fission-details">
    <a-modal v-model="modalShow" :width="636" :footer="false" centered>
      <template slot="title">活动详情</template>
      <div class="content">
        <div class="left-content">
          <div class="qr-code" ref="qrCode"></div>
          <div class="name">111111</div>
          <div class="btn">
            <a-button type="primary" block @click="downQrcode">下载二维码</a-button>
          </div>
          <div class="btn">
            <a-button block>修改</a-button>
          </div>
        </div>
        <div class="right-content">
          <div class="block">
            <span class="title">扫码人数：</span>
            <div class="block-content">
              <div class="flex">
                <span class="ellipses link-text">
                  {{ data.total_num }}
                </span>
              </div>
            </div>
          </div>
          <div class="block">
            <span class="title">创建时间：</span>
            <div class="block-content">
              <div class="flex">
                <span class="ellipses link-text">
                  {{ data.created_at }}
                </span>
              </div>
            </div>
          </div>
          <div class="block">
            <span class="title">群活码详情：</span>
          </div>
          <div class="block">
            <div class="ml30">
              <div class="chat-li" v-for="(v,i) in data.qwCode" :key="i">
                <div class="chat-info">
                  <img :src="v.qrcode" height="32" width="32"/>
                  <span class="chat-name ml8">群活码{{ i + 1 }}</span></div>
                <div class="ml8">
                  <a-tag v-if="v.status === 0">未开始</a-tag>
                  <a-tag color="green" v-if="v.status === 1">拉人中</a-tag>
                  <a-tag color="red" v-if="v.status === 2">已停用</a-tag>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script>
import QRCode from 'qrcodejs2'

export default {
  data () {
    return {
      modalShow: false,
      loading: false,
      data: {}
    }
  },
  methods: {
    downQrcode () {
      const img = this.$refs.qrCode.childNodes[1]

      const a = document.createElement('a')

      const event = new MouseEvent('click')

      a.download = 'qrcode'

      a.href = img.src
      a.dispatchEvent(event)
    },

    initQrcode () {
      setTimeout(() => {
        this.$refs.qrCode.innerHTML = ''

        // eslint-disable-next-line no-new
        new QRCode(this.$refs.qrCode, {
          text: this.data.link,
          width: 122,
          height: 122
        })
      }, 100)
    },

    show (data) {
      this.data = data

      this.modalShow = true

      this.initQrcode()
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
  height: 371px;
  display: flex;
  justify-content: flex-start;

  .left-content {
    padding-right: 40px;
    width: 180px;

    .qr-code {
      margin-left: 10px;
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

.chat-li {
  margin-bottom: 8px;
  display: -webkit-box;
  display: flex;
  -webkit-box-align: center;
  align-items: center;
  box-sizing: border-box;
}

.chat-li .chat-status.green {
  background: #e8fcdc;
  border: 1px solid #a1ec9f;
}

.chat-li .chat-status {
  width: 58px;
  height: 22px;
  font-size: 12px;
  line-height: 17px;
  color: rgba(0, 0, 0, .65);
  text-align: center;
  border-radius: 2px;
  margin-left: 16px;
  word-break: keep-all;
  white-space: nowrap;
}

.chat-li .chat-info {
  width: 160px;
  height: 38px;
  padding: 12px 13px;
  background: #fff;
  border: 1px solid #e6e6e6;
  box-sizing: border-box;
  border-radius: 2px;
  display: -webkit-box;
  display: flex;
  -webkit-box-align: center;
  align-items: center;
}

/deep/ .ant-modal-title {
  text-align: center;
  font-size: 17px;
  font-weight: 600;
  letter-spacing: 1px;
}
</style>
