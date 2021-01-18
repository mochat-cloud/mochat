<template>
  <div class="medium">
    <div class="search-wrapper">
      <van-search class="search" v-model="searchStr" @search="onSearch" placeholder="请输入搜索关键词" />
      <van-button class="btn" type="primary" @click="onSearch">搜索</van-button>
    </div>
    <div class="wrapper">
      <div class="group-wrapper">
        <van-dropdown-menu active-color="#1989fa" class="dropdown-menu">
          <van-dropdown-item
            v-model="mediumGroupId"
            :options="groupOptions"
            @change="groupChange" />
        </van-dropdown-menu>
        <img class="img" src="@/assets/check.png" @click="check">
      </div>
      <van-tabs
        v-model:active="type"
        color="#4477bc"
        swipeable
        title-active-color="#000000"
        title-inactive-color="#999999"
        @change="typeChange">
        <van-tab title="文本" :name="1">
        </van-tab>
        <van-tab title="图文" :name="3">
        </van-tab>
        <van-tab title="文件" :name="7">
        </van-tab>
        <van-tab title="图片" :name="2">
        </van-tab>
        <van-tab title="视频" :name="5">
        </van-tab>
      </van-tabs>
      <van-list
        v-model:loading="loading"
        :finished="finished"
        v-model:error="error"
        error-text="点击加载更多"
        finished-text="没有更多了"
        @load="getDetail"
      >
        <!-- 文本 -->
        <div v-if="type == 1">
          <div class="list-item" v-for="(item, index) in list" :key="index">
            <van-checkbox
              class="checkbox"
              v-model="item.checked"
              @change="checkChange"
              v-if="checkText"></van-checkbox>
            <div class="content">
              <div class="top text">
                {{ item.content.title }}
              </div>
              <div class="bottom text">
                {{ item.content.content }}
              </div>
            </div>
          </div>
        </div>
        <!-- 图片 -->
        <div v-if="type == 2">
          <van-grid :column-num="4">
            <van-grid-item v-for="(item, index) in list" :key="index" class="picture-item" >
              <div class="img-wrapper">
                <van-image
                  :src="item.content.imageFullPath"
                  @click="preview(2, list, index)"
                  class="img" />
                <van-checkbox
                  class="checkbox"
                  v-model="item.checked"
                  @change="checkChange"
                  v-if="checkText"></van-checkbox>
              </div>

            </van-grid-item>
          </van-grid>
        </div>
        <!-- 图文 -->
        <div v-if="type == 3">
          <div class="list-item picture-text" v-for="(item, index) in list" :key="index">
            <van-checkbox
              class="checkbox"
              v-model="item.checked"
              @change="checkChange"
              v-if="checkText"></van-checkbox>
            <div class="content">
              <img
                @click="preview(1, item.content.imageFullPath)"
                :src="item.content.imageFullPath"
                class="img"
                alt="">
              <div class="text-wrapper">
                <div class="top text">{{ item.content.title }}</div>
                <div class="bottom text">{{ item.content.description }}</div>
              </div>
            </div>
          </div>
        </div>
        <!-- 文件 -->
        <div v-if="type == 7">
          <div class="list-item file-item" v-for="(item, index) in list" :key="index">
            <van-checkbox
              class="checkbox"
              v-model="item.checked"
              @change="checkChange"
              v-if="checkText"></van-checkbox>
            <div class="content">
              <img src="@/assets/word.png" class="img" v-if="checkFileType(item.content.fileName) == 0" alt="">
              <img src="@/assets/excel.png" class="img" v-if="checkFileType(item.content.fileName) == 1" alt="">
              <img src="@/assets/zip.png" class="img" v-if="checkFileType(item.content.fileName) == 2" alt="">
              <img src="@/assets/pdf.png" class="img" v-if="checkFileType(item.content.fileName) == 3" alt="">
              <img src="@/assets/file.png" class="img" v-if="checkFileType(item.content.fileName) == 4" alt="">
              <div class="title">
                {{ item.content.fileName }}
              </div>
            </div>
          </div>
        </div>
        <!-- 视频 -->
        <div v-if="type == 5">
          <div class="list-item video-wrapper" v-for="(item, index) in list" :key="index">
            <van-checkbox
              class="checkbox"
              v-model="item.checked"
              @change="checkChange"
              v-if="checkText"></van-checkbox>
            <div class="content">
              <video
                id="video"
                class="video"
                poster=""
                data-setup="{}"
                @click="playVideo(item.content.videoFullPath)"
                preload="metadata">
                <source :src="item.content.videoFullPath">
              </video>
              <div class="title">
                {{ item.content.videoName }}
              </div>
            </div>
          </div>
        </div>
      </van-list>
    </div>
    <div v-if="checkText" class="footer">
      <div class="check-all">
        <van-checkbox icon-size="20px" class="checkbox" v-model="allChecked" @click="allCheckChange">全选</van-checkbox>
      </div>
      <van-button type="primary" size="large" @click="transmit">发送</van-button>
    </div>
    <van-image-preview
      closeable
      v-model:show="imgShow"
      :images="images"
      @click="imgShow=false"
      @change="imgChange"
      :start-position="start"
    >
    </van-image-preview>
    <van-overlay :show="videoShow" @click="videoShow = false" :z-index="10000">
      <span class="close" @click="videoShow = false">
        <van-icon size="25px" name="cross" />
      </span>
      <div class="video-preview">
        <div @click.stop class="content">
          <video
            v-if="videoShow"
            id="video"
            ref="video"
            class="video"
            poster=""
            autoplay="autoplay"
            data-setup="{}"
            controls
            preload="">
            <source :src="videoSrc">
          </video>
        </div>
      </div>

    </van-overlay>
  </div>
