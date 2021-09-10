<template>
  <div>
    <a-row class="top" :gutter="0" type="flex" justify="space-between">
      <a-col :span="12">
        <div class="child_module left" style="padding-bottom: 20px;">
          <div class="child_title">基本详情</div>
          <div class="user_info">
            <div class="item">
              <span>创建者：</span>
              <div class="user"><a-icon class="icon" type="user" />{{ info.creator }}</div>
            </div>
            <div class="item"><span>创建时间：</span>{{ info.createdAt }}</div>
            <div class="item" style="flex-wrap: wrap;">
              <span>群发对象：</span>
              <div class="obj" v-for="(item,index) in info.seedRooms" :key="index">{{ item.name }}</div>
              ;等{{ info.seedRooms.length !== 'undefined' ? info.seedRooms.length : 0 }}个群聊
            </div>
            <div v-for="(item,index) in info.content" :key="index">
              <div class="item" v-if="item.msgType==='text'"><span>群发消息1：</span>{{ item.content }}</div>
              <div class="item" v-if="item.msgType==='image' || item.msgType==='link' || item.msgType==='miniprogram'"><span>群发消息2：</span></div>
              <div style="margin-left: 75px;margin-top: 10px;">
                <div v-if="item.msgType=='image'">
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
                      <img :src="item.pic_url">
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
      </a-col>
      <a-col :span="12" class="col_right">
        <div class="child_module right">
          <div class="child_title">数据详情</div>
          <a-row class="total_module" type="flex" justify="space-between">
            <a-col class="item item_hr" :span="12">
              <span>{{ info.sendTotal }}</span>
              <p>已发送群主</p>
            </a-col>
            <a-col class="item" :span="12">
              <span>{{ info.notSendTotal }}</span>
              <p>未发送群主</p>
            </a-col>
            <a-col class="item item_hr" :span="12">
              <span>{{ info.receivedTotal }}</span>
              <p>已送达群聊</p>
            </a-col>
            <a-col class="item" :span="12">
              <span>{{ info.notReceivedTotal }}</span>
              <p>未送达群聊</p>
            </a-col>
          </a-row>
        </div>
      </a-col>
    </a-row>
    <!--    表格-->
    <a-card style="margin-top: 20px;">
      <a-tabs @change="tabTable" v-model="tableIndex">
        <a-tab-pane :key="1" tab="客户群接受详情"></a-tab-pane>
        <a-tab-pane :key="2" tab="群主发送详情"></a-tab-pane>
      </a-tabs>
      <div>
        <div style="font-weight: bold;">
          共{{ table.data.length }}个<template v-if="tableIndex==1">群聊</template><template v-else>群主</template>
        </div>
      </div>
      <div style="display: flex;margin-top: 20px;">
        <div class="screen_box" v-if="tableIndex==1">
          搜索群聊：
          <a-input
            placeholder="请输入要搜索的群聊"
            style="width: 200px;"
            v-model="tableAskData.keyWords"
          />
        </div>
        <div class="screen_box">
          群主：
          <a-select
            style="width: 200px"
            placeholder="选择管理员"
            v-model="tableAskData.employeeIds"
          >
            <a-select-option v-for="(item,index) in employeeData" :key="index" :value="item.employeeId">
              {{ item.name }}
            </a-select-option>
          </a-select>
        </div>
        <div class="screen_box">
          送达状态：
          <a-select
            placeholder="请选择发送状态"
            style="width: 200px"
            v-model="tableAskData.sendStatus"
          >
            <a-select-option :value="0">未发送</a-select-option>
            <a-select-option :value="1">已发送</a-select-option>
            <a-select-option :value="2">发送失败</a-select-option>
          </a-select>
        </div>
        <a-button style="margin-left: 20px;" @click="resetBtn">重置</a-button>
        <a-button style="margin-left: 20px;" @click="searchTableData" type="primary">搜索</a-button>
      </div>
      <div style="margin-top: 30px;">
        <a-table
          :columns="tableIndex == 1?table.groupColumns:table.sendColumns"
          :data-source="table.data"
        >
          <div slot="employeeName" slot-scope="text">
            <a-tag><a-icon type="user" />{{ text }}</a-tag>
          </div>
          <div slot="status" slot-scope="text">
            <a-tag v-if="text==0" color="gray">未发送</a-tag>
            <a-tag v-if="text==1" color="green">已发送</a-tag>
            <a-tag v-if="text==2" color="red">因客户不是好友导致发送失败</a-tag>
            <a-tag v-if="text==3" color="red">因客户已经收到其他群发消息导致发送失败</a-tag>
          </div>
          <div slot="operation" slot-scope="text,record">
            <a @click="detailsRow(record)">群详情</a>
          </div>
          <div slot="operate" slot-scope="text,record">
            <a @click="remindBtn(record)" v-if="record.status===0">提醒发送</a>
            <a-divider type="vertical" />
            <a @click="groupSendDetails(record)">群发详情</a>
          </div>
        </a-table>
      </div>
    </a-card>
    <a-modal
      v-model="detailsPopup"
      title="群发详情"
      :footer="null"
      :maskClosable="false"
      width="750px">
      <div style="padding-bottom: 10px;">
        <div class="details_top_data">
          <div style="text-align: center;font-size: 15px;">
            <div style="font-size: 22px;font-weight: bold;">{{ groupTotal }}</div>
            <div>本次推送群群聊</div>
          </div>
          <div style="text-align: center;font-size: 15px;">
            <div style="font-size: 22px;font-weight: bold;">{{ groupSendNum }}</div>
            <div>已发送群聊</div>
          </div>
          <div style="text-align: center;font-size: 15px;">
            <div style="font-size: 22px;font-weight: bold;">{{ groupTotal-groupSendNum }}</div>
            <div>未发送群聊</div>
          </div>
        </div>
        <div style="display: flex;justify-content:space-between;margin-top: 20px;font-size: 15px;">
          <div class="row_name">全部({{ sendData.length }})</div>
          <div>
            送达状态：
            <a-select
              placeholder="请选择送达状态"
              style="width: 200px"
              v-model="sendStatus"
              @change="selectStatus"
            >
              <a-select-option :value="4">全部</a-select-option>
              <a-select-option :value="0">群主未发送</a-select-option>
              <a-select-option :value="1">群主已发送</a-select-option>
              <a-select-option :value="2">发送失败</a-select-option>
            </a-select>
          </div>
        </div>
        <div class="group_details_row">
          <div v-if="sendData.length!=0">
            <div class="group_details" v-for="(item,index) in sendData" :key="index">
              <div style="display: flex;justify-content:space-between;">
                <div>
                  <img src="../../assets/avatar-room-default.svg" alt="">
                  {{ item.roomName }}
                  <a-tag color="green" v-if="item.status==1">群主已发送</a-tag>
                  <a-tag color="red" v-if="item.status==0">群主未发送</a-tag>
                  <a-tag color="gray" v-if="item.status==2||item.status==3">发送失败</a-tag>
                </div>
                <a style="margin-top: 15px;" @click="$router.push({ path: '/workRoom/detail?workRoomId=' + item.roomId })">群详情</a>
              </div>
              <a-divider />
            </div>
          </div>
          <div v-else style="margin-top: 75px;">
            <Empty />
          </div>
        </div>
      </div>
    </a-modal>
  </div>
