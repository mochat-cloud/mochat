<template>
  <div class="white-bg-module">
    <a-tabs v-model="tabPane">
      <a-tab-pane :key="1" tab="潜在客户">
        <div class="radios-btns">
          <div class="radios">
            筛选：
            <a-radio-group v-model="askClientTable.status" @change="selectRadio">
              <a-radio :value="4"> 全部</a-radio>
              <a-radio :value="0"> 未分配</a-radio>
              <a-radio :value="1"> 待添加</a-radio>
              <a-radio :value="2"> 待通过</a-radio>
              <a-radio :value="3"> 已添加</a-radio>
            </a-radio-group>
          </div>
          <div class="btns">
            <a-button type="primary" @click="visible = true" icon="plus"> 导入客户</a-button>
            <a-button type="primary" icon="profile" @click="$router.push({ path: '/contactBatchAdd/importIndex' })"> 导入记录</a-button>
            <a-button type="primary" @click="distributionBtn">分配</a-button>
            <a-button type="primary" icon="setting" @click="openSetup"> 设置</a-button>
          </div>
        </div>
        <div class="search-table">
          <div class="search">
            <h5>共{{ clientTable.data.length }}个客户</h5>
            <div class="an-form">
              <a-input-search
                placeholder="请输入要搜索电话 / 备注名"
                style="width: 240px"
                @search="searchClientData"
                v-model="askClientTable.searchKey"
                :allowClear="true"
                @change="cleanInput"
              />
            </div>
          </div>
          <a-table
            bordered
            class="table"
            :columns="clientTable.columns"
            :data-source="clientTable.data"
            :row-selection="{selectedRowKeys:clientTable.rowSelection,onChange: onSelectChange}"
          >
            <div slot="tags" slot-scope="text">
              <a-tag v-for="(item,index) in text" :key="index">{{ item.name }}</a-tag>
            </div>
            <div slot="status" slot-scope="text">
              <a-tag v-if="text==0" color="gray">未分配</a-tag>
              <a-tag v-if="text==1" color="orange">待添加</a-tag>
              <a-tag v-if="text==2" color="cyan">待通过</a-tag>
              <a-tag v-if="text==3" color="green">已添加</a-tag>
            </div>
            <div slot="allotEmployee" slot-scope="text">
              <a-tag>{{ text.name }}</a-tag>
            </div>
            <div slot="operation" slot-scope="text,record">
              <a @click="remindBtn(record)">提醒</a>
              <a-divider type="vertical" />
              <a @click="deleteRow(record)">删除</a>
            </div>
          </a-table>
        </div>
      </a-tab-pane>
      <a-tab-pane :key="2" tab="数据统计" force-render>
        <a-row :gutter="0" type="flex" justify="space-between">
          <a-col :span="24">
            <div class="child_module">
              <div class="child_title">总览</div>
              <a-row class="total_module" type="flex" :gutter="20" justify="space-between">
                <a-col class="item item_hr" :span="4">
                  <span>{{ dataStatistic.contactNum }}</span>
                  <p>导入总客户数</p>
                  <a @click="customerDataDetails(4)">详情</a>
                </a-col>
                <a-col class="item" :span="4">
                  <span>{{ dataStatistic.pendingNum }}</span>
                  <p>待分配客户数</p>
                  <a @click="customerDataDetails(0)">详情</a>
                </a-col>
                <a-col class="item item_hr" :span="4">
                  <span>{{ dataStatistic.toAddNum }}</span>
                  <p>待添加客户数</p>
                  <a @click="customerDataDetails(1)">详情</a>
                </a-col>
                <a-col class="item item_hr" :span="4">
                  <span>{{ dataStatistic.toPendingNum }}</span>
                  <p>待通过客户数</p>
                  <a @click="customerDataDetails(2)">详情</a>
                </a-col>
                <a-col class="item item_hr" :span="4">
                  <span>{{ dataStatistic.passedNum }}</span>
                  <p>已添加总客户数</p>
                  <a @click="customerDataDetails(3)">详情</a>
                </a-col>
                <a-col class="item" :span="4">
                  <span>{{ dataStatistic.completion }}%</span>
                  <p>添加完成率</p>
                </a-col>
              </a-row>
            </div>
          </a-col>
        </a-row>
        <div class="screen_row">
          <div>自定义时间：<a-range-picker v-model="screenTime" @change="changeTime" valueFormat="YYYY-MM-DD" /></div>
          <div>选择成员：
            <a-select
              mode="multiple"
              style="min-width: 200px;max-width: 570px;margin-left: -5px;"
              placeholder="请选择成员"
              v-model="screenMember"
              @change="changeMember"
            >
              <a-select-option v-for="(item,index) in employeeData" :key="index" :value="item.id">{{ item.name }}</a-select-option>
            </a-select>
          </div>
          <a-button style="margin-left: 25px;" @click="resetBtn">重置</a-button>
        </div>
        <div class="add-table">
          <a-table :columns="tableData.columns" :data-source="tableData.data">
            <div slot="completion" slot-scope="text">
              {{ text }}%
            </div>
            <!--            <div slot="operation" slot-scope="text,record">-->
            <!--              <a @click="$router.push('/autoTag/joinRoomShow?idRow='+record.id)">详情</a>-->
            <!--            </div>-->
          </a-table>
        </div>
      </a-tab-pane>
    </a-tabs>
    <!--    设置-->
    <a-modal
      title="设置"
      :footer="null"
      width="600px"
      :visible="settingVisible"
      dialogClass="setting-module"
      @cancel="settingVisible = false"
      :maskClosable="false"
    >
      <a-tabs type="card" default-active-key="1">
        <a-tab-pane key="1" class="" tab="自动提醒">
          <div style="font-weight: bold;font-size: 15px;">未分配提醒</div>
          <div>
            <div class="num-date">
              超过
              <a-input-number style="width: 67px;" v-model="askSetUpData.pendingTimeOut" :min="1" />
              天，<span class="b">客户未分配跟进成员</span>，次日
              <a-time-picker
                format="HH:mm:ss"
                style="width: 120px"
                v-model="askSetUpData.pendingReminderTime"
                valueFormat="HH:mm:ss"
              />
              提醒管理员分配
            </div>
            <div>
              选择接收提醒管理员:
              <a-select
                style="width: 300px"
                placeholder="选择管理员"
                v-model="askSetUpData.pendingLeaderId"
              >
                <a-select-option v-for="(item,index) in employeeData" :key="index" :value="item.employeeId">
                  {{ item.name }}
                </a-select-option>
              </a-select>
            </div>
          </div>
          <div class="hr"></div>
          <div style="font-weight: bold;font-size: 15px;">成员未添加客户提醒</div>
          <div class="num-date">
            超过
            <a-input-number style="width: 67px" v-model="askSetUpData.undoneTimeOut" :min="1" />
            天，<span class="b">成员未添加客户</span>，次日
            <a-time-picker
              format="HH:mm:ss"
              style="width: 120px"
              valueFormat="HH:mm:ss"
              v-model="askSetUpData.undoneReminderTime"
            />
            提醒成员分配
          </div>
          <div class="hr"></div>
          <div style="font-weight: bold;font-size: 15px;">自动回收</div>
          <div class="num-date">
            超过
            <a-input-number style="width: 67px;" v-model="askSetUpData.recycleTimeOut" :min="1" />
            天，<span class="b">客户未通过好友</span>， 自动转移到待分配
          </div>
          <div class="btns">
            <a-button @click="cancelBtn">取消</a-button>
            <a-button type="primary" @click="saveSetUp"> 保存</a-button>
          </div>
        </a-tab-pane>
        <!--        <a-tab-pane key="2" tab="短信提醒">-->
        <!--          <div class="img"><img src="@/assets/batch-add-user-sms.png" /></div>-->
        <!--          <div class="open-btn">-->
        <!--            <a-button type="primary"> 开启短信功能</a-button>-->
        <!--          </div>-->
        <!--          <div class="title-info">-->
        <!--            <h2>应用场景</h2>-->
        <!--            <p>开启短信提醒后，员工点击复制手机号，将自动给客户发送一条短信，提高通过率。</p>-->
        <!--          </div>-->
        <!--        </a-tab-pane>-->
      </a-tabs>
    </a-modal>
    <!--    上传表格-->
    <a-modal
      title="上传表格"
      :footer="null"
      :visible="visible"
      dialogClass="uploadExcel"
      @cancel="visible = false"
      :maskClosable="false">
      <div class="text">
        请下载模板后输入手机号上传，可批量复制手机号至模板内，若输入内容有重复手机号或空行将会自动过滤
      </div>
      <div class="download-template">点击下载<a :href="downloadExcelUrl" target="_blank">Excel模板</a></div>
      <a-form-model
        class="upload-form"
        ref="ruleForm"
        :model="form"
        :rules="rules"
        :label-col="labelCol"
        :wrapper-col="wrapperCol"
      >
        <a-form-model-item label="选择表格">
          <div class="upload-box">
            <div class="btn">
              <a-button>
                <a-icon type="plus" />
                添加文件
              </a-button>
            </div>
            <input type="file" @change="uploadChange" accept=".xls,.xlsx" style="cursor: pointer;">
          </div>
          <div style="margin-top: 15px;" v-if="uploadState">
            <div style="font-size: 16px;margin-bottom: 5px;">{{ xlsName }}</div>
            <img src="../../assets/xls.png" alt="" style="width: 50px;height: 50px;">
          </div>
        </a-form-model-item>
        <a-form-model-item label="选择员工" prop="delivery">
          <a-button @click="selectEmployee">
            选择员工
          </a-button>
          <div class="ml16">
            <a-tag v-for="v in leadClientData.allotEmployee" :key="v.id">
              {{ v.name }}
            </a-tag>
          </div>
        </a-form-model-item>
        <a-form-model-item label="客户标签">
          <div @click="showModal" class="choiceAdmin">
            <span class="operationTips" v-if="clientTagArr.length==0">请选择标签</span>
            <a-tag v-for="(obj,idx) in clientTagArr" :key="idx">
              {{ obj.name }}
              <a-icon type="close" @click.stop="delTagsArr(idx)" />
            </a-tag>
          </div>
        </a-form-model-item>
        <a-form-model-item :wrapper-col="{ span: 14, offset: 4 }">
          <a-button type="primary" @click="importContactClick">保存</a-button>
          <a-button style="margin-left: 10px" @click="visible = false"> 取消</a-button>
        </a-form-model-item>
      </a-form-model>
    </a-modal>
    <!--    分配员工-->
    <a-modal v-model="allocationPopup" title="分配" :maskClosable="false" :footer="null" width="350px">
      <div style="display: flex;justify-content:center;">
        <div style="line-height: 30px;">分配员工：</div>
        <a-select
          style="width: 200px"
          placeholder="选择分配员工"
          v-model="assignEmployeesId"
        >
          <a-select-option v-for="(item,index) in employeeData" :key="index" :value="item.employeeId">
            {{ item.name }}
          </a-select-option>
        </a-select>
      </div>
      <div style="display: flex;justify-content:center;margin-top: 15px;">
        <a-button style="margin-right: 30px;" @click="cancelAssignment">取消</a-button>
        <a-button type="primary" @click="sureAssigned">确定</a-button>
      </div>
    </a-modal>
    <addlableIndex ref="childRef" @choiceTagsArr="acceptArray"></addlableIndex>
    <selectMember ref="selectMember" @change="(e)=> leadClientData.allotEmployee = e" />
  </div>
