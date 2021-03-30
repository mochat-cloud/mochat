<template>
  <div>
    <a-card :bordered="false">
      <div class="table-page-search-wrapper">
        <a-form>
          <div class="search-btn">
            <div class="search">
              <a-form-item style="width:200px">
                <a-input v-model="searchName" placeholder="搜索企业微信名称"/>
              </a-form-item>
              <div class="btn">
                <a-button
                  v-permission="'/corp/index@search'"
                  type="primary"
                  @click="() => { this.pagination.current = 1 ; this.getTableData() }">查找</a-button>
                <a-button style="margin-left: 8px" @click="() => { this.searchName = '' }">清空</a-button>
              </div>
            </div>
            <span v-permission="'/corp/index@addwx'" class="table-page-search-submitButtons">
              <a-button type="primary" @click="() => {this.visible = true}">+添加企业微信号</a-button>
            </span>
          </div>
        </a-form>
      </div>
      <a-table
        :rowKey="record => record.corpId"
        :columns="columns"
        :data-source="tableData"
        :pagination="pagination"
        @change="handleTableChange">
        <div slot="action" slot-scope="text, record">
          <template>
            <a-button v-permission="'/corp/index@check'" type="link" @click="getWechatDetail(record.corpId)">查看</a-button>
            <a-button v-permission="'/corp/index@edit'" type="link" @click="editWechat(record.corpId)">修改</a-button>
          </template>
        </div>
      </a-table>
      <a-modal
        :width="640"
        :visible="visible"
        @cancel="() => {this.visible = false}">
        <a-form-model ref="ruleForm" :model="wechatDetail" :rules="rules" :label-col="{ span: 7 }" :wrapper-col="{ span: 12 }">
          <a-form-model-item label="企业名称：" prop="corpName">
            <a-input v-model="wechatDetail.corpName" placeholder="输入企业微信企业名称"/>
          </a-form-model-item>
          <a-form-model-item label="企业ID：" prop="wxCorpId">
            <a-input v-model="wechatDetail.wxCorpId" :maxLength="18" placeholder="输入企业微信ID"/>
          </a-form-model-item>
          <a-form-model-item label="通讯录管理secret：" prop="employeeSecret">
            <a-input v-model="wechatDetail.employeeSecret" :maxLength="43" placeholder="输入企业通讯录管理secret"/>
          </a-form-model-item>
          <a-form-model-item label="外部联系人管理secret：" prop="contactSecret">
            <a-input v-model="wechatDetail.contactSecret" :maxLength="43" placeholder="输入外部联系人管理secret"/>
          </a-form-model-item>
        </a-form-model>
        <template slot="footer">
          <a-button @click="saveConfig" :loading="btnLoading" key="submit" type="primary">
            保存配置
          </a-button>
          <a-button key="back" @click="() => {this.visible = false} ">
            关闭
          </a-button>
        </template>
      </a-modal>
      <a-modal
        :width="640"
        :visible="editVisble"
        @cancel="() => {this.editVisble = false}">
        <a-form-model ref="editForm" :model="editWechatDetail" :rules="rules" :label-col="{ span: 7 }" :wrapper-col="{ span: 12 }">
          <a-form-model-item label="企业名称：" prop="corpName">
            <a-input :disabled="getDisble" v-model="editWechatDetail.corpName"/>
          </a-form-model-item>
          <a-form-model-item label="企业ID：" prop="wxCorpId">
            <a-input :disabled="getDisble" v-model="editWechatDetail.wxCorpId"/>
          </a-form-model-item>
          <a-form-model-item label="通讯录管理secret：" prop="employeeSecret">
            <a-input :disabled="getDisble" v-model="editWechatDetail.employeeSecret"/>
          </a-form-model-item>
          <a-form-model-item label="外部联系人管理secret：" prop="contactSecret">
            <a-input :disabled="getDisble" v-model="editWechatDetail.contactSecret"/>
          </a-form-model-item>
          <a-form-model-item label="通讯录事件服务器URL：">
            <a-input :disabled="getDisble || editDisble" v-model="editWechatDetail.eventCallback" />
          </a-form-model-item>
          <a-form-model-item label="Token：">
            <a-input :disabled="getDisble || editDisble" v-model="editWechatDetail.token"/>
          </a-form-model-item>
          <a-form-model-item label="EncodingAESKey：">
            <a-input :disabled="getDisble || editDisble" v-model="editWechatDetail.encodingAesKey" />
          </a-form-model-item>
          <a-form-model-item label="外部联系人事件服务器URL">
            <a-input :disabled="getDisble || editDisble" v-model="editWechatDetail.eventCallback"/>
          </a-form-model-item>
        </a-form-model>
        <template slot="footer" v-if="getDisble">
          <a-button key="back" @click="() => {this.getDisble = false, this.editVisble = false}">
            关闭
          </a-button>
        </template>
        <template slot="footer" v-if="editDisble">
          <a-button @click="editConfig" key="submit" type="primary">
            保存配置
          </a-button>
          <a-button key="back" @click="() => {this.editDisble = false, this.editVisble = false}">
            关闭
          </a-button>
        </template>
      </a-modal>
    </a-card>
  </div>