</template>

<script>
import { mediumGroup, mediumDetail } from '@/api/medium'
import { wxConfig, agentConfig, sendChatMessage } from '@/utils/wxCodeAuth'
import { getCookie } from '@/utils'

export default {
  data () {
    return {
      searchStr: '',
      type: 1,
      mediumGroupId: 0,
      groupOptions: [],
      loading: false,
      finished: true,
      allChecked: false,
      checkText: false,
      list: [],
      fileList: [],
      error: false,
      requestTime: 1,
      imgShow: false,
      images: [],
      start: 0,
      videoShow: false,
      videoSrc: ''
    }
  },
  created () {
    this.getAllGroup()
    this.getDetail()
    this.config()
  },
  methods: {
    config () {
      const uriPath = this.$route.fullPath
      const corpId = getCookie('corpId')
      const agentId = getCookie('agentId')
      wxConfig(corpId, uriPath)
      agentConfig(corpId, uriPath, agentId)
    },
    checkFileType (fileName) {
      const reg = /\.[a-zA-Z]+/
      let type = reg.exec(fileName)
      type = type?.[0]?.toLowerCase()?.slice(1) || ''
      if (type == 'doc' || type == 'docx') {
        return 0
      } else if (type == 'xls' || type == 'xlsx') {
        return 1
      } else if (type == 'zip') {
        return 2
      } else if (type == 'pdf') {
        return 3
      } else {
        return 4
      }
    },
    getAllGroup () {
      mediumGroup().then(res => {
        this.groupOptions = res.data.map(item => {
          return {
            text: item.name,
            value: item.id
          }
        })
      })
    },
    getDetail () {
      const params = {
        mediumGroupId: this.mediumGroupId,
        searchStr: this.searchStr,
        type: this.type,
        page: this.requestTime,
        perPage: 10
      }
      this.loading = true
      mediumDetail(params).then(res => {
        this.requestTime = this.requestTime + 1
        const { page: { total } } = res.data
        const list = res.data.list.map(item => {
          return {
            ...item,
            checked: false
          }
        })
        if (this.requestTime == 2) {
          this.list = list
        } else {
          this.list = this.list.concat(list)
        }
        if (this.list.length == total || list.length < 10) {
          this.finished = true
        } else {
          this.error = true
          this.finished = false
        }
        this.loading = false
      }).catch(e => {
        console.log(e)
        this.loading = false
      })
    },
    groupChange (value) {
      this.typeChange()
    },
    typeChange (type) {
      this.requestTime = 1
      this.checkText = false
      this.allChecked = false
      this.list = []
      this.getDetail()
    },
    preview (type, img, index) {
      this.imgShow = true
      if (type == 1) {
        this.images = [
          img
        ]
        this.start = 0
      } else {
        this.images = img.map(item => {
          return item.content.imageFullPath
        })
        this.start = index
      }
    },
    imgChange (index) {
      this.start = index
    },
    onSearch () {
      this.typeChange()
    },
    onLoad () {

    },
    check () {
      this.checkText = !this.checkText
    },
    checkChange () {
      const allChecked = this.list.every(item => {
        return item.checked
      })
      this.allChecked = allChecked
    },
    allCheckChange () {
      this.list.map(item => {
        item.checked = this.allChecked
      })
    },
    playVideo (src) {
      this.videoShow = true
      this.videoSrc = src
    },
    transmit () {
      const ary = this.list.filter(item => {
        return item.checked
      })
      ary.forEach(item => {
        if (this.type == 1) {
          sendChatMessage(this.type, item.content?.content || '')
        } else if (this.type == 3) {
          sendChatMessage(3, {
            link: '#.com',
            title: item.content.title,
            desc: item.content.description,
            imgUrl: item.content.imageFullPath
          })
          // 图片
          // sendChatMessage(2, item.content)
        } else {
          sendChatMessage(this.type, item.mediaId)
        }
      })
      // 1 文本 2 图片 3 图文 5 视频 7文件
    }
  }
}

