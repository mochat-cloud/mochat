<template>
  <div class="box">
    <div class="Join">
      <a-card>
        <div class="content-top">
          <div class="title">
            <span>
              活码设置
            </span>
          </div>
          <div class="join-content">
            <p class="remind">
              <a-alert message="入群人数统计规则：扫描活码即认为客户入群，扫码人数会与实际扫企业微信活码入群人数有偏差" banner/>
            </p>
            <div>
              客户入群需要扫描两个二维码，将先扫活码，再扫企业微信活码，入群过程不会添加企业员工，不会成为企业客户哦～
            </div>
            <div class="group-name elastic mt20">
              <span>
                拉群名称：
              </span>
              <a-input v-model="name" placeholder="名称不会展示给用，用于企业记录渠道名称或使用场景" style="width: 420px;"/>
            </div>
            <div class="code-preview  mt20" style="display: flex">
              <span>
                二维码预览：
              </span>
              <div class="qrcode-avatar">
                <img
                  src="../../assets/wework-qrcode.png"
                  style="width: 118px;height: 118px;">
                <div class="avatar" v-if="avatar">
                  <img :src="avatar">
                </div>
              </div>
              <div class="upload-qc">
                <m-upload
                  v-model="avatar"
                  :def="false"
                  text="上传头像"
                  :preview="false"
                  type="btn"
                />
              </div>
            </div>
          </div>
        </div>
        <div class="content-bottom">
          <div class="title mt60">
            <span>
              企微活码设置
            </span>
          </div>
          <div class="content-box">
            <div class="con-bot-left">
              <div class="name-set">
                <span>
                  群名称设置：
                </span>
                <a-switch size="small" v-model="switchs.title"/>
                <div class="input" v-if="switchs.title">
                  <a-input v-model="title" placeholder="请输入群名称" style="width: 300px"/>
                </div>
              </div>
              <div class="name-set mt18">
                <span>
                  入群引导语：
                </span>
                <a-switch size="small" v-model="switchs.describe"/>
                <div class="input" v-if="switchs.describe">
                  <a-input v-model="describe" placeholder="请输入群引导语" style="width: 300px"/>
                </div>
              </div>
              <div class="upload-avatar mt18">
                <span>
                  上传头像：
                </span>
                <div class="upload-img">
                  <m-upload v-model="logo" :def="false" text="上传头像"/>
                </div>
              </div>
              <div class="tips mt18">
                <a-alert message="请上传在企业微信后台创建的活码" type="info" show-icon style="width: 600px"/>
              </div>
              <div class="code-one mt20" v-for="(v,i) in list" :key="i">
                <span>
                  企微活码{{ i + 1 }}：
                </span>
                <div class="back-box">
                  <div class="back-top flex">
                    <m-upload
                      :def="false"
                      text="上传企微活码"
                      v-model="v.qrcode"
                    />
                    <a class="del-text" @click="delQrcode(i)">删除</a>
                  </div>
                  <div class="back-bottom flex">
                    群活码人数上限：
                    <div>
                      <a-input-number v-model="v.upper_limit" :min="1"/>
                    </div>
                  </div>
                </div>
              </div>
              <div class="code-two mt20 ml78" @click="list.push({upper_limit:1000})">
                <a-button>
                  <a-icon type="plus"/>
                  添加活码
                </a-button>
              </div>
            </div>
            <div class="con-bot-right">
              <div class="phone">
                <img
                  src="../../assets/phone-preview-bg.png"
                  class="bg">
                <div class="info">
                  <div class="avatar" v-if="logo">
                    <img :src="logo">
                  </div>
                  <div class="name">
                    <div v-if="switchs.title">
                      {{ title }}
                    </div>
                    <div v-if="switchs.describe">
                      {{ describe }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="btn mt40 ml80">
          <a-button type="primary" @click="addClick">
            保存
          </a-button>
        </div>
      </a-card>
    </div>
  </div>
</template>

<script>
import { add } from '@/api/roomInfinitePull'

export default {
  data () {
    return {
      name: '',
      avatar: '',
      title: '',
      describe: '',
      logo: '',
      qrCodes: '',
      switchs: {
        title: false,
        describe: false
      },
      list: [{ upper_limit: 1000 }]
    }
  },
  methods: {
    addClick () {
      if (!this.name) {
        this.$message.error('名称未填写')

        return false
      }

      for (const v of this.list) {
        if (!v.qrcode) {
          this.$message.error('活码未上传')

          return false
        }

        if (!v.upper_limit) {
          this.$message.error('人数上限未填写')

          return false
        }
      }

      add({
        name: this.name,
        avatar: this.avatar,
        title_status: this.switchs.title,
        describe_status: this.switchs.describe,
        title: this.title,
        describe: this.describe,
        logo: this.logo,
        qw_code: this.list
      }).then(_ => {
        this.$message.success('创建成功')

        this.$router.push('/roomInfinitePull/index')
      })
    },

    delQrcode (i) {
      if (this.list.length === 1) {
        this.$message.error('不能删除最后一个')

        return false
      }

      this.list.splice(i, 1)
    }
  }
}
</script>

<style lang="less" scoped>
.elastic {
  display: flex;
  align-items: center;
}

.phone {
  width: 280px;
  height: 500px;
  position: relative;

  .bg {
    width: 274px;
    position: absolute;
    z-index: 1;
  }

  .info {
    display: flex;
    align-items: center;
    position: absolute;
    z-index: 10;
    left: 70px;
    top: 138px;

    .avatar img {
      width: 32px;
      height: 32px;
      margin-right: 8px;
    }

    .name div {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      word-break: break-all;
      height: 17px;
      font-size: 12px;
      font-weight: 700;
      color: #000;
    }
  }
}

.title {
  font-size: 15px;
  font-weight: bold;
  line-height: 21px;
  color: rgba(0, 0, 0, .85);
  border-bottom: 1px solid #e9ebf3;
  padding-bottom: 12px;
  margin-bottom: 16px;
  position: relative;
}

.black {
  color: black;
  font-weight: bold;
}

.input {
  margin-top: 10px;
  margin-left: 84px;
}

.upload-avatar {
  display: flex;
}

.upload-img {
  margin-left: 12px;
}

.code-one {
  display: flex;
}

.back-box {
  background-color: #f6f6f6;
  width: 240px;

  .back-top {
    padding: 15px;
    border-bottom: 1px dashed #d0d0d0;
    position: relative;
  }

  .back-bottom {
    padding: 15px;
  }

  .del-text {
    position: absolute;
    right: 13px;
    top: 10px;
  }
}

.content-box {
  display: flex;

  .con-bot-left {
    flex: 1;
  }

  .con-bot-right {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
  }
}

.upload-qc {
  margin-left: 12px;
  margin-top: -6px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.qrcode-avatar {
  position: relative;

  .avatar {
    img {
      width: 35px;
      height: 35px;
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
    }
  }
}
</style>