</template>
<script>
import { wechatAuthList, addEnterpriseWeChat, lookEnterpriseWeChat, editEnterpriseWeChat } from '@/api/corp'
export default {
  name: 'WechatAuthorization',
  data () {
    return {
      btnLoading: false,
      visible: false,
      editVisble: false,
      columns: [
        {
          title: '企业名称',
          dataIndex: 'corpName',
          align: 'center'
        },
        {
          title: '企业ID',
          dataIndex: 'wxCorpId',
          align: 'center'
        },
        {
          title: '绑定时间',
          dataIndex: 'createdAt',
          align: 'center'
        },
        {
          title: '操作',
          dataIndex: 'action',
          width: '200px',
          align: 'center',
          scopedSlots: { customRender: 'action' }
        }
      ],
      tableData: [],
      searchName: '',
      wechatDetail: {},
      // 查看数据
      editWechatDetail: {},
      // 是否为查看
      getDisble: false,
      // 是否为修改
      editDisble: false,
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      rules: {
        corpName: [
          { required: true, message: '请输入企业微信企业名称', trigger: 'blur' }
        ],
        wxCorpId: [{ required: true, message: '请输入企业ID', trigger: 'blur' }],
        employeeSecret: [{ required: true, message: '请输入通讯录管理secret', trigger: 'blur' }],
        contactSecret: [{ required: true, message: '请输入外部联系人管理secret', trigger: 'blur' }]
      }
    }
  },
  created () {
    this.getTableData()
  },
  methods: {
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
    // 保存配置
    saveConfig () {
      this.$refs.ruleForm.validate(valid => {
        if (valid) {
          this.btnLoading = true
          addEnterpriseWeChat(this.wechatDetail).then(res => {
            this.wechatDetail = {}
            this.visible = false
            this.getTableData()
            this.btnLoading = false
          }).catch(() => {
            this.visible = true
            this.btnLoading = false
          })
        } else {
          console.log('error submit!!')
          return false
        }
      })
    },
    // 查看企业微信授权详情
    getWechatDetail (corpId) {
      this.editVisble = true
      this.getDisble = true
      lookEnterpriseWeChat({
        corpId: corpId
      }).then(res => {
        this.editWechatDetail = res.data
      })
    },
    // 编辑企业微信授权
    editWechat (corpId) {
      this.editVisble = true
      this.editDisble = true
      lookEnterpriseWeChat({
        corpId: corpId
      }).then(res => {
        this.editWechatDetail = res.data
      })
    },
    // 修改后保存
    editConfig () {
      this.$refs.editForm.validate(valid => {
        if (valid) {
          const params = {
            corpId: this.editWechatDetail.corpId,
            corpName: this.editWechatDetail.corpName,
            wxCorpId: this.editWechatDetail.wxCorpId,
            employeeSecret: this.editWechatDetail.employeeSecret,
            contactSecret: this.editWechatDetail.contactSecret
          }
          this.btnLoading = true
          editEnterpriseWeChat(params).then(res => {
            this.btnLoading = false
            this.wechatDetail = {}
            this.editVisble = false
            this.editDisble = false
            this.getTableData()
          }).catch(() => {
            this.btnLoading = false
            this.editVisble = true
            this.editDisble = true
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
<style lang='less' scoped>
.search-btn {
  display: flex;
  justify-content: space-between;
  .search {
    display: flex;
    .btn {
      margin-left: 20px;
      margin-top: 3px;
    }
  }
}
</style>
