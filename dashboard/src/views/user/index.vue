<template>
  <div>
    <a-card>
      <div class="table-page-search-wrapper">
        <a-form>
          <a-row :gutter="16">
            <a-col :lg="8">
              <a-form-item
                label="手机号码："
                :labelCol="{lg: {span: 7} }"
                :wrapperCol="{lg: {span: 14} }">
                <a-input v-model="screenData.phone"></a-input>
              </a-form-item>
            </a-col>
            <a-col :lg="8">
              <a-form-item
                label="状态："
                :labelCol="{lg: {span: 7} }"
                :wrapperCol="{lg: {span: 14} }">
                <a-select v-model="screenData.status">
                  <a-select-option v-for="item in statusList" :key="item.value">
                    {{ item.label }}
                  </a-select-option>
                </a-select>
              </a-form-item>
            </a-col>
            <a-col :lg="8">
              <a-form-item
                label=""
                :labelCol="{lg: {span: 7} }"
                :wrapperCol="{lg: {span: 14} }">
                <a-button v-permission="'/user/index@search'" type="primary" @click="search">查询</a-button>
                <a-button style="marginLeft:5px" @click="() => { this.screenData = {} }">重置</a-button>
              </a-form-item>
            </a-col>
          </a-row>
        </a-form>
        <div class="alter">
          <a-icon theme="twoTone" type="question-circle" />
          <span>已启用<span style="color: #4BA9FF">{{ usingNum }}</span> 项 未启用:<span style="color:#757E82">{{ forbiddenNum }}</span>人</span>
          <a-button type="link" @click="statusModal">启用</a-button>
          <a-modal
            :visible="statusModalDis"
            @cancel="() => {this.statusModalDis = false}"
          >
            <p>是否确认启用</p>
            <template slot="footer">
              <a-button key="back" @click="() => {this.statusModalDis = false}">
                取消
              </a-button>
              <a-button @click="status" key="submit" type="primary">
                确定
              </a-button>
            </template>
          </a-modal>
        </div>
      </div>
      <div class="table-wrapper">
        <a-button v-permission="'/user/index@add'" type="primary" @click="addPopupBtn"> + 添加</a-button>
        <a-table
          :columns="columns"
          :data-source="tableData"
          :rowKey="record => record.userId"
          :row-selection="{ selectedRowKeys: userIdList, onChange: onSelectChange }"
          :pagination="pagination"
          @change="handleTableChange"
        >
          <div slot="department" slot-scope="text, record">
            <div v-for="item in record.department" :key="item.departmentId">{{ item.departmentName }}</div>
          </div>
          <div slot="action" slot-scope="text, record">
            <a @click="editSubManagement(record.userId)">修改</a>
            <a-divider type="vertical" />
            <a @click="rowResetBtn(record)">重置密码</a>
          </div>
        </a-table>
      </div>
      <!--      添加弹窗-->
      <a-modal
        :visible="modalVisible"
        :maskClosable="false"
        @cancel="reset"
        title="基础信息">
        <a-form-model ref="ruleForm" :model="userData" :rules="rules" :label-col="{ span: 6 }" :wrapper-col="{ span: 16 }">
          <a-form-model-item label="员工姓名:" prop="userName">
            <a-input v-model="userData.userName"></a-input>
          </a-form-model-item>
          <a-form-model-item label="手机号码：" prop="phone" style="position: relative;">
            <a-input v-model="userData.phone" @blur="phoneSelect"></a-input>
            <span style="position: absolute;left: 5px;top: 20px;color: #a9a9a9;">输入的手机号需与企业微信中员工的手机号一致</span>
          </a-form-model-item>
          <a-form-model-item label="密码：" prop="password" v-if="addShowPopup">
            <a-input v-model="userData.password" type="password"></a-input>
          </a-form-model-item>
          <a-form-model-item label="确认密码：" prop="confirmPass" v-if="addShowPopup">
            <a-input v-model="userData.confirmPass" type="password"></a-input>
          </a-form-model-item>
          <a-form-model-item label="性别：" prop="gender">
            <a-radio-group v-model="userData.gender">
              <a-radio :value="1">
                男
              </a-radio>
              <a-radio :value="2">
                女
              </a-radio>
            </a-radio-group>
          </a-form-model-item>
          <a-form-model-item label="状态" prop="status">
            <a-radio-group v-model="userData.status">
              <a-radio :value="1">
                正常
              </a-radio>
              <a-radio :value="2">
                禁用
              </a-radio>
              <a-radio :value="0">
                未启用
              </a-radio>
            </a-radio-group>
          </a-form-model-item>
          <div class="role-box">
            <a-form-model-item label="部门" >
              <a-tooltip v-if="departmentList.length !== 0" v-for="item in departmentList" :key="item.workDepartmentId">
                <template slot="title">
                  {{ item.workDepartmentName }}
                </template>
                <div class="name" >{{ item.workDepartmentName }}</div>
              </a-tooltip>
            </a-form-model-item>
            <a-form-model-item label="角色" prop="roleId">
              <a-select v-model="userData.roleId">
                <a-select-option v-for="item in roleList" :key="item.roleId" :disabled="item.roleId == 0">
                  {{ item.name }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </div>
        </a-form-model>
        <template slot="footer">
          <a-button key="submit" :loading="btnLoading" type="primary" @click="addSubManagement">
            确定
          </a-button>
          <a-button @click="reset" key="back">
            取消
          </a-button>
        </template>
      </a-modal>
    </a-card>
    <!--    重置密码-->
    <a-modal
      v-model="resetPopup"
      title="重置密码"
      :footer="null"
      :maskClosable="false"
      width="450px"
      @cancel="cancelBtn">
      <div style="display: flex;justify-content:center;">
        <div style="line-height: 31px;width: 75px;text-align: right;">新密码：</div>
        <a-input placeholder="请输入新密码" type="password" v-model="resetPassData.newPassword" style="width: 240px;" />
      </div>
      <div style="display: flex;justify-content:center;margin-top: 15px;">
        <div style="line-height: 31px;width: 75px;text-align: right;">确认密码：</div>
        <a-input placeholder="请再次确认密码" type="password" v-model="resetPassData.confirmPassword" style="width: 240px;" />
      </div>
      <div style="display: flex;justify-content:center;margin-top: 15px;">
        <a-button style="margin-right: 40px;" @click="cancelBtn">取消</a-button>
        <a-button type="primary" @click="confirmReset">确认修改</a-button>
      </div>
    </a-modal>
  </div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import { subManagementList, addSubManagement, getSubManagement, editSubManagement, changeStatus, selectByPhone, selectRole, passwordResetApi } from '@/api/user'
export default {
  data () {
    return {
      // 重置密码弹窗
      resetPopup: false,
      resetPassData: {
        newPassword: '',
        confirmPassword: ''
      },
      // 修改id
      resetRowId: '',
      // 判断添加修改
      addShowPopup: true,
      btnLoading: false,
      screenData: {},
      modalVisible: false,
      columns: [
        {
          title: '企业成员',
          dataIndex: 'userName',
          align: 'center'
        },
        {
          title: '所属部门',
          dataIndex: 'department',
          align: 'center',
          scopedSlots: { customRender: 'department' }
        },
        {
          title: '职务',
          dataIndex: 'roleName',
          align: 'center'
        },
        {
          title: '手机号（登录账号）',
          dataIndex: 'phone',
          align: 'center'
        },
        {
          title: '状态',
          dataIndex: 'statusText',
          align: 'center'
        },
        {
          title: '时间',
          dataIndex: 'createdAt',
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
      userData: {
        gender: 1,
        status: 0
      },
      // 启用数量
      usingNum: '',
      forbiddenNum: '',
      // 修改时Id
      userId: '',
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      statusList: [
        {
          label: '未启用',
          value: 0
        },
        {
          label: '正常',
          value: 1
        },
        {
          label: '禁用',
          value: 2
        }
      ],
      userIdList: [],
      rules: {
        userName: [
          { required: true, message: '请输入员工姓名', trigger: 'blur' }
        ],
        phone: [{ required: true, message: '请输入手机号', trigger: 'focus' }],
        password: [{ required: true, message: '密码不能为空', trigger: 'blur' }],
        confirmPass: [{ required: true, message: '请再次确认密码', trigger: 'blur' }],
        gender: [{ required: true, message: '请选择性别', trigger: 'change' }],
        status: [{ required: true, message: '请选择状态', trigger: 'change' }],
        roleId: [{ required: true, message: '请选择角色', trigger: 'change' }]
      },
      statusModalDis: false,
      departmentList: [],
      roleList: []
    }
  },
  created () {
    this.getTableData()
    this.getSelectRole()
  },
  methods: {
    // 取消按钮
    cancelBtn () {
      this.resetPopup = false
      this.resetPassData.newPassword = ''
      this.resetPassData.confirmPassword = ''
    },
    // 显示弹窗
    rowResetBtn (record) {
      this.resetRowId = record.userId
      this.resetPopup = true
    },
    // 确认修改
    confirmReset () {
      if (this.resetPassData.newPassword == '') {
        this.$message.warning('新密码不能为空')
        return false
      }
      if (this.resetPassData.confirmPassword == '') {
        this.$message.warning('请再次确认密码')
        return false
      }
      if (this.resetPassData.newPassword != this.resetPassData.confirmPassword) {
        this.$message.warning('两次密码不一致，请确认密码')
        this.resetPassData.newPassword = ''
        this.resetPassData.confirmPassword = ''
        return false
      }
      passwordResetApi({
        id: this.resetRowId,
        newPassword: this.resetPassData.newPassword
      }).then((res) => {
        this.$message.success('修改成功')
        this.resetPassData = {}
        this.resetPopup = false
      })
    },
    // 添加弹窗
    addPopupBtn () {
      this.modalVisible = true
      this.addShowPopup = true
    },
    getTableData () {
      const params = {
        phone: this.screenData.phone,
        status: this.screenData.status,
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      subManagementList(params).then(res => {
        this.usingNum = res.data.normalNum
        this.forbiddenNum = res.data.notEnabledNum
        this.tableData = res.data.list
        this.pagination.total = res.data.page.total
      })
    },
    // 筛选
    search () {
      this.pagination.current = 1
      this.getTableData()
    },
    // 表格选中
    onSelectChange (value) {
      this.userIdList = value
    },
    // 启用弹窗
    statusModal () {
      if (this.userIdList.length === 0) {
        this.$message.error('请选择要启用的账户')
      } else {
        this.statusModalDis = true
      }
    },
    // 批量启用
    status () {
      changeStatus({
        userId: this.userIdList.join(),
        status: 1
      }).then(res => {
        this.statusModalDis = false
        this.userIdList = []
        this.getTableData()
      })
    },
    // input失去焦点
    phoneSelect () {
      if (!(/^1[3456789]\d{9}$/.test(this.userData.phone))) {
        this.btnLoading = false
        this.$message.warning('手机号格式错误')
        return false
      }
      selectByPhone({
        phone: this.userData.phone
      }).then(res => {
        this.departmentList = res.data
      })
    },
    // 获取人员
    getSelectRole () {
      const obj = {
        roleId: 0,
        name: '请选择'
      }
      selectRole().then(res => {
        this.roleList = res.data
        this.roleList.unshift(obj)
      })
    },
    // 添加子账户
    addSubManagement () {
      this.$refs.ruleForm.validate(valid => {
        console.log(valid)
        if (valid) {
          // 修改
          if (this.userId !== '') {
            this.btnLoading = true
            if (!(/^1[3456789]\d{9}$/.test(this.userData.phone))) {
              this.btnLoading = false
              this.$message.warning('手机号格式错误')
              return false
            }
            editSubManagement({
              userId: this.userId,
              userName: this.userData.userName,
              phone: this.userData.phone,
              gender: this.userData.gender,
              roleId: this.userData.roleId,
              status: this.userData.status
            }).then(res => {
              this.btnLoading = false
              this.modalVisible = false
              this.userData = {
                gender: 1,
                status: 0
              }
              this.getTableData()
            }).catch((res) => {
              this.modalVisible = true
              this.btnLoading = false
            })
          } else {
            // 添加
            this.btnLoading = true
            if (!(/^1[3456789]\d{9}$/.test(this.userData.phone))) {
              this.btnLoading = false
              this.$message.warning('手机号格式错误')
              return false
            }
            if (this.userData.password != this.userData.confirmPass) {
              this.btnLoading = false
              this.userData.password = ''
              this.userData.confirmPass = ''
              this.$message.warning('两次密码不一致')
              return false
            }
            addSubManagement(this.userData).then(res => {
              this.btnLoading = false
              this.modalVisible = false
              this.getTableData()
              this.userData = {
                gender: 1,
                status: 0
              }
            }).catch((res) => {
              this.modalVisible = true
              this.btnLoading = false
            })
          }
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    // 取消
    reset () {
      this.modalVisible = false
      this.userData = {
        gender: 1,
        status: 0
      }
      this.departmentList = []
      this.$refs.ruleForm.resetFields()
    },
    // 修改子账户
    editSubManagement (id) {
      this.addShowPopup = false
      getSubManagement({
        userId: id
      }).then(res => {
        this.modalVisible = true
        this.userId = res.data.userId
        this.userData = res.data
        selectByPhone({
          phone: this.userData.phone
        }).then(res => {
          this.departmentList = res.data
        })
      })
    },
    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
      this.getTableData()
    }
  }
}
</script>
<style lang="less" scoped>
/deep/ .ant-modal-header{
  text-align: center;
  font-weight: bold;
}
.alter {
  width: 80%;
  height: 50px;
  background: #E6F7FF;
  border: 1px solid #C2EAFF;
  padding: 0 15px;
  span {
    line-height: 50px;
    margin-left: 10px;
  }
}
.table-wrapper {
  margin-top: 20px;
  .ant-btn {
    margin-bottom: 20px;
  }
}
.role-box {
  width: 100%;
  border-top: 1px dashed #e8e8e8;
  display: flex;
  justify-content: space-between;
  padding: 15px 5px;
  .ant-row {
    width: 49%;
    .name {
      overflow: hidden;
      text-overflow:ellipsis;
      white-space: nowrap;
    }
  }
}
</style>