</template>
<script>
import { Empty } from 'ant-design-vue'
// eslint-disable-next-line no-unused-vars
import { show, roomOwnerSendIndex, roomReceiveIndex, department, remind } from '@/api/roomMessageBatchSend'
export default {
  components: { Empty },
  data () {
    return {
      info: {},
      contentArray: [],
      receiveLst: {},
      sendArray: [],
      //  最新
      tableIndex: 1,
      table: {
        groupColumns: [
          {
            title: '群聊名称',
            dataIndex: 'roomName'
          },
          {
            title: '群主',
            dataIndex: 'employeeName',
            scopedSlots: { customRender: 'employeeName' }
          },
          {
            title: '群聊成员数量',
            dataIndex: 'roomEmployeeNum'
          },
          {
            title: '消息送达状态',
            dataIndex: 'status',
            scopedSlots: { customRender: 'status' }
          },
          {
            title: '群聊创建时间',
            dataIndex: 'roomCreateTime'
          },
          {
            title: '操作',
            dataIndex: 'operation',
            scopedSlots: { customRender: 'operation' }
          }
        ],
        sendColumns: [
          {
            title: '群主',
            dataIndex: 'employeeName',
            scopedSlots: { customRender: 'employeeName' }
          },
          {
            title: '群发发送状态',
            dataIndex: 'status',
            scopedSlots: { customRender: 'status' }
          },
          {
            title: '本次群发群聊总数',
            dataIndex: 'sendRoomTotal'
          },
          {
            title: '已发送群聊数',
            dataIndex: 'sendSuccessTotal'
          },
          {
            title: '确认发送时间',
            dataIndex: 'sendTime'
          },
          {
            title: '操作',
            dataIndex: 'operate',
            scopedSlots: { customRender: 'operate' }
          }
        ],
        data: []
      },
      batchId: '',
      //  客户接收筛选数据
      tableAskData: {
        batchId: '',
        keyWords: ''
      },
      //  员工数据
      employeeData: [],
      detailsPopup: false,
      groupTotal: 0,
      groupSendNum: 0,
      sendData: [],
      sendStatus: 4,
      employeeIds: ''
    }
  },
  mounted () {
    this.batchId = this.$route.query.batchId
    this.tableAskData.batchId = this.batchId
    this.getDetailsData(this.batchId)
    this.getStaffData()
    this.clientAccept()
  },
  methods: {
    // 选择状态
    selectStatus () {
      this.groupSendData()
    },
    // 获取群发详情数据
    groupSendData () {
      const params = {
        batchId: this.batchId,
        employeeIds: this.employeeIds
      }
      if (this.sendStatus == 4) {
        params.sendStatus = ''
      } else {
        params.sendStatus = this.sendStatus
      }
      roomReceiveIndex(params).then((res) => {
        this.sendData = res.data.list
      })
    },
    // 群发详情
    groupSendDetails (record) {
      this.groupTotal = record.sendRoomTotal
      this.groupSendNum = record.sendSuccessTotal
      this.employeeIds = record.employeeId
      this.groupSendData()
      this.detailsPopup = true
    },
    // 群详情
    detailsRow (record) {
      this.$router.push({ path: '/workRoom/detail?workRoomId=' + record.roomId })
    },
    // 提醒发送
    remindBtn (record) {
      remind({
        batchId: this.batchId,
        batchEmployId: record.employeeId
      }).then((res) => {
        this.$message.success('提醒成功')
      })
    },
    // 重置按钮
    resetBtn () {
      this.tableAskData = {}
      if (this.tableIndex == 1) {
        this.clientAccept()
      } else {
        this.clientSend()
      }
    },
    // 搜索
    searchTableData () {
      if (this.tableIndex == 1) {
        this.clientAccept()
      } else {
        this.clientSend()
      }
    },
    // 获取员工数据
    getStaffData () {
      department().then((res) => {
        this.employeeData = res.data.employee
      })
    },
    // 客户发送详情
    tabTable () {
      this.table.data = []
      this.tableAskData = {}
      this.tableAskData.batchId = this.batchId
      if (this.tableIndex == 1) {
        this.clientAccept()
      } else {
        this.clientSend()
      }
    },
    clientSend () {
      roomOwnerSendIndex(this.tableAskData).then((res) => {
        console.log(res)
        this.table.data = res.data.list
      })
    },
    // 客户接受详情
    clientAccept () {
      roomReceiveIndex(this.tableAskData).then((res) => {
        this.table.data = res.data.list
      })
    },
    // 获取详情数据
    getDetailsData (batchId) {
      show({
        batchId
      }).then((res) => {
        this.info = res.data
      })
    }
  }
}
</script>