</script>

<style lang="less" scoped>
.wrapper {
  background: #fff;
  padding: 0 30px;
  .group-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    .dropdown-menu {
      width: 180px;
      margin-left: -25px;
    }
    .img {
      width: 34px;
      height: 31px
    }
  }
  .list-item {
    padding: 23px 0;
    display: flex;
    align-items: center;
    .checkbox {
      flex: 0 0 50px;
      height: 50px
    }
    .content {
      flex: 1;
      padding-left: 22px;
      .top {
        font-size: 30px;
        font-weight: 500;
        color: #333333;
        line-height: 42px;
        padding-bottom: 9px;
      }
      .bottom {
        font-size: 28px;
        font-weight: 400;
        color: #999999;
        line-height: 40px;
      }
      .text {
        width: 580px;
        overflow: hidden;
        text-overflow:ellipsis;
        white-space: nowrap;
      }
    }
  }
  .picture-text {
    .content {
      display: flex;
      .img {
        flex: 0 0 94px;
        width: 94px;
        height: 94px;
      }
      .text-wrapper {
        flex: 1;
        padding-left: 25px;
        .text {
          width: 415px;
          overflow: hidden;
          text-overflow:ellipsis;
          white-space: nowrap;
        }
      }
    }

  }
  .file-item {
    .content {
      display: flex;
      align-items: center;
      .img {
        flex: 0 0 80px;
        width: 80px;
        height: 94px;
      }
      .title {
        padding-left: 30px;
        flex: 1;
        font-size: 30px;
        font-weight: 500;
        color: #333333;
        line-height: 42px;
      }
    }
  }
  .video-wrapper {
    .content {
      display: flex;
      align-items: center;
      .video {
        flex: 0 0 214px;
        width: 214px;
        height: 128px;
        object-fit:fill;
      }
      .title {
        padding-left: 24px;
        flex: 1;
        font-size: 30px;
        font-weight: 500;
        color: #333333;
        line-height: 42px;
      }
    }
  }
  .picture-item {
    padding: 2px;
    .img-wrapper {
      position: relative;
      max-height: 182px;
      width: 100%;
      height: 100%;
    }
    .img {
      width: 100%;
      height: 100%;
    }
    .checkbox {
      position: absolute;
      top: 0;
      right: 0;
      width: 40px;
      height: 40px;
    }
  }
}
.search-wrapper {
  height: 68px;
  margin: 22px 10px;
  display: flex;
  .search {
    padding: 0;
    flex: 1;
  }
  .btn {
    flex: 0 0 150px;
    height: 68px;
    margin-left: 10px;
  }
}
.footer {
  position: fixed;
  width: 100%;
  bottom: 0;
  .check-all {
    background: #fff;
    height: 70px;
    display: flex;
    align-items: center;
    padding-left: 30px;
    .checkbox {
      font-size: 28px;
      font-weight: 400;
      color: #666666;
      line-height: 40px;
    }
  }
}
.video-preview {
  display: flex;
  align-items: center;
  justify-content: center;
  .content {
    margin: 70px 0 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }
  .video {
    width: -webkit-fill-available;
    height: -webkit-fill-available;
    width: 100%
  }
}
  .close {
    color: #fff;
    position: absolute;
    top: 0;
    left: 10px;
  }
</style>