</template>
<script>
import addlableIndex from '@/components/addlabel/index'
import selectMember from '@/components/Select/member'
import MaUpload from '@/components/MaUpload'
// eslint-disable-next-line no-unused-vars
import { contactList, dataStatistic, department, getSetting, importContact, updateSetting, contactDel, allot, remindApi } from '@/api/contactBatchAdd'
import storage from 'store'

export default {
  components: {
    selectMember, MaUpload, addlableIndex
  },
  computed: {
    headers () {
      const token = storage.get('ACCESS_TOKEN')
      return {
        Accept: `application/json`,
        Authorization: token
      }
    }
  },
  data () {
    return {
      // 上传状态
      uploadState: false,
      // 切换表格
      tabPane: 1,
      // 客户选择的标签
      clientTagArr: [],
      visible: false,
      settingVisible: false,
      labelCol: { span: 4 },
      wrapperCol: { span: 14 },
      form: {
        excel: '',
        region: ''
      },
      rules: {
        excel: [{ required: true, message: '请选择表格', trigger: 'change' }]
      },
      //  最新
      askClientTable: {
        // 分配状态
        status: 4,
        // 搜索关键字
        searchKey: '',
        // 指定导入批次
        recordId: ''
      },
      //  潜在客户表格
      clientTable: {
        columns: [
          {
            title: '电话号码',
            dataIndex: 'phone'
          },
          {
            title: '客服备注名字',
            dataIndex: 'remark'
          },
          {
            title: '导入时间',
            dataIndex: 'uploadAt'
          },
          {
            title: '客户标签',
            dataIndex: 'tags',
            scopedSlots: { customRender: 'tags' }
          },
          {
            title: '添加状态',
            dataIndex: 'status',
            scopedSlots: { customRender: 'status' }
          },
          {
            title: '添加时间',
            dataIndex: 'addAt'
          },
          {
            title: '分配员工',
            dataIndex: 'allotEmployee',
            scopedSlots: { customRender: 'allotEmployee' }
          },
          {
            title: '分配次数',
            dataIndex: 'allotNum'
          },
          {
            title: '操作',
            dataIndex: 'operation',
            scopedSlots: { customRender: 'operation' }
          }
        ],
        data: [],
        rowSelection: []
      },
      //  设置请求数据
      askSetUpData: {
        // 待处理客户提醒开关
        pendingStatus: 1,
        // 天数
        pendingTimeOut: 1,
        //  提醒时间
        pendingReminderTime: '',
        //  管理员id
        pendingLeaderId: '',
        // 成员未添加客户提醒开关
        undoneStatus: 1,
        //  天数
        undoneTimeOut: 1,
        //  提醒时间
        undoneReminderTime: 1,
        // 回收客户开关
        recycleStatus: 1,
        // 天数
        recycleTimeOut: ''
      },
      //  员工数据
      employeeData: [],
      //  数据统计--仪表盘统计数据
      dataStatistic: {},
      xlsName: '',
      //  导入客户数据
      leadClientData: {
        title: '',
        tags: [],
        allotEmployee: [],
        file: {}
      },
      //  数据统计表格
      tableData: {
        columns: [
          {
            title: '成员',
            dataIndex: 'name'
          },
          {
            title: '分配客户数',
            dataIndex: 'allotNum'
          },
          {
            title: '待添加客户数',
            dataIndex: 'toAddNum'
          },
          {
            title: '待通过客户数',
            dataIndex: 'pendingNum'
          },
          {
            title: '已添加客户数',
            dataIndex: 'passedNum'
          },
          {
            title: '回收客户数',
            dataIndex: 'recycleNum'
          },
          {
            title: '添加完成率',
            dataIndex: 'completion',
            scopedSlots: { customRender: 'completion' }
          }
        ],
        data: []
      },
      //  分配弹窗
      allocationPopup: false,
      //  分配员工id
      assignEmployeesId: [],
      //  分配客户id
      assignCustomerId: '',
      // 筛选成员
      screenMember: [],
      // 筛选时间
      screenTime: [],
      downloadExcelUrl: process.env.VUE_APP_API_BASE_URL + '/static/Excel%E6%89%B9%E9%87%8F%E6%B7%BB%E5%8A%A0%E5%A5%BD%E5%8F%8B%E6%A8%A1%E6%9D%BF.xlsx'
    }
  },
  created () {
  },
  mounted () {
    // 获取客户表格数据
    this.getClientTableData()
    //  获取数据统计
    this.getStatisticsData()
    //  获取员工数据
    this.getStaffData()
  },
  methods: {
    // 重置按钮
    resetBtn () {
      this.screenTime = []
      this.screenMember = []
      this.getStatisticsData()
    },
    // 选择筛选时间
    changeTime () {
      this.getStatisticsData()
    },
    changeMember () {
      this.getStatisticsData()
    },
    // 详情
    customerDataDetails (state) {
      this.tabPane = 1
      this.askClientTable.status = state
      this.getClientTableData()
    },
    // 提醒
    remindBtn (record) {
      remindApi({
        employeeId: record.employeeId,
        recordId: record.recordId
      }).then((res) => {
        this.$message.success('提醒成功')
      })
    },
    // 选择员工
    selectEmployee () {
      // this.leadClientData.allotEmployee  setSelect
      // this.$refs.selectMember.show(this.leadClientData.allotEmployee)
      this.$refs.selectMember.setSelect(this.leadClientData.allotEmployee)
    },
    // 取消分配
    cancelAssignment () {
      this.allocationPopup = false
      this.assignEmployeesId = ''
    },
    // 分配
    distributionBtn () {
      if (this.clientTable.rowSelection.length == 0) {
        this.$message.warning('分配客户不能为空')
        return false
      }
      this.allocationPopup = true
    },
    onSelectChange (e) {
      this.clientTable.rowSelection = e
    },
    // 确定分配
    sureAssigned () {
      if (this.assignEmployeesId == '') {
        this.$message.warning('请选择要分配的员工')
        return false
      }
      const Ids = []
      this.clientTable.rowSelection.forEach((item) => {
        Ids[item] = this.clientTable.data[item].id
      })
      allot({
        id: Ids,
        employeeId: this.assignEmployeesId
      }).then((res) => {
        this.$message.success('分配成功')
        this.allocationPopup = false
        this.assignEmployeesId = ''
        this.clientTable.rowSelection = []
        this.getClientTableData()
      })
    },
    // 删除
    deleteRow (row) {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否删除',
        okText: '删除',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          contactDel({
            id: row.id
          }).then((res) => {
            that.getClientTableData()
            that.$message.success('删除成功')
          })
        }
      })
    },
    // 上传
    uploadChange (e) {
      if (e.target.files[0] != undefined) {
        this.uploadState = true
        this.leadClientData.file = e.target.files[0]
        this.xlsName = e.target.files[0].name
      }
    },
    // 导入
    importContactClick () {
      if (!this.uploadState) {
        this.$message.error('表格未上传')
        return false
      }
      if (!this.leadClientData.allotEmployee.length) {
        this.$message.error('员工未选择')
        return false
      }
      const allotEmployee = []
      this.leadClientData.allotEmployee.forEach((item, index) => {
        allotEmployee[index] = item.employeeId
      })
      const tags = []
      if (this.clientTagArr.length != 0) {
        this.clientTagArr.forEach((item, index) => {
          tags[index] = item.id
        })
      }
      const data = new FormData()
      data.append('title', '批量加好友')
      data.append('tags', JSON.stringify(tags))
      data.append('allotEmployee', JSON.stringify(allotEmployee))
      data.append('file', this.leadClientData.file, this.leadClientData.file.name)
      importContact(data).then(res => {
        if (res.code === 200) {
          this.$message.success('导入成功')
          this.visible = false
          this.getClientTableData()
          this.uploadState = false
          this.clientTagArr = []
          this.leadClientData = {}
        }
      })
    },
    // 数据统计
    getStatisticsData () {
      dataStatistic({
        employeeId: this.screenMember,
        startTime: this.screenTime[0],
        endTime: this.screenTime[1]
      }).then((res) => {
        this.dataStatistic = res.data.dataStatistic
        this.tableData.data = res.data.employees.data
      })
    },
    // 保存设置
    saveSetUp () {
    // 待处理客户提醒
      if (this.askSetUpData.pendingTimeOut == '') {
        this.$message.warning('待处理客户提醒天数不能为空')
        return false
      }
      if (this.askSetUpData.pendingReminderTime == '') {
        this.$message.warning('待处理客户提醒时间不能为空')
        return false
      }
      if (this.askSetUpData.pendingLeaderId == '') {
        this.$message.warning('接收提醒管理员不能为空')
        return false
      }
      // 成员未添加客户
      if (this.askSetUpData.undoneTimeOut == '') {
        this.$message.warning('成员未添加客户提醒天数不能为空')
        return false
      }
      if (this.askSetUpData.undoneReminderTime == '') {
        this.$message.warning('成员未添加客户提醒时间不能为空')
        return false
      }
      // 回收客户
      if (this.askSetUpData.recycleTimeOut == '') {
        this.$message.warning('回收客户提醒天数不能为空')
        return false
      }
      updateSetting(this.askSetUpData).then((res) => {
        this.$message.success('设置成功')
        this.settingVisible = false
        this.askSetUpData = {
          pendingStatus: 1,
          undoneStatus: 1,
          recycleStatus: 1
        }
      })
    },
    cancelBtn () {
      this.settingVisible = false
    },
    // 获取员工数据
    getStaffData () {
      department().then((res) => {
        this.employeeData = res.data.employee
      })
    },
    // 设置
    openSetup () {
      getSetting().then((res) => {
        if (JSON.stringify(res.data) != '{}') {
          this.askSetUpData = res.data
        }
        this.settingVisible = true
      })
    },
    // 清除输入框
    cleanInput () {
      if (this.askClientTable.searchKey == '') {
        this.getClientTableData()
      }
    },
    // 搜索潜在客户
    searchClientData () {
      this.getClientTableData()
    },
    // 潜在客户筛选
    selectRadio (e) {
      this.getClientTableData()
    },
    // 表格数据
    getClientTableData () {
      const params = {
        // 分配状态
        status: '',
        // 搜索关键字
        searchKey: this.askClientTable.searchKey,
        // 指定导入批次
        recordId: this.askClientTable.recordId
      }
      if (this.askClientTable.status == 4) {
        params.status = ''
      } else {
        params.status = this.askClientTable.status
      }
      contactList(params).then((res) => {
        this.clientTable.data = res.data.data
      })
    },
    showModal () {
      this.$refs.childRef.show(this.clientTagArr)
    },
    delTagsArr (idx) {
      this.clientTagArr.splice(idx, 1)
    },
    // 接收标签组件传值
    acceptArray (e) {
      if (e) {
        this.clientTagArr = e
      }
    }
  }
}
</script>

