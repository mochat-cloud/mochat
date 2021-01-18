<template>
  <div class="wrapper">
    <div :split="false" class="lists">
      <a-alert :show-icon="false" message="1、可以生成带参数的二维码名片，支持活码功能，即随机选取设置的活码成员推给用户。加企业微信为好友后，可以给微信联系人自动回复相应欢迎消息和打标签。" banner />
      <a-alert :show-icon="false" message="2、每创建一个渠道活码，该码则自动进入【内容引擎】--【图片类型】--分组【渠道码-企业微信】，以素材的方式通过聊天侧边栏快速发送给客户。" banner />
      <a-alert :show-icon="false" message="3、受限于官方，单人类型的渠道码创建后尽量不要再修改成员，否则会造成列表中，该二维码中间的头像与配置的成员头像不一致，但是并不影响功能使用。" banner />
      <a-alert :show-icon="false" message="4、如果企业在企业微信后台为相关成员配置了可用的欢迎语，使用第三方系统配置欢迎语，则均不起效，推送的还是企业微信官方的。" banner />
    </div>
    <a-card>
      <a-form :label-col="{ span: 7 }" :wrapper-col="{ span: 14 }">
        <a-row :gutter="16">
          <a-col :lg="8">
            <a-form-item
              label="名称：">
              <a-input v-model="screentData.name" placeholder="搜索活码名称"></a-input>
            </a-form-item>
          </a-col>
          <a-col :lg="8">
            <a-form-item
              label="活码类型：">
              <a-select v-model="screentData.type">
                <a-select-option v-for="item in typeList" :key="item.value">
                  {{ item.label }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :lg="8">
            <a-form-item
              label="分组：">
              <a-select v-model="screentData.groupId">
                <a-select-option v-for="item in groupList" :key="item.groupId">
                  {{ item.name }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>
      <div class="search">
        <a-button v-permission="'/channelCode/index@search'" type="primary" style="marginRight: 10px" @click="getTableData">查询</a-button>
        <a-button @click="() => {this.screentData = {}}">重置</a-button>
      </div>
      <div class="btn-box">
        <a-button v-permission="'/channelCode/index@editGroup'" type="primary" @click="() => {this.editGroupDis = true}">修改分组</a-button>
        <a-button v-permission="'/channelCode/index@add'" type="primary" @click="() => {this.addGroupDis = true}">新建分组</a-button>
        <router-link :to="{path: '/channelCode/store'}">
          <a-button v-permission="'/channelCode/store'" type="primary">+新建</a-button>
        </router-link>
      </div>
      <a-table
        style="marginTop: 20px"
        bordered
        :columns="columns"
        :data-source="tableData"
        :rowKey="record => record.channelCodeId"
        :pagination="pagination"
        @change="handleTableChange">
        <div slot="image" slot-scope="text, record" class="img-box">
          <img style="width:90px; height:auto;" :src="record.qrcodeUrl" alt="">
          <span>{{ record.type }}</span>
        </div>
        <div slot="tags" slot-scope="text, record">
          <div v-if="record.tags.length !== 0" v-for="(item, index) in record.tags" :key="index + 'lseg'">{{ item }}</div>
        </div>
        <div slot="action" slot-scope="text, record">
          <template>
            <router-link :to="{path:`/channelCode/store?channelCodeId=${record.channelCodeId}`}">
              <a-button v-permission="'/channelCode/index@edit'" type="link">编辑</a-button>
            </router-link>
            <!-- <a-button type="link">编辑</a-button> -->
            <a-button v-permission="'/channelCode/index@customer'" type="link" @click="getChannelCodeContact(record.channelCodeId)">客户</a-button>
            <a-button v-permission="'/channelCode/index@download'" type="link">
              <a :href="record.qrcodeUrl" download>下载</a>
            </a-button>
            <a-button v-permission="'/channelCode/index@move'" type="link" @click="moveGroup(record.channelCodeId, record.groupId)">移动</a-button>
            <router-link :to="{path: `/channelCode/statistics?channelCodeId=${record.channelCodeId}`}">
              <a-button v-permission="'/channelCode/statistics'" type="link">统计</a-button>
            </router-link>
          </template>
        </div>
      </a-table>
    </a-card>
    <a-modal
      width="800px"
      title="扫码客户"
      :visible="contactModal"
      @cancel="() => {this.contactModal = false; this.contactPagination.current = 1;}">
      <a-table
        style="marginTop: 20px;"
        bordered
        :columns="contactColumns"
        :data-source="contactTableData"
        :rowKey="record => record.contactId"
        :pagination="contactPagination"
        @change="handleTableChangeContact">
      </a-table>
      <template slot="footer">
        <a-button type="primary" @click="() => {this.contactModal = false; this.contactPagination.current = 1;}">确定</a-button>
      </template>
    </a-modal>
    <a-modal
      title="新建分组"
      :visible="addGroupDis"
      @cancel="() => {this.addGroupDis = false; this.groupName = ''}">
      <a-form-model :label-col="{ span: 6 }" :wrapper-col="{ span: 14}">
        <a-form-model-item label="分组名称：">
          <p>每个分组名称最多15个字。同时新建多个分组时，请用“空格”隔开</p>
          <a-input v-model="groupName"/>
        </a-form-model-item>
      </a-form-model>
      <template slot="footer">
        <a-button @click="() => {this.addGroupDis = false; this.groupName = ''}">取消</a-button>
        <a-button type="primary" :loading="btnLoading" @click="addGroup">确定</a-button>
      </template>
    </a-modal>
    <a-modal
      title="修改分组"
      :visible="editGroupDis"
      @cancel="() => {this.editGroupDis = false; this.editGroupData = {}}">
      <a-form-model :label-col="{ span: 6 }" :wrapper-col="{ span:14}">
        <a-form-model-item label="选择分组：">
          <a-select v-model="editGroupData.groupId">
            <a-select-option v-for="item in groupList" :key="item.groupId">
              {{ item.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
        <a-form-model-item label="修改分组名称：">
          <a-input v-model="editGroupData.name"/>
        </a-form-model-item>
      </a-form-model>
      <template slot="footer">
        <a-button @click="() => {this.editGroupDis = false; this.editGroupData = {}}">取消</a-button>
        <a-button type="primary" :loading="btnLoading" @click="editGroup">确定</a-button>
      </template>
    </a-modal>
    <div class="mbox" ref="mbox"></div>
    <a-modal
      :getContainer="() => $refs.mbox"
      title="移动分组"
      class="move-box"
      :visible="moveGroupDis"
      @cancel="() => {this.moveGroupDis = false}">
      <div class="group-box">
        <div :class="moveGroupId == item.groupId ? 'active' : ''" v-for="item in groupList" :key="item.groupId" @click="changeGroup(item.groupId)">
          {{ item.name }}
        </div>
      </div>
      <template slot="footer">
        <a-button @click="() => {this.moveGroupDis = false; this.moveGroupData = {}}">取消</a-button>
        <a-button type="primary" @click="moveGroupDefined">确定</a-button>
      </template>
    </a-modal>
  </div>
</template>

<script>
import { channelCodeList, channelCodeGroup, channelCodeGroupAdd, channelCodeGroupUpdate, channelCodeContact, channelCodeGroupMove } from '@/api/channelCode'
export default {
  data () {
    return {
      channelCodeId: '',
      editGroupDis: false,
      addGroupDis: false,
      visible: false,
      btnLoading: false,
      contactModal: false,
      moveGroupDis: false,
      columns: [
        {
          align: 'center',
          title: '二维码',
          dataIndex: 'image',
          scopedSlots: { customRender: 'image' }
        },
        {
          align: 'center',
          title: '名称',
          dataIndex: 'name'
        },
        {
          align: 'center',
          title: '分组',
          dataIndex: 'groupName'
        },
        {
          align: 'center',
          title: '客户数',
          dataIndex: 'contactNum'
        },
        {
          align: 'center',
          title: '标签',
          dataIndex: 'tags',
          scopedSlots: { customRender: 'tags' }
        },
        {
          align: 'center',
          title: '自动添加好友',
          dataIndex: 'autoAddFriend'
        },
        {
          align: 'center',
          title: '操作',
          width: '250px',
          dataIndex: 'action',
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
      screentData: {},
      groupList: [],
      typeList: [
        {
          label: '全部',
          value: 0
        },
        {
          label: '单人',
          value: 1
        },
        {
          label: '多人',
          value: 2
        }
      ],
      // 客户数据
      contactTableData: [],
      contactColumns: [
        {
          align: 'center',
          title: '客户名称',
          dataIndex: 'name'
        },
        {
          align: 'center',
          title: '归属成员',
          dataIndex: 'employees'
        },
        {
          align: 'center',
          title: '添加时间',
          dataIndex: 'createTime'
        }
      ],
      contactPagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      // 分组
      groupName: '',
      editGroupData: {},
      // 移动分组
      moveGroupId: ''
      // channelCodeId: ''
    }
  },
  created () {
    this.getTableData()
    this.getGroupList()
  },
  methods: {
    getTableData () {
      const params = {
        name: this.screentData.name,
        groupId: this.screentData.groupId,
        type: this.screentData.type,
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      channelCodeList(params).then(res => {
        this.tableData = res.data.list
        this.pagination.total = res.data.page.total
      })
    },
    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
      this.getTableData()
    },
    handleTableChangeContact ({ current, pageSize }) {
      this.contactPagination.current = current
      this.contactPagination.pageSize = pageSize
      this.getChannelCodeContact(this.channelCodeId)
    },
    getGroupList () {
      channelCodeGroup().then(res => {
        this.groupList = res.data
      })
    },
    // 获取客户
    getChannelCodeContact (id) {
      this.channelCodeId = id
      channelCodeContact({
        channelCodeId: this.channelCodeId,
        page: this.contactPagination.current,
        perPage: this.contactPagination.pageSize
      }).then(res => {
        this.contactModal = true
        this.contactTableData = res.data.list
        this.contactPagination.total = res.data.page.total
      })
    },
    // 新增分组
    addGroup () {
      const reg = /\s+/g
      const name = this.groupName.replace(reg, ' ').split(' ')
      let falg = false
      let flag = false
      let fg = false
      if (name.length > 1) {
        const lengths = Array.from(new Set(name)).length
        if (lengths < name.length) {
          flag = true
        }
      }
      name.map((item, index) => {
        if (item == '') {
          fg = true
        }
        if (item.length > 15) {
          this.$message.error('每个敏感词最多15个字')
          falg = true
        }
      })
      if (falg) {
        return
      }
      if (fg) {
        this.$message.error('请输入正确的分组名称')
        return
      }
      if (flag) {
        this.$message.error('分组名称重复')
        return
      }
      this.btnLoading = true
      channelCodeGroupAdd({
        name: name
      }).then(res => {
        this.getGroupList()
        this.addGroupDis = false
        this.groupName = ''
        this.btnLoading = false
      }).catch(res => {
        this.btnLoading = false
      })
    },
    // 修改分组
    editGroup () {
      const reg = /\s+/g
      const name = this.editGroupData.name
      if (this.editGroupData.groupId == undefined) {
        this.$message.error('请选择分组')
        return
      }
      if (reg.test(name)) {
        this.$message.error('请输入正确的分组名称')
        return
      }
      this.btnLoading = true
      channelCodeGroupUpdate({
        groupId: this.editGroupData.groupId,
        name: this.editGroupData.name
      }).then(res => {
        this.editGroupData = {}
        this.getGroupList()
        this.editGroupDis = false
        this.btnLoading = false
      }).catch(res => {
        this.btnLoading = false
      })
    },
    // 移动分组
    moveGroup (id, groupId) {
      this.moveGroupDis = true
      this.channelCodeId = id
      this.moveGroupId = groupId
    },
    // 选择移动分组
    changeGroup (id) {
      this.moveGroupId = id
    },
    // 确定移动分组
    moveGroupDefined () {
      channelCodeGroupMove({
        channelCodeId: this.channelCodeId,
        groupId: this.moveGroupId
      }).then(res => {
        this.moveGroupDis = false
        this.moveGroupId = ''
        this.channelCodeId = ''
        this.getTableData()
      })
    }
  }
}
</script>

<style lang="less" scoped>
  .wrapper{
    .search {
      display: flex;
      justify-content: flex-end;
    }
    .btn-box {
      margin-top: 20px;
      display: flex;
      justify-content: flex-end;
      .ant-btn {
        margin-left: 10px;
      }
    }
    .img-box {
      display: flex;
      flex-direction: column;
      align-items: center
    }
    .mbox {
      .move-box {
        .group-box {
          display: flex;
          flex-wrap: wrap;
          div {
            padding: 0 10px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            margin: 5px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
          }
          .active {
            padding: 0 10px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            margin: 5px 10px;
            border: 2px solid #1890FF;
            border-radius: 5px;
            cursor: pointer;
          }
        }
      }
    }
  }
</style>