<style lang="less" scoped>
.details_top_data{
  background: #fbfdff;
  border: 1px solid #cfe8ff;
  padding: 20px 50px 20px 50px;
  display: flex;
  justify-content:space-between;
}
.row_name{
  border-left: 3px solid #1890FF;
  padding-left: 8px;
  height: 25px;
}
/deep/ .ant-modal-header{
  text-align: center;
}
.group_details_row{
  height: 300px;
  overflow-y: scroll;
  padding-right: 10px;
}
.group_details_row::-webkit-scrollbar {
  width: 8px;
}
.group_details_row::-webkit-scrollbar-track {
  background-color:#fff;
  -webkit-border-radius: 2em;
  -moz-border-radius: 2em;
  border-radius:2em;
}
.group_details_row::-webkit-scrollbar-thumb {
  background-color:#D9D9D9;
  -webkit-border-radius: 2em;
  -moz-border-radius: 2em;
  border-radius:2em;
}
.group_details{
  margin-top: 15px;
}
.screen_box{
  margin-left: 30px;
}
.screen_box:first-child{
  margin-left: 0px;
}
.top {
  width: 100%;
}
.col_right {
.child_module{
  height:307px;
}
}
.child_module {
  background-color: #fff;
  border-radius: 5px;
  padding-bottom: 1px;
  .child_title {
    font-size: 16px;
    font-weight: 600;
    padding: 15px;
    border-bottom: 1px solid #f2f2f2;
  }
  .user_info {
    .item {
      padding-left: 15px;
      display: flex;
      align-items: flex-start;
      margin-bottom: 20px;
      line-height: 30px;
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
      .msg_data {
        margin-bottom: 15px;
        .item_image{
          img{
            width: 90px;
            height: 90px;
            border-radius: 5px
          }
        }
      }
      .obj {
        margin-right: 5px;
      }
    }
    .item:first-child {
      margin-top: 25px;
    }
    .item:last-child {
      margin-bottom: 17px;
    }
  }
  .total_module {
    background-color: #fbfdff;
    border: 1px solid #daedff;
    margin: 30px;
    position: relative;
    padding: 10px 0px;
    .item {
      padding: 15px 0px;
      text-align: center;
      span {
        font-size: 20px;
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
  .text_num {
    padding-left: 15px;
  }
  .search_module {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    padding: 15px;
    .ant-select,
    .ant-input-search {
      width: 200px;
      margin-right: 25px;
    }
  }
}
.show_table {
  margin: 15px 15px;
  .ant-btn {
    padding: 0px 10px;
  }
}
</style>