<style lang="less" scoped>
/deep/ .ant-modal-header{
  text-align: center;
  font-weight: bold;
}

.upload-box{
  position: relative;

  input[type="file"]{
    width: 100px;
    height: 28px;
    opacity: 0;
    position: absolute;
    left: 0;
    top: 0;
  }
}

.choiceAdmin{
  width: 280px;
  min-height: 32px;
  background: #fff;
  border-radius: 4px;
  border: 1px solid #dcdfe6;
  cursor: pointer;
  padding-left: 5px;
  margin-right: 8px;
  line-height: 30px;
}
.operationTips{
  color: #b2b2b2;
}
.white-bg-module {
  background-color: #fff;

  .radios-btns {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 00px 20px;

    .btns {
      .ant-btn {
        margin-left: 15px;
      }
    }
  }

  .search-table {
    background-color: #fff;
    box-shadow: 0px 0px 10px #ddd;
    margin: 20px;
    padding: 15px;

    .search {
      display: flex;
      justify-content: space-between;
      align-items: center;

      h5 {
        font-weight: bold;
        margin: 0px;
      }

      .ant-input-search {
        margin-left: 15px;
      }
    }

    .table {
      margin-top: 20px;
    }
  }

  .child_module {
    padding: 0px 25px;

    .child_title {
      font-size: 14px;
      margin-left: -10px;
      margin-bottom: 15px;
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
.screen_row{
  margin-top: 20px;
  display: flex;
  div{
    margin-left: 15px;
  }
}
  .add-table {
    padding: 0px 15px;
    margin-top: 20px;

    .title {
      padding: 15px 0px 20px;
      display: flex;
      font-weight: bold;
      align-items: center;
      justify-content: space-between;
    }

    .search {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;

      .ant-select {
        width: 120px;
        margin-right: 15px;
      }

      .ant-calendar-picker {
        margin-right: 15px;
      }
    }
  }
}

.uploadExcel {
  .text {
    background: #f0f8ff;
    border-radius: 2px;
    padding: 8px 17px 7px;
    font-size: 12px;
    color: rgba(0, 0, 0, 0.44);
    line-height: 17px;
  }

  .download-template {
    margin-top: 15px;

    a {
      padding-left: 4px;
      color: #1890ff;
    }
  }

  .upload-form {
    margin-top: 15px;

    .tag_client {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-right: 15px;
      border: 1px solid #d9d9d9;
      background-color: #fff;
      cursor: pointer;
      flex-wrap: nowrap;
      height: 40px;

      .icon {
        font-size: 14px;
        color: #999;
        flex-shrink: 0;
      }

      .tag {
        width: 100%;
        flex-shrink: 1;
        padding: 0px 15px;
        overflow: hidden;
        display: flex;
        cursor: pointer;
        justify-content: flex-start;
      }
    }
  }
}

.setting-module {
  .num-date {
    display: flex;
    align-items: center;
    margin: 15px 0px;

    .ant-input,
    .ant-time-picker {
      margin: 0px 5px;
    }

    .b {
      font-weight: bold;
    }
  }

  .hr {
    margin: 20px 0px;
    border-bottom: 1px dashed #e8e8e8;
  }

  .btns {
    display: flex;
    justify-content: flex-end;

    .ant-btn {
      margin-left: 10px;
    }
  }

  .img {
    text-align: center;

    img {
      width: 200px;
    }
  }

  .open-btn {
    text-align: center;
    padding: 20px 0px;
  }

  .title-info {
    height: 81px;
    background: #f9f9f9;
    border-radius: 2px;
    padding: 14px 16px;
    font-size: 13px;
    font-family: PingFangSC-Regular, PingFang SC;
    font-weight: 400;
    color: #000;
    margin: 10px auto 0px;
    line-height: 18px;

    h2 {
      font-size: 16px;
      font-weight: bold;
    }
  }
}
</style>
