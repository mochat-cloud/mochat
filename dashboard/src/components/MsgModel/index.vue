<template>
  <div class="model" :style="{ width: width + 'px' }">
    <img @load="imgLoad" class="bgImg" src="@/assets/model_iphone.png" />
    <div class="c" :style="{ width: modelImg.width - 60 + 'px', height: modelImg.height - 200 + 'px' }">
      <div class="dialogue-box">
        <div class="portrait">
          <a-icon type="user" class="user" />
        </div>
        <div class="content">
          {{ addWelcomeData.words }}
        </div>
      </div>
      <div class="dialogue-box" v-for="(item, index) in mediumArray" :key="index">
        <div class="portrait">
          <a-icon type="user" class="user" />
        </div>
        <div class="img-box" v-if="item.type == 2">
          <img :src="item.imageFullPath" alt="" />
        </div>
        <div class="applets-box" v-if="item.type == 6">
          <h4>{{ item.title }}</h4>
          <img :src="item.imageFullPath" alt="" />
        </div>
        <div class="text-box" v-if="item.type == 3">
          <h4>{{ item.title }}</h4>
          <div>
            <span v-if="item.description">{{ item.description }}</span>
            <img :src="item.imageFullPath" alt="" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    width: {
      type: Number,
      default: 210
    }
  },
  data () {
    return {
      modelImg: { width: 0, height: 0 },
      addWelcomeData: {
        words: '您好，请问有什么需要帮助的'
      },
      mediumArray: [
        {
          type: 2,
          imageFullPath: 'https://img2.baidu.com/it/u=3228549874,2173006364&fm=26&fmt=auto&gp=0.jpg'
        },
        {
          type: 6,
          title: '魅力的天空',
          imageFullPath: 'https://img2.baidu.com/it/u=3228549874,2173006364&fm=26&fmt=auto&gp=0.jpg'
        }, {
          type: 3,
          title: 'vue的讲解',
          description: '非常快之外,还有其它一些好处。例如,如果你允许用户在不同的登录方式之间切换',
          imageFullPath: 'https://dss0.baidu.com/6ONWsjip0QIZ8tyhnq/it/u=949383615,3755507436&fm=58'
        }
      ]

    }
  },
  mounted () {
  },
  methods: {
    imgLoad () {
      this.modelImg = document.querySelector('.bgImg').getBoundingClientRect()
      console.log(JSON.stringify(this.modelImg))
    }
  }
}
</script>
<style scoped lang="less">
.model {
  position: relative;
  .bgImg {
    width: 100%;
  }
  .c {
    position: absolute;
    top: 95px;
    left: 30px;
    overflow-y: auto;
    .dialogue-box {
      width: 100%;
      padding: 10px 0px;
      display: flex;
      .portrait {
        padding-right: 10px;
        .user {
          color: #1890ff;
          font-size: 20px;
        }
      }
      .content {
        width: 80%;
        background: #f3f6fb;
        padding: 10px;
        border-radius: 4px 5px 5px 0px;
        word-wrap: break-word;
      }
      .img-box {
        width: 80%;
        img {
          width: 100%;
          height: auto;
          border-radius: 5px;
        }
      }
      .text-box {
        width: 80%;
        height: 135px;
        display: flex;
        flex-direction: column;
        padding: 10px 15px;
        border: 1px solid #eee;
        border-radius: 4px;
        background: #fff;
        h4 {
          width: 100%;
          height: 20px;
          line-height: 20px;
          font-size: 16px;
          margin: 0;
        }
        div {
          margin-top: 5px;
          width: 100%;
          display: flex;
          justify-content: space-between;

          img {
            width: 50px;
            height: 50px;
          }
          span {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 4;
            overflow: hidden;
          }
        }
      }
      .applets-box {
        width: 80%;
        border: 1px solid #eee;
        border-radius: 4px;
        background: #fff;
        padding: 10px;

        h4 {
          width: 100%;
          height: 25px;
          line-height: 25px;
        }
        img {
          width: 100%;
          height: auto;
        }
      }
    }
  }
}
</style>
