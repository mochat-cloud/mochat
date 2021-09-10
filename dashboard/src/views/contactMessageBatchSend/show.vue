<template>
  <div>
    <a-row class="top child_module" :gutter="0" type="flex" justify="space-between" style="padding-bottom: 50px;">
      <a-col :span="16">
        <div class="left">
          <div class="child_title">基本详情</div>
          <div class="user_info">
            <div class="item">
              <span>创建者：</span>
              <div class="user"><a-icon class="icon" type="user" />
                {{ seatInfoData.creator }}</div>
            </div>
            <div class="item"><span>创建时间：</span>{{ seatInfoData.createdAt }}</div>
            <div class="item">
              <span>群发类型：</span>
              <template v-if="seatInfoData.filterParams">筛选客户</template>
              <template v-else>全部客户</template>
            </div>
            <div class="item" style="display: flex;">
              <span>群发消息：</span>
              <div v-for="(item,index) in seatInfoData.content" :key="index" style="width: 570px;">
                <div v-if="item.msgType==='text'">群发消息1：{{ item.content }}</div>
                <div style="margin-top: 10px;" v-if="item.msgType==='image' || item.msgType==='link' || item.msgType==='miniprogram'">群发消息2：</div>
                <div style="margin-left: 15px;margin-top: 10px;">
                  <div v-if="item.msgType==='image'">
                    <img :src="item.pic_url" alt="" style="width: 70px;height: 70px;">
                  </div>
                  <div v-if="item.msgType=='link'">
                    <div>{{ item.url }}</div>
                    <div style="display: flex;">
                      <div>
                        <div>{{ item.title }}</div>
                        <div>{{ item.desc }}</div>
                      </div>
                      <img :src="item.pic_url" alt="" style="width: 70px;height: 70px;">
                    </div>
                  </div>
                  <div v-if="item.msgType=='miniprogram'">
                    <div class="applets">
                      <div class="title">
                        {{ item.title }}
                      </div>
                      <div class="image">
                        <img :src="item.pic_url" style="width: 100px;">
                      </div>
                      <div class="applets-logo">
                        小程序
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="obj">
            <span class="title">群发对象：</span>
            <div class="data">
              <div class="item" v-if="seatInfoData.filterParams">
                <p class="p">在
                  <a-tag v-for="(item,index) in seatInfoData.filterParamsDetail.rooms" :key="index">{{ item.name }}</a-tag>
                  中，满足以下条件中的客户</p>
                <div class="sex">
                  <span>性别：</span>
                  <template v-if="seatInfoData.filterParamsDetail.gender==1">全部性别</template>
                  <template v-if="seatInfoData.filterParamsDetail.gender==2">男</template>
                  <template v-if="seatInfoData.filterParamsDetail.gender==3">女</template>
                  <template v-if="seatInfoData.filterParamsDetail.gender==4">未知性别</template>
                </div>
                <div class="sex" style="margin-top: -20px;">
                  <span>标签：</span>
                  <a-tag v-for="(item,index) in seatInfoData.filterParamsDetail.tags" :key="index">{{ item.name }}</a-tag>
                </div>
              </div>
              <a-tag v-else>全部客户</a-tag>
            </div>
          </div>
        </div>
      </a-col>
      <a-col :span="7" class="msg_module">
        <div class="msg_title">客户收到的消息</div>
        <m-preview ref="preview"/>
        <!--        <msgModel :width="300"></msgModel>-->
      </a-col>
    </a-row>
    <a-row :gutter="0" type="flex" justify="space-between">
      <a-col :span="24">
        <div class="child_module">
          <div class="child_title">数据统计</div>
          <a-row class="total" type="flex" :gutter="20" justify="space-between">
            <a-col class="item" :span="8">
              <a-row class="total_module" type="flex" justify="space-between">
                <a-col class="item item_hr" :span="12">
                  <span style="font-size: 25px;">{{ seatInfoData.sendTotal }}</span><span>人</span>
                  <p>已发送成员</p>
                </a-col>
                <a-col class="item" :span="12">
                  <span style="font-size: 25px;">{{ seatInfoData.receivedTotal }}</span><span>人</span>
                  <p>送达用户</p>
                </a-col>
              </a-row>
            </a-col>
            <a-col class="item" :span="8">
              <a-row class="total_module" type="flex" justify="space-between">
                <a-col class="item item_hr" :span="12">
                  <span style="font-size: 25px;">{{ seatInfoData.notSendTotal }}</span><span>人</span>
                  <p>未发送成员</p>
                </a-col>
                <a-col class="item" :span="12">
                  <span style="font-size: 25px;">{{ seatInfoData.notReceivedTotal }}</span><span>人</span>
                  <p>未送达客户</p>
                </a-col>
              </a-row>
            </a-col>
            <a-col class="item" :span="8">
              <a-row class="total_module" type="flex" justify="space-between">
                <a-col class="item item_hr" :span="12">
                  <span style="font-size: 25px;">{{ seatInfoData.receiveLimitTotal }}</span><span>人</span>
                  <p>客户接受已达上限</p>
                </a-col>
                <a-col class="item" :span="12">
                  <span style="font-size: 25px;">{{ seatInfoData.notFriendTotal }}</span><span>人</span>
                  <p>因不是好友发送失败</p>
                </a-col>
              </a-row>
            </a-col>
          </a-row>
        </div>
      </a-col>
    </a-row>
    <a-row :gutter="7" type="flex" justify="space-between">
      <a-col :span="12">
        <div class="child_module">
          <div class="child_title">成员详情</div>
          <a-tabs @change="tabMemberPanel" v-model="memberAskData.sendStatus">
            <a-tab-pane :key="0" tab="未发送成员"></a-tab-pane>
            <a-tab-pane :key="1" tab="已发送成员"></a-tab-pane>
            <a-tab-pane :key="2" tab="发送失败"></a-tab-pane>
          </a-tabs>
          <div class="search_module">
            <div class="total_num"><a-icon type="user" /> <span class="text">共{{ memberTable.data.length }}人</span></div>
            <a-input-search placeholder="请输入成员名称" @search="searchMember" @change="emptyMember" v-model="memberAskData.keyWords" />
          </div>
          <a-table :columns="memberTable.columns" :data-source="memberTable.data" style="margin-top: 10px;">
            <div slot="name" slot-scope="text, record">
              <div>
                <img :src="record.employeeAvatar" alt="" style="width: 40px;height: 40px;border-radius: 50%;">
                {{ record.employeeName }}
              </div>
            </div>
            <div slot="status" slot-scope="text">
              <a-tag v-if="text==0" color="gray">未发送</a-tag>
              <a-tag v-if="text==1" color="green">已发送</a-tag>
              <a-tag v-if="text==2" color="red">发送失败</a-tag>
            </div>
            <div slot="operation" slot-scope="text,record">
              <a @click="tipSend(record)">提醒</a>
            </div>
          </a-table>
        </div>
      </a-col>
      <a-col :span="12">
        <div class="child_module">
          <div class="child_title">客户详情</div>
          <a-tabs @change="tabClientPanel" v-model="clientAskData.sendStatus">
            <a-tab-pane :key="1" tab="已送达"> </a-tab-pane>
            <a-tab-pane :key="0" tab="未送达客户" force-render> </a-tab-pane>
            <a-tab-pane :key="3" tab="客户接收达上限" force-render> </a-tab-pane>
            <a-tab-pane :key="2" tab="已不是好友客户" force-render> </a-tab-pane>
          </a-tabs>
          <div class="search_module">
            <div class="total_num"><a-icon type="user" /> <span class="text">共{{ clientTable.data.length }}人</span></div>
            <a-input-search placeholder="请输入客户名称" @search="searchCustomer" @change="emptyCustomer" v-model="clientAskData.keyWords" />
          </div>
          <a-table :columns="clientTable.columns" :data-source="clientTable.data" style="margin-top: 10px;">
            <div slot="name" slot-scope="text, record">
              <div>
                <img :src="record.contactAvatar" alt="" style="width: 40px;height: 40px;border-radius: 50%;">
                {{ record.contactName }}
              </div>
            </div>
            <div slot="status" slot-scope="text">
              <a-tag v-if="text==0" color="gray">未发送</a-tag>
              <a-tag v-if="text==1" color="green">已发送</a-tag>
              <a-tag v-if="text==2" color="red">因客户不是好友导致发送失败</a-tag>
              <a-tag v-if="text==3" color="red">因客户已经收到其他群发消息导致发送失败</a-tag>
            </div>
          </a-table>
        </div>
      </a-col>
    </a-row>
  </div>
