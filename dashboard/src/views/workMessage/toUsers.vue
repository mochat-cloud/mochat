<template>
  <div class="wrapper">
    <a-row>
      <a-col :span="6">
        <div class="left-wrapper">
          <div class="left">
            <div class="top">
              <div class="avatar-wrapper">
                <img
                  v-if="thumbAvatar"
                  class="avatar"
                  :src="thumbAvatar"
                  :onerror="errorImg"
                  alt="">
                <a-icon v-else type="user" class="icon"/>
              </div>
              <a-select class="avatar-select" v-model="workEmployeeName" @change="selectChange" show-search @search="workEmployeeSearch">
                <a-select-option v-for="(item, index) in workEmployees" :value="item.name" :key="index">
                  {{ item.name }}
                </a-select-option>
              </a-select>
            </div>
            <div class="type-wrapper">
              <span class="type" :class="type == 0 ?'active' : ''" @click="choose(0)">内部</span>
              <span class="type" :class="type == 1 ?'active' : ''" @click="choose(1)">外部</span>
              <span class="type" :class="type == 2 ?'active' : ''" @click="choose(2)">群聊</span>
            </div>
            <div>
              <a-input-search v-model="searchName" @search="getNameList" placeholder="按名称搜索"></a-input-search>
              <ul class="msg-wrapper">
                <li
                  v-for="(item, index) in msgList"
                  :key="index"
                  class="msg-item"
                  @click="changePeople(item, index)"
                  :class="index == msgIndex ? 'active' : ''">
                  <div class="left">
                    <img
                      v-if="item.avatar"
                      :src="item.avatar"
                      :onerror="errorImg"
                      class="img"
                      alt="">
                    <a-icon v-else type="user" class="icon"/>
                  </div>
                  <div class="right">
                    <div class="right-name">
                      <span class="text name">{{ item.name }}</span>
                      <span class="text">{{ item.msgDataTime.slice(5) }}</span>
                    </div>
                    <div class="right-massage">
                      {{ item.content }}
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div class="left-pagination">
          <a-pagination
            v-model="leftCurrent"
            :page-size-options="pageSizeOptions"
            :total="leftTotal"
            show-size-changer
            :page-size="leftPageSize"
            @showSizeChange="onShowSizeChange"
          >
          </a-pagination>
        </div>
      </a-col>
      <a-col :span="18">
        <div class="card">
          <ul class="info-wrapper">
            <li
              v-for="(item, index) in typeList"
              :key="index"
              class="info-item"
              @click="changeType(item.type)"
              :class="item.type == magType ? 'info-active' : ''">
              {{ item.text }}
            </li>
          </ul>
          <div class="message-wrapper">
            <div class="top-search">
              <span class="name">{{ toUserName }}</span>
              <div class="time-wrapper">
                <div class="search-left">
                  <a-input class="search-key" v-model="searchMsgKey" placeholder="请输入搜索内容"></a-input>
                  <a-range-picker
                    class="search-time"
                    v-model="searchTime"
                    @change="dateChange"
                    show-time
                    format="YYYY-MM-DD HH:mm:ss" />
                </div>
                <div class="search-right">
                  <a-button type="primary" class="btn" @click="getMessageList">搜索</a-button>
                  <a-button @click="clearKey" class="btn">清空</a-button>
                </div>
              </div>
            </div>
            <ul class="message-content">
              <li
                class="message-item"
                v-for="(item, index) in messageData"
                :key="index">
                <div class="people-wrapper" :class="item.isCurrentUser == 1 ? 'people-self' : ''">
                  <div class="people-avatar">
                    <img
                      v-if="item.avatar"
                      :src="item.avatar"
                      :onerror="errorImg"
                      class="img"
                      alt="">
                    <a-icon v-else type="user" class="icon"/>
                  </div>
                  <div class="people-info">
                    <div class="name-wrapper" :class="item.isCurrentUser == 1 ? 'self-name-wrapper' : ''">
                      <span class="name">{{ item.name }}</span>
                      <span class="message-time">{{ item.msgDataTime }}</span>
                    </div>
                    <div
                      v-if="item.type == 1 || item.type > 7"
                      :class="[ 'info', item.isCurrentUser == 1 ? 'self-info' : '']">
                      {{ item.content.content }}
                      <div v-if="item.content.item" class="info-item">
                        <div v-for="(inner, innerIndex) in item.content.item" :key="innerIndex">
                          <div>
                            {{ inner.content }}
                          </div>
                        </div>
                      </div>
                    </div>
                    <div v-else :class="[ 'info', 'white', item.isCurrentUser == 1 ? 'self-info' : '']">
                      <a :href="item.content.ossFullPath" target="blank" v-if="item.type == 2">
                        <img
                          :src="item.content.ossFullPath"
                          :onerror="errorImg"
                          class="img"
                          alt="">
                      </a>
                      <audio controls class="audio" v-if="item.type == 4">
                        <source :src="item.content.ossFullPath" type="audio/mpeg">
                        <source :src="item.content.ossFullPath" type="audio/ogg">
                      </audio>
                      <video
                        v-if="item.type == 5"
                        class="video"
                        poster=""
                        controls
                        data-setup="{}"
                        preload="auto">
                        <source :src="item.content.ossFullPath">
                      </video>
                      <div v-if="item.type == 6" class="little">
                        <div class="wrpper">小程序</div>
                        <div class="title-wrapper">
                          <div class="name">{{ item.content.displayname }}</div>
                          <div class="title">{{ item.content.title }}</div>
                          <div class="description">
                            {{ item.content.description }}
                          </div>
                        </div>

                      </div>
                      <a v-if="item.type == 7" :href="item.content.ossFullPath" target="blank">
                        <div class="other">
                          <a-icon type="file" class="file" />
                          <span class="file-name">文件</span>
                        </div>
                      </a>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
        <div class="right-pagination">
          <a-pagination
            v-model="rightCurrent"
            :page-size-options="rightPageSizeOptions"
            :total="rightTotal"
            show-size-changer
            :page-size="rightPageSize"
            @showSizeChange="rightShowSizeChange"
          >
          </a-pagination>
        </div>

      </a-col>
    </a-row>
  </div>
