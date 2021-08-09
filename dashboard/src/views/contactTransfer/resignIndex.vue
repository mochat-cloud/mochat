<template>
  <div class="resign-index">
    <a-card class="mb16">
      <a-tabs type="card" default-active-key="0" @change="tabSwitch">
        <a-tab-pane key="0" tab="待分配客户">
          <div class="filter-form">
            <!--            客户昵称-->
            <div class="item">
              <label>客户昵称：</label>
              <div class="input">
                <a-input-search
                  placeholder="请输入要搜索的客户"
                  v-model="paramsAskClient.contactName"
                  @search="retrievalClient"
                  :allowClear="true"
                  @change="emptyClientIpt"
                ></a-input-search>
              </div>
            </div>
            <!--            所属客服-->
            <div class="item">
              <label>所属客服：</label>
              <div class="input belongTo" @click="$refs.selectMember.show()">
                <span class="tips" v-if="showEmployee.length==0">请选择客服</span>
                <a-tag v-for="(item,index) in showEmployee" :key="index">{{ item.name }}</a-tag>
              </div>
            </div>
            <selectMember ref="selectMember" @change="effectStaff"/>
            <!--            添加时间-->
            <div class="item">
              <label>添加时间：</label>
              <div class="input">
                <a-range-picker @change="retrievalDate" v-model="showTimeSearch" />
              </div>
            </div>
            <div class="item">
              <a-button @click="resetBtn">重置</a-button>
            </div>
          </div>
        </a-tab-pane>
        <a-tab-pane key="1" tab="待分配群聊">
          <div class="filter-form">
            <div class="item">
              <label>群名称：</label>
              <div class="input">
                <a-input-search
                  placeholder="请输入要搜索的群名称"
                  v-model="paramsAskGroup.roomName"
                  @search="retrievalGroup"
                  :allowClear="true"
                  @change="emptyGroupIpt"
                ></a-input-search>
              </div>
            </div>
          </div>
        </a-tab-pane>
      </a-tabs>
    </a-card>
    <a-card>
      <div class="flex">
        <div class="info-box">
          <span class="f-blod">共{{ table.data.length }}个已分配客户</span>
          <a-divider type="vertical"/>
          <span class="desc-text" v-if="currentTab==0">上次同步时间：{{ synchroTime }}</span>
        </div>
        <div class="btn-box">
          <a-button
            type="primary"
            ghost
            v-if="currentTab==0"
            @click="allocation"
          >分配客户</a-button>
          <a-button type="primary" ghost v-else @click="distributionGroup">分配群聊</a-button>
          <a-button type="primary" ghost @click="$router.push('/contactTransfer/resignAllotRecord')">分配记录</a-button>
          <a-button type="primary" ghost v-if="currentTab==0" @click="updateTo">同步</a-button>
        </div>
        <!--        选择员工、群聊弹窗-->
        <selectStaff ref="choiceStaff" @change="acceptData" />
      </div>
      <div class="table mt20">
        <a-table
          :columns="currentTab==0?table.clientCol:table.groupCol"
          :data-source="table.data"
          :row-selection="{selectedRowKeys:table.rowSelection,onChange: onSelectChange}"
          :pagination="true"
        >
          <!--     待分配客户     -->
          <div slot="employeeName" slot-scope="text">
            <a-tag><a-icon type="user" />{{ text }}</a-tag>
          </div>
          <div slot="tags" slot-scope="text">
            <a-tag v-for="(item,index) in text" :key="index">{{ item }}</a-tag>
          </div>
          <div slot="operation" slot-scope="text,record">
            <a
              @click="$router.push({ path: '/workContact/contactFieldPivot?contactId='+record.contactId+'&employeeId='+record.employeeId+'&isContact=2'})"
            >客户详情</a>
          </div>
          <!--     群聊     -->
          <div slot="owner" slot-scope="text">
            <a-tag><a-icon type="user" />{{ text }}</a-tag>
          </div>
          <div slot="handGroup">
            <a>群聊详情</a>
          </div>
        </a-table>
      </div>
    </a-card>
  </div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import { unassignedListApi, roomApi, saveUnassignedListApi, indexApi, postRoomApi } from '@/api/contactTransfer'
