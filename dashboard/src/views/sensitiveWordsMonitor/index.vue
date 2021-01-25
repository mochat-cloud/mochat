<template>
  <div class="wrapper">
    <div :split="false" class="lists">
      <a-alert :show-icon="false" message="1、当敏感词被不启用时，曾经所触发的内容依然在列表中可见。 " banner />
      <a-alert :show-icon="false" message="2、显示历史上开启会话存档的企业成员。若曾经设置过某员工违规提醒，现在对该员工关闭、不再开启会话存档或是该员工离职，那么其历史触发敏感词的监控内容，依然在列表中可见。" banner />
      <a-alert :show-icon="false" message="3、显示历史上设置过违规提醒的群聊。如曾经设置过某群聊违规提醒，现在该群聊不再设置，那么其历史触发敏感词的监控内容，依然在列表中可见。" banner />
    </div>
    <a-card>
      <a-form :label-col="{ span: 7 }" :wrapper-col="{ span: 14 }">
        <a-row :gutter="16">
          <a-col :lg="8">
            <a-form-item
              label="选择成员：">
              <a-button @click="() => {this.choosePeopleShow = true}">选择成员</a-button>
            </a-form-item>
          </a-col>
          <a-col :lg="8">
            <a-form-item
              label="选择群聊：">
              <a-button @click="() => {this.groupChatModal=true}">选择群聊</a-button>
            </a-form-item>
          </a-col>
        </a-row>
        <a-row>
          <a-col :lg="8">
            <a-form-item
              label="选择分组：">
              <a-select v-model="screentData.intelligentGroupId">
                <a-select-option v-for="item in groupList" :key="item.groupId">
                  {{ item.name }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :lg="8">
            <a-form-item
              label="触发日期：">
              <a-range-picker v-model="dataList" @change="onChangeTime" />
            </a-form-item>
          </a-col>
          <a-col :lg="8">
            <a-form-item label="">
              <a-button v-permission="'/sensitiveWordsMonitor/index@search'" type="primary" @click="getTableData">查询</a-button>
              <a-button style="marginLeft:5px" @click="reset">重置</a-button>
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>
      <a-table
        style="marginTop: 20px"
        bordered
        :data-source="tableData"
        :rowKey="record => record.sensitiveWordMonitorId"
        :columns="columns"
        :pagination="pagination"
        @change="handleTableChange">
        <div slot="action" slot-scope="text, record">
          <template>
            <a-button v-permission="'/sensitiveWordsMonitor/index@detail'" type="link" @click="detailMessage(record.sensitiveWordMonitorId)">对话详情</a-button>
          </template>
        </div>
      </a-table>
    </a-card>
    <a-modal
      title="选择企业成员"
      :maskClosable="false"
      :width="700"
      :visible="choosePeopleShow"
      @cancel="() => { this.choosePeopleShow = false; this.employeeIdList = ''; this.employees = []; this.searchKey = ''}"
    >
      <department
        v-if="choosePeopleShow"
        :searchKeyEmpty="searchKey"
        :checkedKey="employees"
        :memberKey="employees"
        @change="peopleChange"
        @search="searchEmp"></department>
      <template slot="footer">
        <a-button @click="() => { this.choosePeopleShow = false; this.employeeIdList = ''; this.employees = []; this.searchKey = '' }">取消</a-button>
        <a-button type="primary" @click="() => { this.choosePeopleShow = false; }">确定</a-button>
      </template>
    </a-modal>
    <a-modal
      title=""
      :width="900"
      :visible="detailModal"
      @cancel="() => {this.detailModal = false}">
      <a-table
        style="width: 800px"
        bordered
        :data-source="detailList"
        :pagination="false"
        :rowKey="(record,index)=>{return index}"
        :columns="detailColumns">
        <div slot="sender" slot-scope="text, record">
          <div v-if="record.isTrigger == 1" style="color: red">{{ record.sender }}</div>
          <div v-else>{{ record.sender }}</div>
        </div>
        <div slot="sendTime" slot-scope="text, record">
          <div v-if="record.isTrigger == 1" style="color: red">{{ record.sendTime }}</div>
          <div v-else>{{ record.sendTime }}</div>
        </div>
        <div slot="action" slot-scope="text, record">
          <div v-if="record.isTrigger == 1" style="color: red">
            <div v-if="record.msgType == 1">
              {{ record.msgContent.content }}
            </div>
            <div v-if="record.msgType == 2">
              <img style="width:80px; height:auto" :src="record.msgContent.ossFullPath" alt="">
            </div>
            <div v-if="record.msgType == 6">
              <div class="applets-box">
                <h4>{{ record.msgContent.title }}</h4>
              </div>
            </div>
            <div v-if="record.msgType == 4">
              <audio :src="record.msgContent.ossFullPath" controls="controls"></audio>
            </div>
            <div v-if="record.msgType > 7">
              {{ record.msgContent.content }}
            </div>
          </div>
          <div v-else>
            <div v-if="record.msgType == 1">
              {{ record.msgContent.content }}
            </div>
            <div v-if="record.msgType == 2">
              <img style="width:80px; height:auto" :src="record.msgContent.ossFullPath" alt="">
            </div>
            <div v-if="record.msgType == 4">
              <audio :src="record.msgContent.ossFullPath" controls="controls"></audio>
            </div>
            <div v-if="record.msgType == 5">
              <video style="width: 200px; height: 100px" :src="record.msgContent.ossFullPath" controls="controls"></video>
            </div>
            <div v-if="record.msgType == 6">
              <div class="applets-box">
                <h4>{{ record.msgContent.title }}</h4>
              </div>
            </div>
            <div v-if="record.msgType > 7">
              {{ record.msgContent.content }}
            </div>
          </div>
        </div>
      </a-table>
      <template slot="footer">
        <a-button type="primary" @click="() => {this.detailModal = false}">确定</a-button>
      </template>
    </a-modal>
    <div class="bbox" ref="bbox">
      <a-modal
        :getContainer="() => $refs.bbox"
        :visible="groupChatModal"
        @cancel="() => {this.groupChatModal = false; this.roomIdList = [] ; this.groupSearch = ''; this.getGroupChatList()}"
        title="选择群聊">
        <a-input-search v-model="groupSearch" placeholder="输入群名称" @search="onSearch" />
        <div style="color:red;marginBottom:10px;">只显示历史上设置过违规提醒的群聊。如曾经设置过某群聊违规提醒，现在该群聊不再设置，那么其历史触发敏感词的监控内容，依然在列表中可见。</div>
        <p>全部群聊（{{ groupNumber }}）</p>
        <a-table
          :rowKey="record => record.roomId"
          :columns="groupColumns"
          :data-source="groupChatList"
          :row-selection="{ selectedRowKeys: roomIdList, onChange: onSelectChange }">
          <div slot="roomName" slot-scope="text, record">
            <div class="group">
              <a-icon type="user" />
              <div>
                <span>{{ record.roomName }}</span>
                <span>{{ record.currentNum }}/{{ record.roomMax }}</span>
              </div>
            </div>
          </div>
        </a-table>
        <template slot="footer">
          <a-button @click="() => { this.groupChatModal = false; this.roomIdList = [] ; this.groupSearch = ''; this.getGroupChatList() }">取消</a-button>
          <a-button type="primary" @click="() => { this.groupChatModal = false; this.groupSearch = ''; this.getGroupChatList() }">确定</a-button>
        </template>
      </a-modal>
    </div>
  </div>
</template>

<script>
import department from './components/department'
import { groupChatList } from '@/api/workContact'
import { sensitiveWordsMonitor, sensitiveWordsGroupList, dialogueDetails } from '@/api/sensitiveWordsMonitor'
export default {
  components: {
    department
  },
  data () {
    return {
      searchKey: '',
      choosePeopleShow: false,
      detailModal: false,
      groupChatModal: false,
      columns: [
        {
          align: 'center',
          title: '敏感词',
          dataIndex: 'sensitiveWordName'
        },
        {
          align: 'center',
          title: '触发来源',
          dataIndex: 'sourceText'
        },
        {
          align: 'center',
          title: '触发人',
          dataIndex: 'triggerName'
        },
        {
          align: 'center',
          title: '触发场景',
          dataIndex: 'triggerScenario'
        },
        {
          align: 'center',
          title: '触发时间',
          dataIndex: 'triggerTime'
        },
        {
          align: 'center',
          title: '操作',
          scopedSlots: { customRender: 'action' }
        }
      ],
      detailColumns: [
        {
          align: 'center',
          title: '发送人',
          dataIndex: 'sender',
          scopedSlots: { customRender: 'sender' }
        },
        {
          align: 'center',
          title: '发送时间',
          dataIndex: 'sendTime',
          scopedSlots: { customRender: 'sendTime' }
        },
        {
          align: 'center',
          title: '发送内容',
          width: 500,
          scopedSlots: { customRender: 'action' }
        }
      ],
      // 群聊表格Cloumn
      groupColumns: [
        {
          title: '',
          dataIndex: 'roomName',
          key: 'roomName',
          align: 'center',
          scopedSlots: { customRender: 'roomName' }
        }
      ],
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      tableData: [],
      groupList: [],
      screentData: {},
      employeeIdList: '',
      employees: [],
      detailList: [],
      groupChatList: [],
      groupNumber: '',
      roomIdList: [],
      groupSearch: '',
      dataList: []
    }
  },
  created () {
    this.getTableData()
    this.getGroupList()
    this.getGroupChatList()
  },
  methods: {
    getTableData () {
      const params = {
        employeeId: this.employeeIdList,
        workRoomId: this.roomIdList.join(','),
        intelligentGroupId: this.screentData.intelligentGroupId,
        triggerStart: this.screentData.triggerStart,
        triggerEnd: this.screentData.triggerEnd,
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      sensitiveWordsMonitor(params).then(res => {
        this.tableData = res.data.list
        this.pagination.total = res.data.page.total
      })
    },
    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
      this.getTableData()
    },
    // 获取分组列表
    getGroupList () {
      sensitiveWordsGroupList().then(res => {
        this.groupList = res.data
      })
    },
    // 群聊分组
    getGroupChatList () {
      groupChatList({
        name: this.groupSearch
      }).then(res => {
        this.groupChatList = res.data.list
        this.groupNumber = res.data.total
      })
    },
    // 对话详情
    detailMessage (id) {
      dialogueDetails({
        sensitiveWordsMonitorId: id
      }).then(res => {
        this.detailList = res.data
        this.detailModal = true
      })
    },
    // 成员
    // 成员选择
    peopleChange (data) {
      const arr = []
      data.map(item => {
        arr.push(item.employeeId)
      })
      this.employeeIdList = arr.join(',')
    },
    searchEmp (data) {
      if (data.length === 0) {
        this.employeeIdList = '空'
      } else {
        this.employeeIdList = data[0].id
      }
    },
    onChangeTime (data, strData) {
      this.screentData.triggerStart = strData[0]
      this.screentData.triggerEnd = strData[1]
    },
    onSearch (value) {
      this.groupSearch = value
      this.getGroupChatList()
    },
    // 群聊筛选
    onSelectChange (selectedRowKeys) {
      this.roomIdList = selectedRowKeys
    },
    reset () {
      this.screentData = {}
      this.roomIdList = []
      this.employeeIdList = ''
      this.employees = []
      this.pagination.current = 1
      this.dataList = []
    }
  }
}
</script>

<style lang="less" scoped>
.bbox {
  .group {
    width: 100%;
    display: flex;
    align-items: center;
    .anticon {
      font-size: 24px;
    }
    div {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      margin-left: 5px;
    }
  }
}
</style>