</template>
<script>
import { workEmployee, toUsersList, messageList } from '@/api/workMessage'
import moment from 'moment'
import { mapGetters } from 'vuex'

export default {
  data () {
    return {
      // 所选成员id
      workEmployeeId: '',
      // 成员名称
      workEmployeeName: '',
      // 头像
      thumbAvatar: '',
      // 成员搜索下拉
      workEmployees: [],
      // 左侧聊天对象类型
      type: 0,
      // 名称
      searchName: '',
      // 聊天对象列表
      msgList: [],
      // 所选聊天对象索引
      msgIndex: 0,
      // 文本类型列表
      typeList: [
        {
          type: 0,
          text: '所有'
        }, {
          type: 1,
          text: '文本'
        }, {
          type: 2,
          text: '图片'
        }, {
          type: 3,
          text: '图文'
        }, {
          type: 4,
          text: '音频'
        }, {
          type: 5,
          text: '视频'
        }, {
          type: 6,
          text: '小程序'
        }, {
          type: 7,
          text: '文件'
        }
      ],
      // 所选消息类型
      magType: 0,
      // 群聊id
      roomId: '',
      // 聊天对象id
      toUserId: '',
      toUserName: '',
      // 聊天消息
      messageData: [],
      timer: null,
      searchTime: null,
      searchMsgKey: '',
      dateTimeStart: '',
      dateTimeEnd: '',
      leftCurrent: 1,
      pageSizeOptions: ['20', '30', '50', '100'],
      leftTotal: 0,
      leftPageSize: 20,
      rightCurrent: 1,
      rightPageSizeOptions: ['50', '100', '150', '200'],
      rightTotal: 0,
      rightPageSize: 50,
      errorImg: 'this.src="' + require('@/assets/avatar.png') + '"'
    }
  },
  computed: {
    ...mapGetters(['corpId'])
  },
  created () {
    const time = this.corpId ? 0 : 2000
    setTimeout(() => {
      this.getEmployeeList()
    }, time)
  },
  methods: {
    // 获取企业成员
    async getEmployeeList () {
      try {
        const { data } = await workEmployee({ corpId: this.corpId })
        this.workEmployees = data
        const item = data[0]
        if (item) {
          this.workEmployeeId = item.id
          this.workEmployeeName = item.name
          this.thumbAvatar = item.avatar || ''
          this.getNameList()
        }
      } catch (e) {
        console.log(e)
      }
    },
    // 成员更改
    selectChange (value) {
      const { avatar, id } = this.workEmployees.find(item => {
        return item.name == this.workEmployeeName
      })
      this.thumbAvatar = avatar || ''
      this.workEmployeeId = id || ''
      this.getNameList()
    },
    // 选择聊天对象类型
    choose (type) {
      this.type = type
      this.getNameList()
    },
    // 成员搜索
    async workEmployeeSearch (value) {
      try {
        const { data } = await workEmployee({ corpId: this.corpId, name: value })
        this.workEmployees = data
      } catch (e) {
        console.log(e)
      }
    },
    // 获取聊天对象列表
    async getNameList () {
      if (!this.workEmployeeId) {
        return
      }
      try {
        const params = {
          workEmployeeId: this.workEmployeeId,
          toUsertype: this.type,
          name: this.searchName,
          page: this.leftCurrent,
          perPage: this.leftPageSize
        }
        const { data: { page: { total }, list } } = await toUsersList(params)
        this.msgList = list
        this.leftTotal = total
        if (list[0]) {
          this.changePeople(list[0], 0)
        } else {
          this.messageData = []
          this.rightTotal = 0
        }
      } catch (e) {
        console.log(e)
      }
    },
    // 更换聊天对象
    changePeople (item, index) {
      this.msgIndex = index
      const { toUserId } = item
      // this.roomId = roomId
      this.toUserId = toUserId
      this.getMessageList()
    },
    // 更改消息类型
    changeType (type) {
      this.magType = type
      this.getMessageList()
    },
    // 获取消息
    async getMessageList () {
      if (!this.workEmployeeId || !this.toUserId) {
        return
      }
      const params = {
        corpId: this.corpId,
        workEmployeeId: this.workEmployeeId,
        type: this.magType,
        // roomId: this.roomId,
        toUserId: this.toUserId,
        toUserType: this.type,
        content: this.searchMsgKey,
        dateTimeStart: this.dateTimeStart,
        dateTimeEnd: this.dateTimeEnd,
        page: this.rightCurrent,
        perPage: this.rightPageSize
      }
      try {
        const { data: { page: { total }, list } } = await messageList(params)
        this.messageData = list
        this.rightTotal = total
      } catch (e) {
        console.log(e)
      }
    },
    dateChange (date, dateString) {
      this.dateTimeStart = dateString[0]
      this.dateTimeEnd = dateString[1]
      if (!dateString[0] || !dateString[1]) {
        this.searchTime = null
      } else {
        this.searchTime = [moment(this.dateTimeStart, 'YYYY-MM-DD HH:mm:ss'), moment(this.dateTimeEnd, 'YYYY-MM-DD HH:mm:ss')]
      }
    },
    clearKey () {
      this.searchMsgKey = ''
      this.dateTimeStart = ''
      this.dateTimeEnd = ''
      this.searchTime = null
    },
    onShowSizeChange (current, pageSize) {
      this.leftCurrent = current
      this.leftPageSize = pageSize
      this.getNameList()
    },
    rightShowSizeChange (current, pageSize) {
      this.rightCurrent = current
      this.rightPageSize = pageSize
      this.getMessageList()
    }
  }
}
</script>
<style lang='less' scoped>
.wrapper {
  .top {
    display: flex;
    align-items: center;
    .avatar-wrapper {
      flex: 0 0 45px;
      .avatar {
        width: 45px;
        height: 45px
      }
    }
    .avatar-select {
      flex: 1
    }
  }
  .type-wrapper {
    display: flex;
    justify-content: space-between;
    margin: 10px 0;
    .type {
      font-size: 14px;
      cursor: pointer;
    }
    .active {
      color: #1890ff;
    }
  }
  .left-wrapper {
    padding: 10px;
    background: #fff;
    height: 650px;
    border-left: 1px solid #ececec;
    border-top: 1px solid #ececec;
    border-bottom: 1px solid #ececec;
  }
  .icon {
    font-size: 35px;
  }
  .msg-wrapper {
    padding: 0;
    margin-top: 10px;
    .msg-item {
      display: flex;
      padding: 5px;
      margin-bottom: 10px;
      cursor: pointer;
      .left {
        flex: 0 0 50px;
        .img {
          width: 45px;
          height: 45px;
        }
      }
      .right {
        padding-left: 5px;
        flex: 1;
        .right-name {
          display: flex;
          justify-content: space-between;
          .name {
            font-size: 14px;
            font-weight: bold;
          }
        }
        .right-massage {
        }
      }
    }
    .active {
      background: #1890ff;
      color: #fff;
    }
  }
  .card {
    background: #fff;
    border: 1px solid #ececec;
    height: 650px;
    // overflow-y: auto;
  }
  .info-wrapper {
    padding: 10px;
    margin: 0;
    display: flex;
    height: 55px;
    justify-content: space-between;
    align-items: center;
    background: rgba(250, 250, 250);
    border-bottom: 1px solid #ececec;
    .info-item {
      cursor: pointer;
      flex: 1
    }
    .info-active {
      color: #1890ff;
    }
  }
  .left-pagination {
    margin-top: 10px;
    display: flex;
    justify-content: flex-start;
  }
  .right-pagination {
    margin-top: 10px;
    display: flex;
    justify-content: flex-end;
  }
  .message-wrapper {
    .top-search {
      display: flex;
      height: 55px;
      align-items: center;
      background: rgba(250, 250, 250);
      border-bottom: 1px solid #ececec;
      .name {
        flex: 0 0 200px;
        padding-left: 10px;
        font-size: 14px;
        font-weight: bold;
      }
      .time-wrapper {
        flex: 1;
        display: flex;
        .search-left {
          flex: 1
        }
        .search-right {
          flex: 0 0 200px
        }
        .search-key,.search-time,.btn {
          margin-right: 15px;
        }
        .search-key {
          max-width: 40%;
        }
        .search-time {
          max-width: 50%
        }
      }
    }
    .message-content {
      margin: 0;
      padding: 0;
      max-height: 540px;
      overflow-y: auto;
      .message-item {
        margin: 20px 0 20px 10px;
        .people-self {
          flex-direction: row-reverse;
          margin-right: 10px
        }
        .people-wrapper {
          display: flex;
          .people-avatar {
            flex: 0 0 50px;
            .img {
              width: 45px;
              height: 45px;
            }
          }
          .people-info {
            margin-left: 10px;
            // flex: 1;
            max-width: 50%;
            .name-wrapper {
              .name {
                margin-right: 20px;
              }
            }
            .self-name-wrapper {
              display: flex;
              flex-direction: row-reverse;
              .name {
                margin-left: 20px;
              }
            }
            .info {
              padding: 15px;
              word-break: break-word;
              background: rgba(0,0,0,.1);
              border-radius: 10px;
            }
            .self-info {
              margin-right: 10px;
              background: #1890ff;
              color: #fff;
              justify-content: flex-end;
            }
            .info-item {
              padding: 10px;
            }
            .white {
              background: none;
              display: flex;
              .img {
                width: 200px
              }
              .video {
                width: 50%
              }
              .little {
                width: 80%;
                border: 1px solid rgba(0,0,0,.3);
                border-radius: 10px;
                color: black;
                display: flex;
                .wrpper {
                  width: 60px;
                  height:60px;
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  font-weight: bold;
                  margin: 5px;
                  background: rgba(0,0,0,0.01);
                  border: 1px solid rgba(0,0,0,.3);
                }
                .title-wrapper {
                  flex: 1;
                  .title,.name,.description {
                    word-break: break-word;
                    margin-bottom: 10px;
                    padding: 5px
                  }
                  .title {
                    text-align: center;
                  }
                }
              }
              .other {
                display: flex;
                align-items: center;
                .file {
                  font-size: 40px;
                }
                .file-name {
                  color: black;
                }
              }
            }
          }
        }
      }
    }
  }
}
</style>
