<template>
  <div class="rules-details">
    <a-card>
      <div class="title">
        基本信息
      </div>
      <div class="information">
        <div class="ifm-left">
          <div class="founder">
            <span>创建人：</span>
            <a-tag><a-icon type="user" /> {{ auto_tag.nickname }}</a-tag>
          </div>
          <div class="member mt16">
            <span>生效成员：</span>
            <div class="effectBox">
              <div class="effectContent">
                <a-tag v-for="(item,index) in auto_tag.employees" :key="index"><a-icon type="user" />{{ item.name }}</a-tag>
              </div>
            </div>
          </div>
          <div class="create-time mt16">
            <span>创建时间：</span>
            <span>{{ auto_tag.createdAt }}</span>
          </div>
          <div class="add-tag mt16">
            <span>自动添加标签：</span>
            <a-tag v-for="(item,index) in auto_tag.tags" :key="index">{{ item }}</a-tag>
          </div>
        </div>
        <div class="ifm-right">
          <div class="title-set">
            <span>规则设置：</span>
            <span class="black">共{{ tagRule.length }}条规则</span>
          </div>
          <div class="set-content mt18 ml84" v-for="(item,index) in tagRule" :key="index">
            <span class="title-small black">规则{{ index+1 }}</span>
            <p class="keys ml4" v-for="(obj,inx) in item.tags" :key="inx">
              为<span>每天</span>触发关键词<span>{{ obj.tagid }}</span>次的客户自动打上 <a-tag>{{ obj.tagname }}</a-tag> 标签
            </p>
          </div>
        </div>
      </div>
    </a-card>
    <a-card class="mt16">
      <div class="title">数据统计</div>
      <div class="data-num">
        <div class="data-box mb20">
          <div class="data">
            <div class="item">
              <div class="count">{{ statistics.total_count }}</div>
              <div class="desc">打标签总数</div>
            </div>
            <div class="item">
              <div class="count">{{ statistics.today_count }}</div>
              <div class="desc">今日打标签数</div>
            </div>
          </div>
        </div>
      </div>
    </a-card>
    <a-card class="mt16">
      <div class="search">
        <div class="search-box">
          <div class="customer">
            <span>搜索用户：</span>
            <a-input-search
              placeholder="请输入要搜索的客户"
              v-model="paramsTable.contact_name"
              @search="searchUser"
              allow-clear
              @change="emptyInput"
              style="width: 200px"
            />
          </div>
          <div class="customer-service ml20">
            <span>所属客服：</span>
            <a-input-search placeholder="请输入客服" style="width: 200px"/>
          </div>
          <div class="add-time ml20">
            <span>添加好友时间：</span>
            <a-range-picker style="width: 220px" @change="searchTime" :allowClear="true" v-model="selectDate"/>
          </div>
        </div>
        <div class="reset"><a-button @click="resetTable">重置</a-button>
        </div>
      </div>
      <div class="table-box mt30">
        <div class="store-box mb20">
          <span class="customers-title">
            共{{ table.data.length }}个客户
          </span>
          <a-divider type="vertical" />
          <span style="cursor: pointer;" @click="updateTable"><a-icon type="redo"/>更新数据</span>
        </div>
        <div class="table">
          <a-table :columns="table.col" :data-source="table.data">
            <div slot="employeeName" slot-scope="text">
              <a-tag><a-icon type="user" />{{ text }}</a-tag>
            </div>
            <div slot="keyword" slot-scope="text">
              <a-tag>{{ text }}</a-tag>
            </div>
            <div slot="operate" slot-scope="text,record">
              <div>
                <a @click="chatRow(record)">聊天记录</a>
                <a-divider type="vertical" />
                <a @click="detailsRow">详情</a>
              </div>
            </div>
          </a-table>
        </div>
        <a-modal
          v-model="visible"
          title="聊天记录"
          :maskClosable="false"
          :footer="null"
          width="750px"
        >
          <div class="message-wrapper">
            <!-- 聊天消息 -->
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
        </a-modal>

      </div>
    </a-card>
  </div>
