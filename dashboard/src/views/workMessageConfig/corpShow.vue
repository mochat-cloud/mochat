<template>
  <div>
    <a-card>
      <div class="table-page-search-wrapper">
        <a-form>
          <a-row :gutter="48">
            <a-col :sm="6" :xm="24">
              <a-form-item>
                <a-input v-model="searchName" placeholder="搜索企业微信名称"/>
              </a-form-item>
            </a-col>
            <a-col :sm="8" :xm="24">
              <span class="table-page-search-submitButtons">
                <a-button v-permission="'/workMessageConfig/corpShow@search'" type="primary" @click="() => { this.pagination.current = 1; this.getTableData() }">查找</a-button>
                <a-button style="margin-left: 8px" @click="() => { this.searchName = '' }">清空</a-button>
              </span>
            </a-col>
          </a-row>
        </a-form>
      </div>
      <div class="table-wrapper">
        <a-table
          :columns="columns"
          :data-source="tableData"
          :rowKey=" record => record.corpId"
          :pagination="pagination"
          @change="handleTableChange">
          <template slot="action" slot-scope="text, record">
            <a-button v-permission="'/workMessageConfig/corpShow@check'" style="marginRight:10px" @click="getInformationData(record.corpId)">查看</a-button>
            <a-button @click="opending(record)" v-if="record.chatApplyStatus <= 1">开通</a-button>
          </template>
          <template slot="chatStatus" slot-scope="text, record">
            <span v-if="record.chatApplyStatus > 1">已开通</span>
            <span v-if="record.chatApplyStatus <= 1">未开通</span>
          </template>
        </a-table>
      </div>
      <a-modal
        :width="640"
        :visible="visible"
        @cancel="() => {this.visible = false}">
        <a-form-model ref="ruleForm" :model="informationData" :rules="rules" :label-col="{ span: 6 }" :wrapper-col="{ span: 14 }">
          <a-form-model-item label="企业名称：">
            <a-input disabled v-model="informationData.corpName" placeholder="输入企业微信企业名称"/>
          </a-form-model-item>
          <a-form-model-item label="企业ID：">
            <a-input disabled v-model="informationData.wxCorpid" placeholder="输入企业微信ID"/>
          </a-form-model-item>
          <a-form-model-item label="企业代码：" prop="socialCode">
            <a-input v-model="informationData.socialCode" :maxLength="18" placeholder=""/>
          </a-form-model-item>
          <a-form-model-item label="企业负责人：" prop="chatAdmin">
            <a-input v-model="informationData.chatAdmin" placeholder=""/>
          </a-form-model-item>
          <a-form-model-item label="企业负责人电话：" prop="chatAdminPhone">
            <a-input v-model="informationData.chatAdminPhone" placeholder=""/>
          </a-form-model-item>
          <a-form-model-item label="企业负责人身份证：" prop="chatAdminIdcard">
            <a-input v-model="informationData.chatAdminIdcard" placeholder=""/>
          </a-form-model-item>
          <a-form-model-item label="是否开通：" prop="chatApplyStatus">
            <a-radio-group v-model="informationData.chatApplyStatus">
              <a-radio :value="1">
                已开通
              </a-radio>
              <a-radio :value="2">
                未开通
              </a-radio>
            </a-radio-group>
          </a-form-model-item>
        </a-form-model>
        <template slot="footer">
          <a-button @click="defineInformation" :loading="btnLoading" key="submit" type="primary">
            确认
          </a-button>
          <a-button @click="() => { this.visible = false }" key="back">
            取消
          </a-button>
        </template>
      </a-modal>
      <a-modal
        :width="640"
        :visible="lookModal"
        @cancel="() => {this.lookModal = false}">
        <a-form :label-col="{ span: 6 }" :wrapper-col="{ span: 14 }">
          <a-form-item label="企业名称：">
            <a-input v-model="lookData.name"/>
          </a-form-item>
          <a-form-item label="企业ID：">
            <a-input v-model="lookData.wxCorpid"/>
          </a-form-item>
          <a-form-item label="企业代码：">
            <a-input v-model="lookData.socialCode"/>
          </a-form-item>
          <a-form-item label="企业负责人：">
            <a-input v-model="lookData.chatAdmin"/>
          </a-form-item>
          <a-form-item label="企业负责人电话：">
            <a-input v-model="lookData.chatAdminPhone"/>
          </a-form-item>
          <a-form-item label="企业负责人身份证：">
            <a-input v-model="lookData.chatAdminIdcard"/>
          </a-form-item>
        </a-form>
        <template slot="footer">
          <a-button @click="() => { this.lookModal = false }" key="back">
            关闭
          </a-button>
        </template>
      </a-modal>
    </a-card>
  </div>
</template>

<script>
import { addInformation, getEnterMembers, wechatAuthList } from '@/api/workMessageConfig'
export default {
  data () {
    return {
      btnLoading: false,
      visible: false,
      lookModal: false,
      searchName: '',
      columns: [
        {
          title: '企业名称',
          dataIndex: 'corpName',
          align: 'center'
        },
        {
          title: '企业ID',
          dataIndex: 'wxCorpid',
          align: 'center'
        },
        {
          title: '申请时间',
          dataIndex: 'createdAt',
          align: 'center'
        },
        {
          title: '状态',
          dataIndex: 'chatApplyStatus',
          align: 'center',
          scopedSlots: { customRender: 'chatStatus' }
        },
        {
          title: '操作',
          dataIndex: 'action',
          align: 'center',
          scopedSlots: { customRender: 'action' }
        }
      ],
      tableData: [],
      informationData: {},
      lookData: {},
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      rules: {
        socialCode: [
          { required: true, message: '请输入企业代码', trigger: 'blur' }
        ],
        chatAdmin: [{ required: true, message: '请输入企业负责人', trigger: 'blur' }],
        chatAdminPhone: [{ required: true, message: '请输入企业负责人电话', trigger: 'blur' }],
        chatAdminIdcard: [{ required: true, message: '请输入企业负责人身份证', trigger: 'blur' }],
        chatApplyStatus: [{ required: true, message: '请选择是否开通', trigger: 'change' }]
      }
    }
  },
  created () {
    this.getTableData()
  },
  methods: {
    // 获取表格数据
    getTableData () {
      const params = {
        corpName: this.searchName,
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      wechatAuthList(params).then(res => {
        this.tableData = res.data.list
        this.pagination.total = res.data.page.total
      })
    },
    // 开通弹窗
    opending (data) {
      this.visible = true
      this.informationData.wxCorpid = data.wxCorpid
      this.informationData.corpName = data.corpName
    },
    // 查看
    getInformationData (id) {
      getEnterMembers({
        corpId: id
      }).then(res => {
        this.lookModal = true
        this.lookData = res.data
      })
    },
    // 开通聊天记录申请
    defineInformation () {
      this.$refs.ruleForm.validate(valid => {
        if (valid) {
          const reg = /^1[3|4|5|7|8][0-9]\d{8}$/
          const regg = /(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{2}$)/
          if (!reg.test(this.informationData.chatAdminPhone)) {
            this.btnLoading = false
            return this.$message.error('请输入正确的手机号')
          }
          if (!regg.test(this.informationData.chatAdminIdcard)) {
            this.btnLoading = false
            return this.$message.error('请输入正确的证件号码')
          }
          this.btnLoading = true
          addInformation(this.informationData).then(res => {
            this.btnLoading = false
            this.getTableData()
            this.visible = false
            this.informationData = {}
          })
        } else {
          console.log('error submit!!')
          return false
        }
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

<style lang="scss" scoped>

</style>
