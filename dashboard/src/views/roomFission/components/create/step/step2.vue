<template>
  <div class="step2">
    <div class="block">
      <div class="title">
        裂变海报设置
      </div>
      <div class="content">
        <div class="poster-preview">
          <img :src="poster.cover_pic" class="bg" v-if="poster.cover_pic!=''">
          <div class="user-info">
            <div class="avatar" v-if="showStep2News.avatar_show">
              <img
                src="../../../../../assets/mission-create-default-avatar.jpg">
            </div>
            <div v-if="showStep2News.nickname_show" :style="{color:poster.nickname_color}">用户昵称</div>
          </div>
          <div class="qr-code">
            <img src="../../../../../assets/qr-preview.png">
          </div>
        </div>
        <div class="setup">
          <div class="setup-title">
            海报设置：
          </div>
          <div class="row">
            <div class="switch">
              <span>用户头像</span>
              <a-switch size="small" v-model="showStep2News.avatar_show"/>
            </div>
            <div class="switch">
              <span>用户昵称</span>
              <a-switch size="small" v-model="showStep2News.nickname_show"/>
            </div>
          </div>
          <div class="row">
            昵称颜色：<colorPicker v-model="poster.nickname_color" />
          </div>
          <div class="row">
            上传海报：
            <div class="input">
              <m-upload :def="false" text="请上传海报" v-model="poster.cover_pic" ref="coverImg"/>
            </div>
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
    </div>
  </div>
</template>
<script>
export default {
  data () {
    return {
      // 封面图片
      overImg: 0,
      // 页面展示数据
      showStep2News: {
        // 用户头像
        avatar_show: true,
        //  用户昵称
        nickname_show: true
      },
      poster: {
        nickname_color: 'red',
        cover_pic: '',
        qrcode_w: '',
        qrcode_h: '',
        qrcode_x: '',
        qrcode_y: ''
      }
    }
  },
  methods: {
    parentNews (data) {
      // 昵称颜色
      this.poster.nickname_color = data.nicknameColor
      // 海报图片
      this.poster.cover_pic = data.coverPic
      this.$refs.coverImg.setUrl(data.coverPic)
      if (data.avatarShow == 1) {
        this.showStep2News.avatar_show = true
      } else {
        this.showStep2News.avatar_show = false
      }
      if (data.nicknameShow == 1) {
        this.showStep2News.nickname_show = true
      } else {
        this.showStep2News.nickname_show = false
      }
    },
    //  抛出数据
    outputStep2 () {
      if (!this.poster.cover_pic) {
        this.$message.error('请上传海报图片')
        return false
      }
      if (this.showStep2News.avatar_show) {
        this.poster.avatar_show = 1
      } else {
        this.poster.avatar_show = 0
      }
      if (this.showStep2News.nickname_show) {
        this.poster.nickname_show = 1
      } else {
        this.poster.nickname_show = 0
      }
      return this.poster
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

    .bg {
      width: 100%;
      height: 100%;
      position: absolute;
      top: 0;
      left: 0;
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
    max-width: 83px;
    max-height: 83px;
    z-index: 10;
    min-height: 60px;
    min-width: 60px;
    position: absolute;
    bottom: 15px;
    right: 15px;
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
</style>
