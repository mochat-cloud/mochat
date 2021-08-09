<template>
  <div class="preview">
    <div class="phone">
      <img src="../../assets/phone.png" class="bg">
      <div class="content">
        <div class="item" v-show="text">
          <img
            class="avatar"
            src="../../assets/avatar-default.svg">
          <div class="msg">
            {{ text }}
          </div>
        </div>
        <div class="item" v-show="imageUrl">
          <img
            class="avatar"
            src="../../assets/avatar-default.svg">
          <div class="msg">
            <img :src="imageUrl">
          </div>
        </div>
        <div class="item" v-show="link.title">
          <img
            class="avatar"
            src="../../assets/avatar-default.svg">
          <div class="msg link">
            <div class="title">
              {{ link.title }}
            </div>
            <div class="desc">
              <span>
                {{ link.desc }}
              </span>
              <img
                :src="link.image">
            </div>
          </div>
        </div>
        <div class="item applets" v-show="applets.title && applets.image">
          <img
            class="avatar"
            src="../../assets/avatar-default.svg">
          <div class="msg link">
            <div class="title">
              {{ applets.title }}
            </div>
            <div class="image">
              <img :src="applets.image">
            </div>
            <div class="applets-logo">
              <img src="../../assets/link.jpg">
              小程序
            </div>
          </div>
        </div>
        <slot/>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data () {
    return {
      text: '',
      imageUrl: '',
      link: {
        title: '',
        desc: '',
        image: ''
      },
      applets: {
        title: '',
        image: ''
      }
    }
  },
  mounted () {

  },
  methods: {
    setApplets (title = '', image = '') {
      this.applets.title = title
      this.applets.image = image
    },
    setLink (title = '', desc = '', image = '') {
      this.link.title = title
      this.link.desc = desc
      this.link.image = image
    },
    setText (text = '') {
      this.text = text
    },
    setImage (url = '') {
      this.imageUrl = url
    }
  }
}
</script>

<style lang="less" scoped>
.phone {
  width: 280px;
  height: 500px;
  position: relative;

  .bg {
    width: 274px;
    position: absolute;
    z-index: 1;
  }

  .content {
    width: 235px;
    height: 343px;
    padding: 10px 7px 7px;
    background: transparent;
    position: relative;
    z-index: 2;
    left: 20px;
    top: 98px;
    display: flex;
    flex-direction: column;
    overflow: auto;

    .item {
      display: flex;
      margin-bottom: 15px;

      .avatar {
        width: 26px;
        height: 26px;
        margin-right: 8px;
      }

      .msg {
        max-width: 183px;
        background: #fff;
        border-radius: 2px;
        display: flex;
        align-items: center;
        padding: 7px 11px;
        font-size: 12px;
        word-break: break-all;
        white-space: pre-wrap;
        position: relative;

        img {
          max-width: 128px;
          max-height: 128px;
        }
      }

      .link {
        flex-direction: column;

        .title {
          width: 100%;
          font-size: 12px;
          text-align: left;
          max-height: 38px;
          margin: 0 0 5px;
          overflow: hidden;
        }

        .desc {
          max-height: 54px;
          display: flex;

          span {
            flex: 1;
            overflow: hidden;
            margin-right: 5px;
            word-break: break-all;
            color: rgba(0, 0, 0, .45);
          }

          img {
            width: 40px;
            height: 40px;
          }
        }
      }
    }

    .applets {
      .title {
        font-size: 12px !important;
        font-weight: 500;
        margin-bottom: 5px !important;
      }

      .image img {
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
