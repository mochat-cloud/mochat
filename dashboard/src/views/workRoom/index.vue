<template>
  <div class="customer-base">
    <a-row>
      <a-col :span="24" class="right-wrapper">
        <div :split="false" class="lists">
          <a-alert style="height: 60px" :show-icon="false" message="客户群，是由具有客户群使用权限的成员创建的外部群。成员在手机端创建群后，自动显示在后台列表中，群聊上限人数200人，包含成员+外部联系人。" banner />
        </div>
        <a-card class="up-card">
          <a-form class="form" :label-col="{ span: 7 }" :wrapper-col="{ span: 14 }">
            <a-row>
              <a-col :span="12">
                <a-form-item
                  label="选择分组">
                  <a-select v-model="active" @change="groupChange">
                    <a-select-option v-for="(d, index) in group" :value="d.workRoomGroupId" :key="index">
                      {{ d.workRoomGroupName }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>
              <a-col :span="12">
                <a-form-item
                  label="群名称：">
                  <a-input placeholder="搜索群名称" v-model="workRoomName"></a-input>
                </a-form-item>
              </a-col>
            </a-row>
            <a-row>
              <a-col :span="12">
                <a-form-item
                  label="选择群主：">
                  <a-button icon="down" class="choose-btn" @click="choosePeople"></a-button>
                </a-form-item>
              </a-col>
              <a-col :span="12">
                <a-form-item
                  label="群状态：">
                  <a-select v-model="workRoomStatus">
                    <a-select-option v-for="(d, index) in status" :value="d.value" :key="index">
                      {{ d.name }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>
            </a-row>
            <a-row>
              <a-col :span="12">
                <a-form-item
                  label="创建时间">
                  <a-range-picker class="picker" v-model="searchTime" @change="dateChange" />
                </a-form-item>
              </a-col>
              <a-col :span="8" :offset="3">
                <a-button v-permission="'/workRoom/index@search'" type="primary" class="search" @click="search">查找</a-button>
                <a-button @click="reset">重置</a-button>
              </a-col>
            </a-row>
          </a-form>
        </a-card>

        <a-card>
          <div class="top-btn">
            <a-button v-permission="'/workRoom/index@batch'" class="btn" type="primary" @click="betchAlter">批量修改分组</a-button>
            <a-button v-permission="'/workRoom/index@sync'" class="btn" type="primary" @click="syncGroup">同步群</a-button>
            <a-button v-permission="'/workRoom/index@add'" class="btn" type="primary" @click="addGroupShow = true">增加分组</a-button>
            <a-button v-permission="'/workRoom/index@edit'" type="primary" @click="alterGroup">编辑分组</a-button>
          </div>
          <a-table
            bordered
            rowKey="workRoomId"
            :pagination="pagination"
            @change="handleTableChange"
            :row-selection="{ selectedRowKeys, onChange: onSelectChange }"
            :columns="columns"
            :data-source="tableData">
            <div slot="action" slot-scope="text, record">
              <template>
                <a-button v-permission="'/workRoom/index@member'" type="link" @click="member(record)">群成员</a-button>
                <a-button v-permission="'/workRoom/statistics'" type="link" @click="statistics(record.workRoomId)">群统计</a-button>
                <a-button v-permission="'/workRoom/index@move'" type="link" @click="move(record.workRoomId)">移动分组</a-button>
              </template>
            </div>
          </a-table>
        </a-card>
      </a-col>
    </a-row>

    <!-- :confirmLoading="loading" -->
    <a-modal
      title="新增分组"
      :width="640"
      :visible="addGroupShow"
      okText="确认"
      cancelText="取消"
      @ok="addGroup"
      @cancel="addGroupShow = false"
    >
      <a-input class="add-input" v-model.trim="addGroupName" :maxLength="15" placeholder="请输入分组名（不得超过15个字符）"></a-input>
    </a-modal>

    <a-modal
      title="编辑分组"
      :width="640"
      :visible="alterGroupShow"
      okText="确认"
      cancelText="取消"
      @ok="alterGroupName"
      @cancel="alterGroupShow = false"
    >
      <a-input class="add-input" v-model.trim="alterName" :maxLength="15"></a-input>
      <template slot="footer">
        <a-button @click="alterGroupName">
          确认
        </a-button>
        <a-button type="danger" @click="deleteGroup">
          删除
        </a-button>
        <a-button type="primary" @click="alterGroupShow = false">
          取消
        </a-button>
      </template>

    </a-modal>

    <a-modal
      title="选择企业成员"
      :maskClosable="false"
      :width="700"
      :visible="choosePeopleShow"
      okText="确认"
      cancelText="取消"
      @ok="choosePeopleConfirm"
      @cancel="choosePeopleShow = false"
    >
      <Department :memberKey="employees" @change="peopleChange"></Department>

    </a-modal>

    <a-modal
      title="群成员"
      :maskClosable="false"
      :width="900"
      :visible="groupMemberShow"
      :footer="null"
      @cancel="groupMemberShow = false"
    >
      <div class="member-wrapper">
        <a-select v-model="memberSearch.status" class="member" placeholder="成员状态">
          <a-select-option value="1">
            正常
          </a-select-option>
          <a-select-option value="2">
            退群
          </a-select-option>
        </a-select>
        <a-input class="search" v-model="memberSearch.name" placeholder="搜索群成员"></a-input>
        <a-range-picker v-model="memberSearchTime" class="date" @change="memberDateChange" />
        <a-button class="find" type="primary" @click="peopleSearch">查找</a-button>
        <a-button @click="memberReset">重置</a-button>
      </div>
      <div>
        当前群成员：{{ memberNum }}人；累计退群成员：{{ outRoomNum }}人
      </div>
      <a-table
        bordered
        rowKey="workContactRoomId"
        :columns="memberClumns"
        :data-source="memberData"
        :pagination="memberPagination"
        @change="handleMemberChange">
        <div slot="name" slot-scope="text, record">
          <template>
            <div class="name-wrapper">
              <div class="img-wrapper">
                <img v-if="record.avatar" class="img" :src="record.avatar" alt="">
                <a-icon v-else type="user" class="icon"/>
              </div>
              <div class="detail">
                <div class="name">
                  {{ record.name }}
                </div>
                <a-button class="owner" v-if="record.isOwner" type="primary">群主</a-button>
              </div>
            </div>
          </template>
        </div>
        <div slot="otherRooms" slot-scope="text, record">
          <div class="otherRooms" v-for="(item, index) in record.otherRooms" :key="index">
            {{ item }}
          </div>
        </div>
      </a-table>
    </a-modal>

    <!-- 选择分组 -->
    <a-modal
      title="选择分组"
      :maskClosable="false"
      :width="800"
      :visible="changeGroupShow"
      @ok="moveGroup"
      @cancel="changeGroupShow = false"
    >
      <!-- <a-select v-model="moveGroupId" placeholder="" class="group-select">
        <a-select-option value="1">
          正常
        </a-select-option>
      </a-select> -->
      <div class="group-wrapper">
        <div
          class="group-item"
          :class="{'group-active': moveGroupId == item.workRoomGroupId}"
          @click="checkGroup(item.workRoomGroupId)"
          v-for="(item, index) in group"
          :key="index">
          {{ item.workRoomGroupName }}
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script>
import moment from 'moment'
import { mapGetters } from 'vuex'
import { workRoomGroupList, createGroup, deleteGroup, updateGroup, workRoomList, synList, batchUpdate, workContactRoom } from '@/api/workRoom'
import Department from '@/components/department'
const columns = [
  {
    title: '群名称(人数)',
    dataIndex: 'roomName',
    customRender: (test, record) => `${record.roomName} (${record.memberNum})`
  },
  {
    title: '群主',
    dataIndex: 'ownerName'
  },
  {
    title: '所属分组',
    dataIndex: 'roomGroup'
  },
  {
    title: '状态',
    dataIndex: 'statusText'
  },
  {
    title: '今日入群/退群',
    dataIndex: 'outRoomNum',
    customRender: (test, record) => `${record.inRoomNum} / ${record.outRoomNum}`
  },
  {
    title: '群公告',
    dataIndex: 'notice',
    customRender: (test, record) => `${test || '---'}`
  },
  {
    title: '创建时间',
    dataIndex: 'createTime'
  },
  {
    title: '操作',
    dataIndex: 'action',
    width: '150px',
    scopedSlots: { customRender: 'action' }
  }
]
const memberClumns = [
  {
    title: '群名称',
    dataIndex: 'name',
    scopedSlots: { customRender: 'name' }
  },
  {
    title: '入群时间',
    dataIndex: 'joinTime'
  },
  {
    title: '所在其他群',
    dataIndex: 'otherRooms',
    scopedSlots: { customRender: 'otherRooms' }
  },
  {
    title: '退群时间',
    dataIndex: 'outRoomTime'
  },
  {
    title: '入群方式',
    dataIndex: 'joinSceneText'
  }
]
export default {
  components: {
    Department
  },
  data () {
    return {
      columns: columns,
      // 群成员
      memberClumns: memberClumns,
      tableData: [],
      // 分组列表
      group: [],
      // 当前选中分组
      active: 0,
      // 新增分组
      addGroupShow: false,
      // 新增分组名称
      addGroupName: '',
      // 编辑分组
      alterGroupShow: false,
      alterGroupId: '',
      alterName: '',
      // 搜索条件 群名称
      workRoomName: '',
      // 所选群主id
      workRoomOwnerId: [],
      // 已选成员
      employees: [],
      // 群状态
      workRoomStatus: '',
      startTime: '',
      endTime: '',
      searchTime: null,
      // 群状态下拉
      status: [
        {
          name: '全部',
          value: ''
        },
        {
          name: '正常',
          value: 0
        }, {
          name: '跟进人离职',
          value: 1
        }, {
          name: '离职继承中',
          value: 2
        }, {
          name: '离职继承完成',
          value: 3
        }
      ],
      // 群名称下拉
      groupName: [],
      // 选择群主
      choosePeopleShow: false,
      // 群成员
      groupMemberShow: false,
      memberSearch: {
        status: '',
        name: '',
        startTime: '',
        endTime: ''
      },
      memberPagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      // 群成员人数
      memberNum: 0,
      // 退群人数
      outRoomNum: 0,
      memberSearchTime: null,
      memberData: [],
      // 移动分组
      changeGroupShow: false,
      moveGroupId: '',
      // 批量移动
      workRoomIds: '',
      // 单个移动id
      workRoomId: '',
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      // 列表勾选
      selectedRowKeys: []
    }
  },
  computed: {
    ...mapGetters(['corpId', 'corpName'])
  },
  created () {
    const time = this.corpId ? 0 : 2000
    setTimeout(() => {
      this.getGroupList()
      this.getTableData()
    }, time)
  },
  methods: {
    // 获取分组列表
    async getGroupList () {
      const params = {
        corpId: this.corpId,
        page: 1,
        perPage: 100
      }
      try {
        const { data: { list } } = await workRoomGroupList(params)
        this.group = [{
          workRoomGroupId: 0,
          workRoomGroupName: '未分组'
        }].concat(list)
      } catch (e) {
        console.log(e)
      }
    },
    groupChange (value) {
      const { workRoomGroupName } = this.group.find(item => {
        return item.workRoomGroupId == value
      })
      this.alterName = workRoomGroupName
      this.getTableData()
    },

    // 编辑分组
    alterGroup () {
      if (this.active == 0) {
        this.$message.warn('请选择其他分组')
        return
      }
      this.alterGroupId = this.active
      this.alterGroupShow = true
    },
    // 删除分组
    deleteGroup (workRoomGroupId, workRoomGroupName) {
      this.$confirm({
        title: '提示',
        content: `【${this.alterName}】一旦删除，归属于该分组的群都将被移至【未分组】,确认删除分组吗?`,
        onOk: () => {
          deleteGroup({ workRoomGroupId: this.active }).then(res => {
            this.getGroupList()
            this.alterGroupShow = false
            this.active = 0
          })
        },
        onCancel () {}
      })
    },
    // 添加分组
    async addGroup () {
      if (!this.addGroupName) {
        this.$message.error('请输入分组名称')
        return
      }
      const flag = this.group.find(item => {
        return item.workRoomGroupName == this.addGroupName
      })
      if (flag || this.addGroupName == '未分组') {
        this.$message.error('不能重复添加分组')
        return
      }
      const param = {
        corpId: this.corpId,
        workRoomGroupName: this.addGroupName
      }
      try {
        await createGroup(param)
        this.addGroupShow = false
        this.addGroupName = ''
        this.getGroupList()
      } catch (e) {
        console.log(e)
      }
    },
    // 修改分组
    async alterGroupName () {
      if (!this.alterName) {
        this.$message.error('请输入分组名称')
        return
      }
      const params = {
        workRoomGroupId: this.alterGroupId,
        workRoomGroupName: this.alterName
      }
      try {
        await updateGroup(params)
        this.getGroupList()
        this.alterGroupShow = false
      } catch (e) {
        console.log(e)
      }
    },
    async getTableData () {
      const params = {
        roomGroupId: this.active,
        workRoomName: this.workRoomName,
        workRoomOwnerId: this.workRoomOwnerId.join(','),
        workRoomStatus: this.workRoomStatus,
        startTime: this.startTime,
        endTime: this.endTime,
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      try {
        const { data: { page: { total }, list } } = await workRoomList(this.handleParam(params))
        this.pagination.total = total
        this.tableData = list
      } catch (e) {
        console.log(e)
      }
    },
    handleParam (obj) {
      for (const item in obj) {
        const data = obj[item]
        if (!data && data !== 0) {
          delete obj[item]
        }
      }
      return obj
    },
    search () {
      this.getTableData()
    },
    reset () {
      this.active = 0
      this.workRoomName = ''
      this.workRoomOwnerId = []
      this.employees = []
      this.workRoomStatus = ''
      this.startTime = ''
      this.endTime = ''
      this.searchTime = null
    },
    // 批量修改分组
    betchAlter () {
      if (this.selectedRowKeys.length == 0) {
        this.$message.warn('请选择需要修改分组的群')
        return
      }
      this.workRoomIds = this.selectedRowKeys.join(',')
      this.changeGroupShow = true
    },
    // 同步群
    async syncGroup () {
      try {
        await synList({ corpId: this.corpId })
        await this.getTableData()
        this.$message.success('同步成功')
      } catch (e) {
        console.log(e)
      }
    },
    // 搜索群名称
    async handleSearch (value) {
      const params = {
        roomGroupId: this.active,
        workRoomName: value,
        page: 1,
        perPage: 50
      }
      try {
        const { data: { list } } = await workRoomList(params)
        this.groupName = list
      } catch (e) {
        console.log(e)
      }
    },
    // 群名称更改
    handleChange (value) {
      this.workRoomName = value
    },
    // 选择群主
    choosePeople () {
      this.choosePeopleShow = true
    },
    // 选择群主人员更改
    peopleChange (data) {
      this.employees = data
    },
    // 选择群主 确定
    choosePeopleConfirm () {
      this.workRoomOwnerId = this.employees.map(item => {
        return item.employeeId
      })
      this.choosePeopleShow = false
    },
    // 列表搜索日期
    dateChange (date, dateString) {
      this.startTime = dateString[0]
      this.endTime = dateString[1]
      if (!dateString[0] || !dateString[1]) {
        this.searchTime = null
      } else {
        this.searchTime = [moment(this.startTime, 'YYYY-MM-DD'), moment(this.endTime, 'YYYY-MM-DD')]
      }
    },
    // 群成员搜索日期
    memberDateChange (date, dateString) {
      this.memberSearch.startTime = dateString[0]
      this.memberSearch.endTime = dateString[1]
      if (!dateString[0] || !dateString[1]) {
        this.memberSearchTime = null
      } else {
        this.memberSearchTime = [moment(this.memberSearch.startTime, 'YYYY-MM-DD'), moment(this.memberSearch.endTime, 'YYYY-MM-DD')]
      }
    },
    // 群成员
    member (record) {
      this.workRoomId = record.workRoomId
      this.groupMemberShow = true
      this.peopleSearch()
    },
    // 成员搜索
    async peopleSearch () {
      const param = {
        ...this.memberSearch,
        workRoomId: this.workRoomId,
        page: this.memberPagination.current,
        perPage: this.memberPagination.pageSize
      }
      try {
        const { data: { memberNum, outRoomNum, page: { total }, list } } = await workContactRoom(this.handleParam(param))
        this.memberNum = memberNum || 0
        this.outRoomNum = outRoomNum || 0
        this.memberPagination.total = total || 0
        this.memberData = list || []
      } catch (e) {
        console.log(e)
      }
    },
    // 重置
    memberReset () {
      this.memberSearch.status = ''
      this.memberSearch.name = ''
      this.memberSearch.startTime = ''
      this.memberSearch.endTime = ''
      this.memberSearchTime = null
    },
    // 群统计
    statistics (workRoomId) {
      this.$router.push({ path: '/workRoom/statistics', query: { workRoomId } })
    },
    // 选择移动分组
    checkGroup (workRoomGroupId) {
      this.moveGroupId = workRoomGroupId
    },
    move (workRoomId) {
      this.moveGroupId = this.active
      this.workRoomIds = String(workRoomId)
      this.changeGroupShow = true
    },
    // 移动分组
    async moveGroup () {
      const params = {
        workRoomGroupId: this.moveGroupId,
        workRoomIds: this.workRoomIds
      }
      try {
        await batchUpdate(params)
        this.changeGroupShow = false
        this.$message.success('修改成功')
        this.selectedRowKeys = []
        this.getTableData()
      } catch (e) {
        console.log(e)
      }
    },
    // 页数
    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
      this.getTableData()
    },
    // 多选
    onSelectChange (selectedRowKeys, selectedRows) {
      this.selectedRowKeys = selectedRowKeys
    },
    // 成员列表页数变化
    handleMemberChange ({ current, pageSize }) {
      this.memberPagination.current = current
      this.memberPagination.pageSize = pageSize
      this.peopleSearch()
    }
  }
}
</script>

<style lang="less" scoped>
.customer-base {
    height: 100%;
    .choose-btn {
      width: 155px;
      margin-top: 5px;
      padding-right: 5px;
      text-align: right;
      color: rgba(0, 0, 0, 0.3);
    }

    .right-wrapper {
      padding-left: 15px;
    }
    .lists{
      margin-bottom: 20px;
    }
    .up-card{
      margin-bottom: 20px;
    }
    .form{
      margin-bottom: 20px;
    }
    .search{
      margin-right: 20px;
    }

    .top-btn{
      display: flex;
      justify-content: flex-end;
      margin-bottom: 10px;
      .btn{
        margin-right: 20px;
      }
    }
  }

  .member-wrapper{
    display: flex;
    margin: 0 20px 20px 0;
    .member{
      width: 200px;
      margin-right:20px
    }
    .search{
      width: 300px;
      margin-right: 20px
    }
    .date{
      width: 300px;
      margin-right: 20px
    }
    .find{
      margin-right: 20px
    }
  }
  .name-wrapper{
    display: flex;
    flex-direction: row;
    .img-wrapper {
      flex: 0 0 50px;
      width: 50px;
      height: 50px;
      margin-right: 10px;
    }
    .icon {
      font-size: 35px;
    }
    .img{
      width: 50px;
      height: 50px;
    }
    .detail{
      flex: 1;
      .name{
        text-align: left;
        margin-bottom: 10px;
      }
      .owner{
        width: 80px;
        height: 22px
      }
    }
  }
  .group-select{
    width:200px
  }
  .group-wrapper{
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    .group-item{
      flex: 0 0 12%;
      display: flex;
      justify-content: center;
      align-items: center;
      border: 1px solid rgba(0, 0, 0, 0.3);
      height: 40px;
      margin-right: 15px;
      margin-bottom: 15px;
      cursor: pointer;
    }
    .group-active{
      color: #fff;
      background: #1890ff;
    }
  }
</style>
