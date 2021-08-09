<template>
  <div>
    <a-card :bordered="false">
      <a-form :label-col="{ span: 7 }" :wrapper-col="{ span: 14 }">
        <a-row :gutter="48">
          <a-col :lg="8">
            <a-form-item
              label="关键词：">
              <a-input v-model="screenData.keyWords" placeholder="客户姓名／昵称"></a-input>
            </a-form-item>
          </a-col>
          <a-col :lg="8">
            <a-form-item
              label="备注：">
              <a-input v-model="screenData.remark	" placeholder="备注"></a-input>
            </a-form-item>
          </a-col>
          <a-col :lg="8">
            <a-form-item
              label="用户画像：">
              <a-row>
                <a-col :span="10">
                  <a-select v-model="screenData.fieldId" @change="portraitChange">
                    <a-select-option v-for="item in UserPortraitList" :key="item.fieldId" :value="item.fieldId">
                      {{ item.name }}
                    </a-select-option>
                  </a-select>
                </a-col>
                <a-col v-if="fieldTypeText" :span="10" :offset="2"><a-input v-model="screenData.fieldValue"/></a-col>
                <a-col v-else :span="10" :offset="2">
                  <a-select v-model="optionsSelect">
                    <a-select-option v-for="(val, index) in fieldSelect.options" :key="index" :value="val">
                      {{ val }}
                    </a-select-option>
                  </a-select>
                </a-col>
              </a-row>
            </a-form-item>
          </a-col>
        </a-row>
        <a-row :gutter="48">
          <a-col :lg="8">
            <a-form-item
              label="客户性别：">
              <a-select v-model="screenData.gender">
                <a-select-option v-for="item in genderList" :key="item.value">
                  {{ item.label }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :lg="8">
            <a-form-item
              label="客户来源：">
              <a-select v-model="screenData.addWay">
                <a-select-option v-for="item in customersSourceList" :key="item.addWay">
                  {{ item.addWayText }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :lg="8">
            <a-form-item
              label="选择群聊：">
              <a-input v-model="screenData.roomId" placeholder="选择群聊" @click="() => { this.groupChatModal = true }"></a-input>
            </a-form-item>
          </a-col>
        </a-row>
        <a-row :gutter="48">
          <a-col :lg="8">
            <a-form-item
              label="客户持群数：">
              <a-select v-model="groupNum">
                <a-select-option v-for="item in holdingGroupList" :key="item.value">
                  {{ item.label }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :lg="8">
            <a-form-item
              label="部门成员：">
              <a-input placeholder="部门成员" @click="() => { this.choosePeopleShow = true }"></a-input>
            </a-form-item>
          </a-col>
          <a-col :lg="8">
            <a-form-item
              label="客户编码：">
              <a-input v-model="screenData.businessNo"></a-input>
            </a-form-item>
          </a-col>
        </a-row>
        <a-row :gutter="48">
          <a-col :lg="8">
            <a-form-item
              label="添加时间：">
              <a-range-picker
                v-model="timeValue"
                :show-time="{ format: 'HH:mm' }"
                format="YYYY-MM-DD HH:mm"
                :placeholder="['开始时间', '结束时间']"
                @change="onTimeChange"
                @ok="onOk"
              />
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>
      <div class="tbox" ref="tbox">
        <a-modal
          :getContainer="() => $refs.tbox"
          :visible="groupChatModal"
          @cancel="() => {this.groupChatModal = false; this.roomIdList = [] ; this.groupSearch = ''; this.getGroupChatList()}"
          title="选择群聊">
          <a-input-search v-model="groupSearch" placeholder="输入群名称" @search="onSearch" />
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
      <a-modal
        title="选择企业成员"
        :maskClosable="false"
        :width="700"
        :visible="choosePeopleShow"
        @cancel="() => {this.choosePeopleShow = false; this.employeeIdList = ''; this.employees = []; this.searchKey = ''}"
      >
        <department v-if="choosePeopleShow" :checkedKey="employees" :memberKey="employees" @change="peopleChange" @search="searchEmp"></department>
        <template slot="footer">
          <a-button @click="() => { this.choosePeopleShow = false; this.employeeIdList = ''; this.employees = []; this.searchKey = ''}">取消</a-button>
          <a-button type="primary" @click="() => { this.choosePeopleShow = false; }">确定</a-button>
        </template>
      </a-modal>
      <div class="footer">
        <a-button v-permission="'/workContact/index@search'" type="primary" style="marginRight: 10px" @click="() => { this.pagination.current = 1; this.getTableData() }">查询</a-button>
        <a-button @click="reset">重置</a-button>
      </div>
      <div class="table-wrapper">
        <div class="btn">
          <a-button v-permission="'/workContact/index@sync'" type="primary" @click="() => { this.getSynContact() }">同步客户</a-button>
        </div>
        <a-table
          :columns="columns"
          :data-source="tableData"
          :rowKey="record => record.id"
          :pagination="pagination"
          @change="handleTableChange">
          <div slot="news" slot-scope="text, record">
            <div class="news">
              <img :src="record.avatar"/>
              <div class="name">
                {{ record.name }}
                <a-icon v-if="record.gender === 1" type="man" />
                <a-icon v-if="record.gender === 2" type="woman" />
                <br>
                {{ record.remark }}
              </div>
              <div class="weixin">@微信</div>
            </div>
          </div>
          <div slot="businessNo" slot-scope="text, record">
            <span v-if="record.businessNo !== ''">{{ record.businessNo }}</span>
            <span v-else>--</span>
          </div>
          <div slot="roomName" slot-scope="text, record">
            <span v-if="record.roomName.length !== 0">{{ record.roomName.join(',') }}</span>
            <span v-else>--</span>
          </div>
          <div slot="tag" slot-scope="text, record">
            <span v-if="record.tag.length !== 0">{{ record.tag.join(',') }}</span>
            <span v-else>--</span>
          </div>
          <div slot="action" slot-scope="text, record">
            <template>
              <router-link v-permission="'/workContact/contactFieldPivot'" :to="{path:`/workContact/contactFieldPivot?contactId=${record.contactId}&employeeId=${record.employeeId}&isContact=${record.isContact}`}">
                <a-button type="link">查看详情</a-button>
              </router-link>
            </template>
          </div>
        </a-table>
      </div>
    </a-card>
  </div>
</template>

<script>
import { workContactList, groupChatList, customersSource, UserPortraitList, synContact } from '@/api/workContact'
import department from './components/department'
export default {
  components: {
    department
  },
  data () {
    return {
      groupChatModal: false,
      screenData: {
        gender: 3,
        addWay: '全部',
        fieldId: 0
      },
      columns: [
        {
          title: '客户信息',
          dataIndex: 'news',
          align: 'center',
          width: '200px',
          scopedSlots: { customRender: 'news' }
        },
        {
          title: '客户编号',
          dataIndex: 'businessNo',
          align: 'center',
          scopedSlots: { customRender: 'businessNo' }
        },
        {
          title: '所在群',
          dataIndex: 'roomName',
          align: 'center',
          scopedSlots: { customRender: 'roomName' }
        },
        {
          title: '来源',
          dataIndex: 'addWayText',
          align: 'center'
        },
        {
          title: '标签',
          dataIndex: 'tag',
          align: 'center',
          scopedSlots: { customRender: 'tag' }
        },
        {
          title: '归属成员',
          dataIndex: 'employeeName',
          align: 'center'
        },
        {
          title: '添加时间',
          dataIndex: 'createTime',
          align: 'center'
        },
        {
          title: '操作',
          dataIndex: 'action',
          align: 'center',
          scopedSlots: { customRender: 'action' }
        }
      ],
      tableData: [],
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
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
      // 客户性别
      genderList: [
        {
          label: '未知',
          value: 0
        },
        {
          label: '男',
          value: 1
        },
        {
          label: '女',
          value: 2
        },
        {
          label: '全部',
          value: 3
        }
      ],
      // 群聊列表
      groupChatList: [],
      // 群聊总人数
      groupNumber: '',
      // 群聊搜索
      groupSearch: '',
      roomIdList: [],
      // 客户来源列表
      customersSourceList: [],
      // 持群列表
      holdingGroupList: [
        {
          label: '无群',
          value: 0
        },
        {
          label: '一个',
          value: 1
        },
        {
          label: '多个',
          value: 2
        },
        {
          label: '全部',
          value: 3
        }
      ],
      // 用户画像下拉框
      UserPortraitList: [],
      // 成员弹框
      choosePeopleShow: false,
      department: [],
      employeeIdList: '',
      // 持群Id
      groupNum: 3,
      selectedRowKeys: [],
      timeValue: [],
      // 用户画像筛选属性类型
      fieldType: '',
      fieldTypeText: null,
      fieldSelect: [],
      optionsSelect: '',
      employees: [],
      searchKey: ''
    }
  },
  created () {
    this.getTableData()
    this.getGroupChatList()
    this.getCustomersSource()
    this.getUserPortraitList()
  },
  methods: {
    getTableData () {
      const params = {
        keyWords: this.screenData.keyWords,
        remark: this.screenData.remark,
        fieldId: this.screenData.fieldId,
        fieldType: Number(this.fieldType),
        fieldValue: this.fieldTypeText ? this.screenData.fieldValue : this.optionsSelect,
        gender: this.screenData.gender,
        addWay: this.screenData.addWay,
        roomId: this.roomIdList.join(','),
        groupNum: this.groupNum,
        employeeId: this.employeeIdList,
        startTime: this.screenData.startTime,
        endTime: this.screenData.endTime,
        businessNo: this.screenData.businessNo,
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      workContactList(params).then(res => {
        this.roomIdList = []
        this.employeeIdList = ''
        this.employees = []
        this.tableData = res.data.list
        this.pagination.total = res.data.page.total
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
    // 客户来源
    getCustomersSource () {
      customersSource().then(res => {
        this.customersSourceList = res.data
        const obj = {
          addWay: '全部',
          addWayText: '全部'
        }
        return this.customersSourceList.unshift(obj)
      })
    },
    // 用户画像
    getUserPortraitList () {
      UserPortraitList().then(res => {
        this.UserPortraitList = res.data
      })
    },
    // 用户画像下拉框
    portraitChange (value) {
      this.UserPortraitList.map(item => {
        if (item.fieldId === value) {
          this.fieldType = item.type
          this.fieldSelect = item
          if (item.typeText === '文本' || item.typeText === '手机号' || item.typeText === '邮箱' || item.typeText === '日期' || item.typeText === '数字') {
            this.fieldTypeText = true
            this.screenData.fieldValue = ''
          } else {
            this.fieldTypeText = false
            this.optionsSelect = ''
          }
        }
      })
    },
    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
      this.getTableData()
    },
    onSearch (value) {
      this.groupSearch = value
      this.getGroupChatList()
    },
    // 群聊筛选
    onSelectChange (selectedRowKeys) {
      this.roomIdList = selectedRowKeys
    },
    // 时间改变
    onTimeChange (value, dateString) {
      this.timeValue = value
      this.screenData.startTime = dateString[0]
      this.screenData.endTime = dateString[1]
    },
    onOk (value) {
    },
    // 成员选择
    peopleChange (data) {
      const arr = []
      data.map(item => {
        arr.push(item.employeeId)
      })
      this.employeeIdList = arr.join(',')
    },
    // 同步客户
    getSynContact () {
      synContact().then(res => {
        this.getTableData()
        this.$message.info('后台同步中,请稍等')
      })
    },
    // 重置
    reset () {
      this.screenData = {
        gender: 3,
        addWay: '全部',
        fieldId: 0
      }
      this.timeValue = []
      this.groupNum = 3
      this.employeeIdList = ''
      this.optionsSelect = ''
      this.employees = []
      this.searchKey = ''
      this.roomIdList = []
      this.groupSearch = ''
    },
    searchEmp (data) {
      if (data.length === 0) {
        this.employeeIdList = '空'
      } else {
        this.employeeIdList = data[0].id
      }
    }
  }
}
</script>

<style lang="less" scoped>
.footer {
  width: 150px;
  display: flex;
  float: right;
  margin-top: -50px;
}
.table-wrapper {
  margin-top: 40px;
  .btn {
    width: 100%;
    display: flex;
    justify-content: flex-end;
    margin-bottom: 20px;
    .ant-btn {
      margin-right: 10px;
    }
  }
  .news {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    img {
      width: 40px;
      height: 40px;
    }
    .weixin {
      color: #86CE76
    }
  }
}
.tbox {
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
