<template>
  <div class="details">
    <a-modal
      v-model="modalShow"
      on-ok="handleOk"
      :width="760"
      :footer="false"
      centered
      :maskClosable="false">
      <template slot="title">活动详情</template>
      <div class="content">
        <div class="left-content">
          <div class="qr-code" ref="qrCode"></div>
          <div class="name">{{ fission.activeName }}</div>
          <div class="btn">
            <a-button type="primary" block @click="downQrcode">下载二维码</a-button>
          </div>
          <div class="btn">
            <a-button block @click="$router.push('/roomFission/update?id='+id)">修改</a-button>
          </div>
        </div>
        <div class="right-content">
          <div class="block" style="align-items: end">
            <span class="title">链接详情：</span>
            <div class="block-content">
              <div class="flex">
                <span class="ellipses link-text">{{ linkgroup }}</span>
                <span class="copy-text" @click="copyLink">复制</span>
              </div>
              <p class="link-desc">
                <span>
                  提示：活动链接自动填入群欢迎素材库，需员工在侧边栏手动选择入群欢迎语，
                </span>
              </p>
            </div>
          </div>
          <div class="block">
            <div class="title">活动时间：</div>
            <div class="block-content">
              <div class="time">
                {{ fission.createdAt }}～{{ fission.endTime }}
              </div>
            </div>
          </div>
          <!--          领奖方式-->
          <div class="block">
            <div class="title">领奖方式：</div>
            <div>联系客服</div>
          </div>
          <!--          客服成员-->
          <div class="block">
            <div class="title">客服成员：</div>
            <div class="block-content">
              <div class="member flex">
                <div class="item flex" v-for="(item,index) in receiveEmployees" :key="index">
                  <img :src="item.avatar">
                  <span>{{ item.name }}</span>
                </div>
              </div>
            </div>
          </div>
          <!--          使用成员-->
          <div class="block">
            <div class="title">使用成员：</div>
            <div v-if="inviteEmployees.length==0">暂不邀请</div>
            <div class="block-content" v-else>
              <div class="member flex">
                <div class="item flex" v-for="(item,index) in inviteEmployees" :key="index">
                  <img :src="item.avatar">
                  <span>{{ item.name }}</span>
                </div>
              </div>
            </div>
          </div>
          <!--          群聊详情-->
          <div class="block">
            <div class="title">群聊详情：</div>
            <div class="block-content">
              <div class="group">
                <div class="count">
                  共{{ rooms.length }}个
                </div>
                <div class="list">
                  <div class="item" v-for="(item,index) in rooms" :key="index">
                    <div class="group flex">
                      <div class="info flex">
                        <img :src="item.roomQrcode">
                        <span class="names">{{ item.room.name }}</span>
                      </div>
                      <div class="status">未开始</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="block">
            <div class="title">
              入群欢迎语1：
            </div>
            <div class="block-content">
              <pre class="welcome-text">{{ welcome.text }}</pre>
            </div>
          </div>
          <div class="block">
            <div class="title">
              入群欢迎语2：
            </div>
            <div class="block-content">
              <div class="link-card">
                <div class="link-title">{{ welcome.linkTitle }}</div>
                <div class="info flex">
                  <div class="desc">{{ welcome.linkDesc }}</div>
                  <div class="img">
                    <img :src="welcome.linkPic">
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
// eslint-disable-next-line no-unused-vars
import { infoApi, department } from '@/api/roomFission'
// eslint-disable-next-line no-unused-vars
import QRCode from 'qrcodejs2'
export default {
  data () {
    return {
      modalShow: false,
      loading: false,
      //  基本活动信息
      fission: {},
      //  设置海报活动
      poster: {},
      // 欢迎语
      welcome: {},
      // 群聊
      rooms: [],
      // 邀请客户
      invite: {},
      //  客服数据
      serviceData: [],
      //  展示客服数据
      receiveEmployees: [],
      inviteEmployees: [],
      // 群链接
      linkgroup: '',
      id: ''
    }
  },
  created () {
  },
  methods: {
    show (id) {
      this.id = id

      this.modalShow = true
      infoApi({ id }).then((res) => {
        console.log(res)
        this.fission = res.data.fission
        this.poster = res.data.poster
        this.welcome = res.data.welcome
        this.rooms = res.data.rooms
        this.invite = res.data.invite
        this.linkgroup = res.data.link
        this.getServiceNews()
        this.initQrcode()
      })
    },
    // 处理客服数据
    handServerData () {
      this.receiveEmployees = []
      this.serviceData.forEach((item) => {
        const empyIndex = this.fission.receiveEmployees.indexOf(item.id)
        if (empyIndex != -1) {
          this.receiveEmployees.push(item)
        }
      })
    },
    // 处理员工数据  inviteEmployees
    handUseMember () {
      this.inviteEmployees = []
      this.serviceData.forEach((item) => {
        const empyIndex = this.fission.receiveEmployees.indexOf(item.id)
        if (empyIndex != -1) {
          this.inviteEmployees.push(item)
        }
      })
    },
    initQrcode () {
      this.$refs.qrCode.innerHTML = ''
      // eslint-disable-next-line no-new
      new QRCode(this.$refs.qrCode, {
        text: this.linkgroup,
        width: 122,
        height: 122
      })
    },
    // 下载二维码
    downQrcode () {
      const img = this.$refs.qrCode.childNodes[1]
      const a = document.createElement('a')
      const event = new MouseEvent('click')
      a.download = 'qrcode'
      a.href = img.src
      a.dispatchEvent(event)
    },
    copyLink () {
      const inputElement = this.$refs.copyInput
      inputElement.value = this.linkgroup
      inputElement.select()
      document.execCommand('Copy')
      this.$message.success('复制成功')
    },
    //  获取客服成员数据
    getServiceNews () {
      department().then((res) => {
        this.serviceData = res.data.employee
        //  处理客服数据
        this.handServerData()
        this.handUseMember()
      })
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
.copy-input{
  position: absolute;
  top: 0;
  left: 0;
  opacity: 0;
  z-index: -10;
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
            font-size: 12px;
            color: rgba(0, 0, 0, .85);
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
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