</template>
<script>
// import msgModel from '@/components/MsgModel'
// eslint-disable-next-line no-unused-vars
import { showApi, employeeSendIndexApi, contactReceiveIndexApi, remindApi } from '@/api/contactMessageBatchSend'
export default {
  components: {
  },
  data () {
    return {
      num: 3,
      seatInfoData: {},
      // 成员列表请求数据
      memberAskData: {
        batchId: '',
        sendStatus: 0,
        keyWords: ''
      },
      memberTable: {
        columns: [
          {
            title: '成员',
            dataIndex: 'name',
            scopedSlots: { customRender: 'name' }
          },
          {
            title: '发送状态',
            dataIndex: 'status',
            scopedSlots: { customRender: 'status' }
          },
          {
            title: '发送数目',
            dataIndex: 'sendContactTotal'
          },
          {
            title: '发送时间',
            dataIndex: 'sendTime'
          },
          {
            title: '操作',
            dataIndex: 'operation',
            scopedSlots: { customRender: 'operation' }
          }
        ],
        data: []
      },
      //  客户列表请求数据
      clientAskData: {
        batchId: '',
        sendStatus: 1,
        keyWords: ''
      },
      clientTable: {
        columns: [
          {
            title: '客户',
            dataIndex: 'name',
            scopedSlots: { customRender: 'name' }
          },
          {
            title: '发送状态',
            dataIndex: 'status',
            scopedSlots: { customRender: 'status' }
          },
          {
            title: '发送时间',
            dataIndex: 'sendTime'
          }
        ],
        data: []
      }
    }
  },
  created () {
    this.batchId = this.$route.query.batchId
    //  获取基础信息
    this.getSeatData(this.batchId)
    this.memberAskData.batchId = this.batchId
    this.clientAskData.batchId = this.batchId
    this.getMemberData()
    this.getClientData()
  },
  methods: {
    // 获取基础信息  showApi
    getSeatData (batchId) {
      showApi({ batchId }).then((res) => {
        console.log(res)
        this.seatInfoData = res.data
        this.previewShow(this.seatInfoData.content)
      })
    },
    // 预览样式
    previewShow (content) {
      console.log(content)
      this.$nextTick(() => {
        content.forEach((value) => {
          if (value.msgType == 'text') {
            this.$refs.preview.setText(value.content)
          }
          if (value.msgType == 'image') {
            this.$refs.preview.setImage(value.pic_url)
          }
          if (value.msgType == 'link') {
            this.$refs.preview.setLink(value.title, value.desc, value.pic_url)
          }
          if (value.msgType == 'miniprogram') {
            this.$refs.preview.setApplets(value.title, value.pic_url)
          }
        })
      })
    },
    // 提醒发送
    tipSend (record) {
      remindApi({
        batchId: this.batchId
      }).then((res) => {
        this.$message.success('提醒成功')
      })
    },
    // 搜索成员
    searchMember () {
      this.getMemberData()
    },
    // 清空成员搜索框
    emptyMember () {
      if (this.memberAskData.keyWords == '') {
        this.getMemberData()
      }
    },
    // 切换客户详情
    tabClientPanel () {
      this.clientAskData.keyWords = ''
      this.getClientData()
    },
    // 搜索客户
    searchCustomer () {
      this.getClientData()
    },
    // 清空客户搜索框
    emptyCustomer () {
      if (this.clientAskData.keyWords == '') {
        this.getClientData()
      }
    },
    // 获取客户详情
    getClientData () {
      contactReceiveIndexApi(this.clientAskData).then((res) => {
        this.clientTable.data = res.data.list
      })
    },
    // 切换成员详情
    tabMemberPanel () {
      this.memberAskData.keyWords = ''
      this.getMemberData()
    },
    // 获取成员详情
    getMemberData () {
      employeeSendIndexApi(this.memberAskData).then((res) => {
        // console.log(res)
        this.memberTable.data = res.data.list
      })
    }
  }
}
</script>