import selectMember from '@/components/Select/member'
import selectStaff from '@/components/addlabel/selectStaff'
export default {
  components: { selectMember, selectStaff },
  data () {
    return {
      synchroTime: '',
      currentTab: 0,
      // 展示客服数据
      showEmployee: [],
      // 展示筛选时间
      showTimeSearch: [],
      // 请求数据 客户
      paramsAskClient: {
        employeeId: '',
        contactName: '',
        addTimeStart: '',
        addTimeEnd: ''
      },
      // 选中表格数据
      tableSelectClient: [],
      // 请求群组
      paramsAskGroup: {
        roomName: ''
      },
      table: {
        // 离职待分配客户列表
        clientCol: [
          {
            key: 'nickName',
            dataIndex: 'nickName',
            title: '客户昵称'
          },
          {
            key: 'employeeName',
            dataIndex: 'employeeName',
            title: '所属客服',
            scopedSlots: { customRender: 'employeeName' }
          },
          {
            key: 'tags',
            dataIndex: 'tags',
            title: '客户标签',
            scopedSlots: { customRender: 'tags' }
          },
          {
            key: 'addTime',
            dataIndex: 'addTime',
            title: '添加时间'
          },
          {
            key: 'lastMsgTime',
            dataIndex: 'lastMsgTime',
            title: '上次对话时间'
          },
          {
            key: 'addWay',
            dataIndex: 'addWay',
            title: '添加渠道'
          },
          {
            key: 'operation',
            dataIndex: 'operation',
            title: '操作',
            scopedSlots: { customRender: 'operation' }
          }
        ],
        // 离职待分配客服群
        groupCol: [
          {
            key: 'roomName',
            dataIndex: 'roomName',
            title: '群聊名称'
          },
          {
            key: 'owner',
            dataIndex: 'owner',
            title: '群主',
            scopedSlots: { customRender: 'owner' }
          },
          {
            key: 'userNum',
            dataIndex: 'userNum',
            title: '群人数'
          },
          {
            key: 'addNum',
            dataIndex: 'addNum',
            title: '今日入群'
          },
          {
            key: 'quitNum',
            dataIndex: 'quitNum',
            title: '今日退群'
          },
          {
            key: 'createTime',
            dataIndex: 'createTime',
            title: '创群时间'
          },
          {
            key: 'handGroup',
            dataIndex: 'handGroup',
            title: '操作',
            scopedSlots: { customRender: 'handGroup' }
          }
        ],
        data: [],
        // 离职待分配
        rowSelection: []
      }
    }
  },
  created () {
    this.getClientTableData()
  },
  methods: {
    // 获取选中的表格数据
    onSelectChange (e) {
      this.tableSelectClient = []
      this.table.rowSelection = e
      if (this.currentTab == 0) {
        e.forEach((item) => {
          const askRowData = {
            employeeWxId: '',
            contactWxId: ''
          }
          askRowData.employeeWxId = this.table.data[item].employeeWxId
          askRowData.contactWxId = this.table.data[item].contactWxId
          this.tableSelectClient.push(askRowData)
        })
      } else {
        e.forEach((item, index) => {
          this.tableSelectClient[index] = this.table.data[item].chatId
        })
      }
    },
    // 待分配客户
    // 分配客户
    allocation () {
      if (this.tableSelectClient == '') {
        this.$message.warning('请选择客户')
        return false
      }
      this.$refs.choiceStaff.show(0)
    },
    // 分配群聊
    distributionGroup () {
      if (this.tableSelectClient == '') {
        this.$message.warning('请选择群聊')
        return false
      }
      this.$refs.choiceStaff.show(0)
    },
    // 接收组件传值
    acceptData (e) {
      if (this.currentTab == 0) {
        const params = {
          type: 1,
          list: JSON.stringify(this.tableSelectClient),
          takeoverUserId: e.wxUserId
        }
        indexApi(params).then((res) => {
          let successNum = 0
          let errNum = 0
          res.data.forEach((item, index) => {
            if (item.errcode == 0) {
              successNum++
            } else {
              errNum++
            }
          })
          this.$message.info('已成功分配' + successNum + '位客户，分配失败' + errNum + '位客户')
          this.table.rowSelection = []
        })
      } else {
        const params = {
          type: 1,
          list: JSON.stringify(this.tableSelectClient),
          takeoverUserId: e.wxUserId
        }
        postRoomApi(params).then((res) => {
          let successNum = 0
          let errNum = 0
          res.data.forEach((item, index) => {
            if (item.errcode == 0) {
              successNum++
            } else {
              errNum++
            }
          })
          this.$message.info('已成功分配' + successNum + '个群聊，分配失败' + errNum + '个群聊')
          this.table.rowSelection = []
        })
      }
    },
    // 同步
    updateTo () {
      this.table.data = []
      this.getClientTableData(this.paramsAskClient)
      this.$message.success('同步成功')
    },
    // 重置
    resetBtn () {
      this.paramsAskClient = {}
      this.showEmployee = []
      this.showTimeSearch = []
      this.getClientTableData(this.paramsAskClient)
    },
    // 接收成员组件数据
    effectStaff (e) {
      this.showEmployee = e
      const askServiceData = []
      e.forEach((item, index) => {
        askServiceData[index] = item.id
      })
      this.paramsAskClient.employeeId = JSON.stringify(askServiceData)
      this.getClientTableData(this.paramsAskClient)
    },
    // 搜索客户
    retrievalClient () {
      this.getClientTableData(this.paramsAskClient)
    },
    // 清空客户输入框
    emptyClientIpt () {
      if (this.paramsAskClient.contactName == '') {
        this.getClientTableData(this.paramsAskClient)
      }
    },
    // 检索日期
    retrievalDate (date, dateString) {
      this.paramsAskClient.addTimeStart = dateString[0]
      this.paramsAskClient.addTimeEnd = dateString[1]
      this.getClientTableData(this.paramsAskClient)
    },
    // 获取待分配客户表格数据
    getClientTableData (params) {
      unassignedListApi(params).then((res) => {
        this.table.data = res.data.list
        this.synchroTime = res.data.lastTime
      })
    },

    // 待分配群聊
    // 搜索群名称
    retrievalGroup () {
      this.getGroupTable(this.paramsAskGroup)
    },
    // 清空群名称
    emptyGroupIpt () {
      if (this.paramsAskGroup.roomName == '') {
        this.getGroupTable(this.paramsAskGroup)
      }
    },
    // 获取分配群列表数据
    getGroupTable (params) {
      roomApi(params).then((res) => {
        this.table.data = res.data
      })
    },
    // 切换面板
    tabSwitch (e) {
      this.currentTab = e
      this.table.data = []
      this.table.rowSelection = []
      if (this.currentTab == 0) {
        this.getClientTableData(this.paramsAskClient)
      } else if (this.currentTab == 1) {
        this.getGroupTable(this.paramsAskGroup)
      }
    }
  }
}
</script>
<style lang="less" scoped>
.filter-form {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  .item {
    min-width: 266px;
    margin-top: 16px;
    padding-right: 33px;
    box-sizing: content-box;
    display: flex;
    justify-content: flex-start;
    align-items: center;

    .input {
      width: 208px;
      padding-left: 12px;
    }
    .belongTo{
      padding-left: 7px;
      border: 1px solid #D9D9D9;
      height: 32px;
      line-height: 32px;
      cursor: pointer;
      .tips{
        color: #BFBFBF;
      }
    }
    .ant-select {
      width: 100%;
    }
  }
}
.info-box {
  flex: 1;
}
.btn-box {
  .ant-btn {
    margin-right: 10px;
  }
}
</style>