</template>
<script>
import { showApi, showContactKeyWordApi, messageList } from '@/api/autoTag'
export default {
  data () {
    return {
      visible: false,
      messageData: [],
      errorImg: 'this.src="' + require('@/assets/avatar.png') + '"',
      // 选择时间
      selectDate: [],
      // 表格请求数据
      paramsTable: {
        // 搜索客户名称
        contact_name: '',
        // 客服
        employee: '',
        // 开始时间
        start_time: '',
        // 结束时间
        end_time: '',
        page: 1,
        perPage: 15
      },
      // 详情-基本信息数据
      auto_tag: {},
      // 规则
      tagRule: [],
      // 详情-数据统计
      statistics: {},
      table: {
        col: [
          {
            key: 'contactName',
            dataIndex: 'contactName',
            title: '客户',
            scopedSlots: { customRender: 'contactName' }
          },
          {
            key: 'employeeName',
            dataIndex: 'employeeName',
            title: '所属员工',
            scopedSlots: { customRender: 'employeeName' }
          },
          {
            key: 'keyword',
            dataIndex: 'keyword',
            title: '最近触发关键词',
            scopedSlots: { customRender: 'keyword' }
          },
          {
            key: 'createTagTime',
            dataIndex: 'createTagTime',
            title: '添加标签时间'
          },
          {
            key: 'createTime',
            dataIndex: 'createTime',
            title: '添加好友时间'
          },
          {
            key: 'operate',
            dataIndex: 'operate',
            title: '操作',
            scopedSlots: { customRender: 'operate' }
          }
        ],
        data: []
      }
    }
  },
  created () {
    // 获取表格传来的id
    this.idRow = this.$route.query.idRow
    this.paramsTable.id = this.idRow
    this.getDetailsData(this.idRow)
    this.getTableData(this.paramsTable)
  },
  methods: {
    // 重置事件
    resetTable () {
      const resetParams = {
        id: this.idRow,
        page: 1,
        perPage: 15
      }
      this.selectDate = []
      this.paramsTable = resetParams
      this.getTableData(this.paramsTable)
    },
    // 清空输入框
    emptyInput () {
      if (this.paramsTable.contact_name == '') {
        this.getTableData(this.paramsTable)
      }
    },
    // 搜索用户
    searchUser () {
      this.getTableData(this.paramsTable)
    },
    // 聊天记录  record
    chatRow (record) {
      this.visible = true
      messageList({
        toUserType: 0,
        workEmployeeId: record.employeeId,
        toUserId: record.contactId,
        page: 1,
        perPage: 10000
      }).then((res) => {
        this.messageData = res.data.list
      })
    },
    // 详情
    detailsRow () {},
    // 更新数据
    updateTable () {
      this.table.data = []
      this.getTableData(this.paramsTable)
    },
    // 搜索时间
    searchTime (date, dateString) {
      this.paramsTable.start_time = dateString[0]
      this.paramsTable.end_time = dateString[1]
      console.log(this.paramsTable)
      this.getTableData(this.paramsTable)
    },
    // 获取表格数据
    getTableData (data) {
      showContactKeyWordApi(data).then((res) => {
        this.table.data = res.data.list
      })
    },
    // 获取数据详情
    getDetailsData (id) {
      showApi({ id }).then((res) => {
        // 后台基本信息
        this.auto_tag = res.data.auto_tag
        this.tagRule = this.auto_tag.tagRule
        // 后台数据统计
        this.statistics = res.data.statistics
      })
    }
  }

}
</script>

<style lang="less" scoped>
.add-tag span{
  margin-top: 15px;
}
.effectBox{
  width: 200px;
  height: 130px;
  position: relative;
  overflow: hidden;
  border: 1px solid #E9E9E9;
}
.effectContent{
  position: absolute;
  left: 0;
  top: 0;
  right: -17px;
  bottom: 0;
  overflow-x: hidden;
  overflow-y: scroll;
}
.effectContent span{
  margin-left: 6px;
  margin-top: 10px;
}
.title {
  font-size: 15px;
  line-height: 21px;
  color: rgba(0, 0, 0, .85);
  border-bottom: 1px solid #e9ebf3;
  padding-bottom: 16px;
  margin-bottom: 16px;
  position: relative;
}

.information{
  display: flex;

  .ifm-left{
    flex: 0.4;
  }

  .ifm-right{
    flex: 1.6;
    background-color: #f6f6f6;
    border: 1px solid #e7e7e7;
    min-height: 260px;
  }
  .title-set{
    padding-top: 14px;
    padding-left: 12px;
  }
}
.black{
  color: #000;
}
.title-small{
  display: block;
  padding-left: 4px;
  border-left: 3px solid #8d8d8d;
  margin-bottom: 4px;
}
.keys{
  span{
    font-weight: bold;
    margin-left: 1px;
    margin-right: 1px;
  }
}
.data-box {
  display: flex;
  justify-content: center;
  flex-direction: column;
  margin-top: 25px;
  width: 500px;
  height: 125px;

  .data {
    flex: 1;
    height: 120px;
    background: #fbfdff;
    border: 1px solid #daedff;
    display: flex;
    align-items: center;

    .item {
      flex: 1;
      border-right: 1px solid #e9e9e9;

      .count {
        font-size: 24px;
        font-weight: 500;
        text-align: center;
      }

      .desc {
        font-size: 13px;
        text-align: center;
      }

      &:last-child {
        border-right: 0;
      }
    }

    &:last-child {
      margin-right: 0;
    }
  }
}
.search{
  display: flex;

  .search-box{
    display: flex;
    flex: 1;
  }
}
.add-tag{
  margin-top: 0;
}
.message-wrapper {
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
</style>