<style lang="less">
.top {
  width: 100%;
}
.child_module {
  background-color: #fff;
  border-radius: 5px;
  padding-bottom: 1px;
  margin-top: 15px;
  .child_title {
    font-size: 16px;
    font-weight: 600;
    padding: 15px;
  }
  .user_info {
    .item {
      padding-left: 15px;
      line-height: 50px;
      span {
        color: #999;
        display: inline-block;
        width: 80px;
        text-align: right;
      }
      .user {
        display: inline-block;
        border: 1px solid #d9d9d9;
        border-radius: 5px;
        padding: 0px 6px;
        line-height: 30px;
        .icon {
          color: #8ba3c9;
          margin-right: 5px;
          font-size: 16px;
        }
      }
    }
    .item:first-child {
      margin-top: 5px;
    }
    .item:last-child {
      margin-bottom: 15px;
    }
  }
  .obj {
    border-top: 1px dashed #f2f2f2;
    display: flex;
    justify-content: flex-start;
    padding: 25px 15px;
    flex-wrap: nowrap;
    .title {
      color: #999;
      width: 80px;
      text-align: right;
      flex-shrink: 0;
    }
    .data {
      display: flex;
      justify-content: flex-start;
      .item {
        background-color: #fbfbfb;
        border: 1px solid #e8e8e8;
        border-radius: 5px;
        .p {
          line-height: 40px;
          padding: 0px 15px;
          margin-bottom: 0px;
          border-bottom: 1px solid #e8e8e8;
        }
        .sex {
          line-height: 60px;
          padding: 0px 15px;
          span {
            color: #999;
          }
        }
      }
    }
  }
  .msg_module {
    padding: 25px 0px ;
    .msg_title {
      text-align: center;
      padding-bottom: 10px;
    }
  }
  .total {
    padding: 0px 15px 15px;
  }
  .total_module {
    background-color: #fbfdff;
    border: 1px solid #daedff;
    position: relative;
    padding: 10px 0px;
    .item {
      padding: 15px 0px;
      text-align: center;
      span {
        font-size: 20px;
        color: black;
        span {
          font-size: 22px;
          font-weight: bold;
          padding-left: 3px;
        }
      }
      p {
        color: #666;
      }
    }
    .item_hr::after {
      position: absolute;
      top: 30%;
      right: 0;
      height: 40%;
      width: 1px;
      content: '';
      background: #e9e9e9;
    }
  }
}
.left {
  margin-right: 8px;
}
.right {
  margin-left: 8px;
}
.tabs_module {
  background: #fff;
  margin-top: 16px;
  border-radius: 5px;
  .ant-tabs-nav-scroll {
    padding-left: 15px;
  }
  .ant-tabs-tab {
    padding: 0px 8px;
    margin-right: 10px;
  }
}
.search_module {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0px 15px;
  .total_num {
    color: black;
    .anticon-user {
      font-size: 16px;
      color: #666;
    }
  }
  .ant-select,
  .ant-input-search {
    width: 200px;
  }
}
.data_list {
  margin: 15px;
  .item {
    border: 1px solid #ffc8c8;
    background-color: #fffcfc;
    margin-bottom: 15px;
    .userinfo {
      display: flex;
      justify-content: flex-start;
      padding: 15px;
      align-items: center;
      img {
        width: 60px;
        border-radius: 50%;
      }
      .info {
        margin-left: 15px;
        .name {
          font-size: 16px;
          color: black;
          span {
            display: inline-block;
            background-color: #ffc2c2;
            font-size: 12px;
            padding: 2px 5px;
            margin-left: 5px;
          }
        }
        .btn {
          display: inline-block;
          background-color: #ffc2c2;
          font-size: 12px;
          padding: 2px 5px;
          margin-top: 5px;
          border-radius: 3px;
        }
        .text {
          margin-top: 5px;
        }
      }
    }
    .text_span {
    }
  }
}
</style>
