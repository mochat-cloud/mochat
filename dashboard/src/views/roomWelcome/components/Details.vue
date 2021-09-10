<template>
  <div class="details">
    <a-modal v-model="modalShow" on-ok="handleOk" :width="535" :footer="false" centered>
      <template slot="title">
        入群欢迎语素材详情
      </template>
      <div class="block">
        <div class="title">
          <span class="bar"/>
          基本设置
        </div>
        <div class="block-content">
          <div class="item">
            <span>类型：</span>
            <div class="item-content">
              {{ data.type }}
            </div>
          </div>
          <div class="item">
            <span>创建人：</span>
            <div class="item-content">
              <a-tag>
                <a-icon type="user" two-tone-color="#7da3d1" :style="{ color: '#7da3d1' }"/>
                {{ data.create_user }}
              </a-tag>
            </div>
          </div>
          <div class="item">
            <span>创建时间：</span>
            <div class="item-content">
              {{ data.create_time }}
            </div>
          </div>
        </div>
      </div>
      <div class="block">
        <div class="title">
          <span class="bar"/>
          欢迎语设置
        </div>
        <div class="msg-content">
          <div class="msg" v-if="data.msg_text">
            <span>消息1：</span>
            {{ data.msg_text }}
          </div>
          <div class="msg" v-if="data.complex_type">
            <span>消息2：</span>
            <img
              v-if="data.complex_type === 'image'"
              :src="data.msg_complex.pic"
              class="image"
            >
            <div class="news" v-if="data.complex_type === 'link'">
              <p class="title">
                {{ data.msg_complex.title }}
              </p>
              <div>
                <span class="desc">{{ data.msg_complex.desc }}</span>
                <img :src="data.msg_complex.pic">
              </div>
            </div>
            <div class="applets" v-if="data.complex_type === 'miniprogram'">
              <div class="title">
                {{ data.msg_complex.title }}
              </div>
              <div class="image">
                <img :src="data.msg_complex.pic">
              </div>
              <div class="applets-logo">
                <img src="../../../assets/link.jpg">
                小程序
              </div>
            </div>
          </div>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script>
export default {
  data () {
    return {
      modalShow: false,
      loading: false,
      data: {}
    }
  },
  methods: {
    show (data) {
      this.data = data
      this.modalShow = true
    },

    hide () {
      this.modalShow = false
    }
  }
}
</script>

<style lang="less" scoped>
/deep/ .ant-modal-title {
  text-align: center;
  font-size: 17px;
  font-weight: 600;
  letter-spacing: 1px;
}

.block {
  .title {
    font-weight: 600;
    display: flex;
    align-items: center;
    color: #333;

    .bar {
      display: block;
      width: 3px;
      height: 12px;
      margin-right: 4px;
      background: #1990ff;
    }
  }

  .block-content {
    margin-top: 15px;
    padding-left: 15px;

    .item {
      display: flex;
      align-items: center;
      margin-bottom: 16px;
    }
  }

  .msg-content {
    width: 100%;
    padding: 12px;
    background: #fbfbfb;
    border-radius: 2px;
    border: 1px solid #eee;
    font-size: 13px;
    font-weight: 400;
    color: #333;
    margin-top: 10px;

    .msg {
      margin-bottom: 15px;
    }

    .image {
      max-width: 200px;
      border-radius: 2px;
    }

    .news {
      width: 194px;
      border: 1px solid #f0f0f0;
      background: #fff;
      font-size: 12px;
      padding: 9px 13px;
      display: flex;
      flex-direction: column;
      margin-bottom: 10px;

      .title {
        font-size: 14px !important;
        color: rgba(0, 0, 0, .85);
        text-align: left;
        max-height: 40px;
        margin: 0 0 5px;
        word-break: break-all;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 500 !important;
      }

      .desc {
        flex: 1;
        width: 0;
        margin-right: 5px;
        color: rgba(0, 0, 0, .45);
        font-size: 13px;
        overflow: hidden;
        text-overflow: ellipsis;
        -webkit-box-orient: vertical;
      }

      img {
        width: 40px;
        height: 40px;
      }
    }

    .applets {
      max-width: 183px;
      background: #fff;
      border-radius: 2px;
      padding: 7px 11px;
      font-size: 12px;

      .title {
        font-size: 12px !important;
        font-weight: 500;
        margin-bottom: 5px !important;
      }

      .image img {
        max-width: 128px;
        max-height: 128px;
        border-radius: 2px;
      }

      .applets-logo {
        width: 100%;
        border-top: 1px solid #E7E7E7;
        margin-top: 9px;
        padding-top: 2px;
        font-size: 11px;
        display: flex;
        align-items: center;

        img {
          width: 17px;
        }
      }
    }
  }
}
</style>
