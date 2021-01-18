<template>
  <div class="wrapper-box">
    <div :split="false" class="lists">
      <a-alert :show-icon="false" message="1、当敏感词被不启用时，曾经所触发的内容依然在列表中可见。" banner />
      <a-alert :show-icon="false" message="2、显示历史上开启会话存档的企业成员。若曾经设置过某员工违规提醒，现在对该员工关闭、不再开启会话存档或是该员工离职，那么其历史触发敏感词的监控内容，依然在列表中可见。" banner />
      <a-alert :show-icon="false" message="3、显示历史上设置过违规提醒的群聊。如曾经设置过某群聊违规提醒，现在该群聊不再设置，那么其历史触发敏感词的监控内容，依然在列表中可见。" banner />
    </div>
    <a-card>
      <a-form :label-col="{ span: 7 }" :wrapper-col="{ span: 14 }">
        <a-row :gutter="16">
          <a-col :lg="8">
            <a-form-item
              label="关键词：">
              <a-input v-model="screentData.keyWords"></a-input>
            </a-form-item>
          </a-col>
          <a-col :lg="8">
            <a-form-item
              label="选择分组：">
              <a-select v-model="screentData.groupId" @change="onChange">
                <a-select-option v-for="item in searchGroupList" :key="item.groupId">
                  {{ item.name }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :lg="8">
            <a-form-item label="">
              <a-button v-permission="'/sensitiveWords/index@search'" type="primary" @click="() => {this.pagination.current = 1; this.isSearch = true; this.getTableData()}">查询</a-button>
              <a-button style="marginLeft:5px" @click="reset">重置</a-button>
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>
      <div class="btn-box">
        <a-button v-permission="'/sensitiveWords/index@edit'" type="primary" @click="() => {this.editGroupDis = true}">修改分组</a-button>
        <a-button v-permission="'/sensitiveWords/index@add'" type="primary" @click="() => {this.addGroupDis = true}">新建分组</a-button>
        <a-button v-permission="'/sensitiveWords/index@addWord'" type="primary" @click="() => {this.visible = true}">新建敏感词</a-button>
      </div>
      <a-table
        bordered
        :columns="columns"
        :data-source="tableData"
        :rowKey="record => record.sensitiveWordId"
        :pagination="pagination"
        @change="handleTableChange">
        <div slot="status" slot-scope="text, record">
          <span v-if="record.status == 1">开启</span>
          <span v-if="record.status == 2">关闭</span>
        </div>
        <div slot="action" slot-scope="text, record">
          <template>
            <a-button v-permission="'/sensitiveWords/index@close'" type="link" v-if="record.status == 2" @click="changeStatus(1, record.sensitiveWordId)">开启</a-button>
            <a-button v-permission="'/sensitiveWords/index@close'" type="link" v-if="record.status == 1" @click="changeStatus(2, record.sensitiveWordId)">关闭</a-button>
            <a-button v-permission="'/sensitiveWords/index@delete'" type="link" @click="delSensitiveWord(record.sensitiveWordId)">删除</a-button>
            <a-button v-permission="'/sensitiveWords/index@move'" type="link" @click="moveGroup(record.sensitiveWordId, record.groupId)">移动</a-button>
          </template>
        </div>
      </a-table>
    </a-card>
    <a-modal
      title="新建敏感词"
      :visible="visible"
      @cancel="() => {this.visible = false; this.sensitiveWordsData = {}}">
      <a-form-model :label-col="{ span: 6 }" :wrapper-col="{ span:14}">
        <a-form-model-item label="选择分组：">
          <a-select v-model="sensitiveWordsData.groupId">
            <a-select-option v-for="item in groupList" :key="item.groupId">
              {{ item.name }}
            </a-select-option>
          </a-select>
        </a-form-model-item>
        <a-form-model-item label="敏感词名称：">
          <p>每个敏感词名称最多6个字。同时新建多个敏感词时，请用“空格”隔开</p>
          <a-input v-model="sensitiveWordsData.name" />
        </a-form-model-item>
      </a-form-model>
      <template slot="footer">
        <a-button @click="() => {this.visible = false; this.sensitiveWordsData = {}}">取消</a-button>
        <a-button type="primary" :loading="btnLoading" @click="addSenistive">确定</a-button>
      </template>
    </a-modal>
    <a-modal
      title="新建分组"
      :visible="addGroupDis"
      @cancel="() => {this.addGroupDis = false; this.newGroupName = ''}">
      <a-form-model :label-col="{ span: 6 }" :wrapper-col="{ span: 14}">
        <a-form-model-item label="分组名称：">
          <p>每个分组名称最多15个字。同时新建多个分组时，请用“空格”隔开</p>
          <a-input v-model="newGroupName"/>
        </a-form-model-item>
      </a-form-model>
      <template slot="footer">
        <a-button @click="() => {this.addGroupDis = false; this.newGroupName = ''}">取消</a-button>
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
        <a-button @click="() => {this.moveGroupDis = false; this.moveGroupId = ''; this.moveSensitiveId = ''}">取消</a-button>
        <a-button type="primary" @click="moveGroupDefined">确定</a-button>
      </template>
    </a-modal>
  </div>
</template>

<script>
import { sensitiveWordsList, sensitiveWordsGroupList, sensitiveWordsAdd, sensitiveWordsGroupAdd, sensitiveWordsGroupUp, sensitiveWordsStatus, delSensitiveWords, sensitiveWordsMove } from '@/api/sensitiveWords'
export default {
  data () {
    return {
      visible: false,
      btnLoading: false,
      addGroupDis: false,
      editGroupDis: false,
      moveGroupDis: false,
      columns: [
        {
          align: 'center',
          title: '敏感词',
          dataIndex: 'name'
        },
        {
          align: 'center',
          title: '员工触发次数',
          dataIndex: 'employeeNum'
        },
        {
          align: 'center',
          title: '客户触发次数',
          dataIndex: 'contactNum'
        },
        {
          align: 'center',
          title: '创建时间',
          dataIndex: 'createdAt'
        },
        {
          align: 'center',
          title: '状态',
          dataIndex: 'status',
          scopedSlots: { customRender: 'status' }
        },
        {
          align: 'center',
          title: '操作',
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
      // 分组id
      groupId: '',
      // 分组列表
      groupList: [],
      // 新建敏感词
      sensitiveWordsData: {},
      // 新建分组
      newGroupName: '',
      // 修改分组名称
      editGroupData: {},
      // 移动敏感词id
      moveSensitiveId: '',
      // 移动分组Id
      moveGroupId: '',
      searchGroupList: [],
      isSearch: false
    }
  },
  created () {
    this.getTableData()
    this.getGroupList()
  },
  methods: {
    onChange () {
      this.$forceUpdate()
    },
    reset () {
      this.screentData = {
        keyWords: '',
        groupId: 0
      }
      this.isSearch = false
    },
    getTableData () {
      let params = {}
      if (this.isSearch) {
        params = {
          keyWords: this.screentData.keyWords,
          groupId: this.screentData.groupId,
          page: this.pagination.current,
          perPage: this.pagination.pageSize
        }
      } else {
        params = {
          keyWords: '',
          groupId: 0,
          page: this.pagination.current,
          perPage: this.pagination.pageSize
        }
      }
      sensitiveWordsList(params).then(res => {
        this.tableData = res.data.list
        this.pagination.total = Number(res.data.page.total)
      })
    },
    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
      this.getTableData()
    },
    // 获取分组列表
    getGroupList () {
      const obj = {
        name: '全部',
        groupId: 0
      }
      const arr = []
      arr.push(obj)
      sensitiveWordsGroupList().then(res => {
        this.groupList = res.data
        res.data.map(item => {
          arr.push(item)
        })
        this.searchGroupList = arr
        this.screentData.groupId = 0
      })
    },
    // 新增敏感词
    addSenistive () {
      if (this.sensitiveWordsData.groupId == undefined) {
        this.$message.error('请选择分组')
        return
      }
      if (this.sensitiveWordsData.name == undefined) {
        this.$message.error('请输入敏感词名称')
        return
      }
      const reg = /\s+/g
      const name = this.sensitiveWordsData.name.replace(reg, ' ').split(' ')
      let falg = false
      let flag = false
      name.map(item => {
        if (item == '') {
          flag = true
        }
        if (item.length > 6) {
          falg = true
        }
      })
      if (falg) {
        this.$message.error('每个敏感词最多6个字')
        return
      }
      if (flag) {
        this.$message.error('请输入敏感词')
        return
      }
      const params = {
        groupId: this.sensitiveWordsData.groupId,
        name: name.join(',')
      }
      this.btnLoading = true
      sensitiveWordsAdd(params).then(res => {
        this.btnLoading = false
        this.visible = false
        this.sensitiveWordsData = {}
        this.getTableData()
      })
    },
    // 新建分组
    addGroup () {
      const reg = /\s+/g
      const name = this.newGroupName.replace(reg, ' ').split(' ')
      let flag = false
      let falg = false
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
          this.$message.error('分组名称最多15个字')
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
      sensitiveWordsGroupAdd({
        name: name.join(',')
      }).then(res => {
        this.btnLoading = false
        this.addGroupDis = false
        this.newGroupName = ''
        this.getGroupList()
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
      if (name == '' || name == undefined) {
        this.$message.error('请输入分组名称')
        return
      }
      if (reg.test(name)) {
        this.$message.error('请输入正确的分组名称')
        return
      }
      this.btnLoading = true
      sensitiveWordsGroupUp(this.editGroupData).then(res => {
        this.editGroupDis = false
        this.btnLoading = false
        this.editGroupData = {}
        this.getGroupList()
      }).catch(res => {
        this.btnLoading = false
      })
    },
    // 修改状态
    changeStatus (type, id) {
      let status = ''
      const _this = this
      if (type == 1) {
        status = '开启'
      } else {
        status = '关闭'
      }
      this.$confirm({
        title: `是否${status}当前的敏感词`,
        content: '',
        okText: '确定',
        cancelText: '取消',
        onOk () {
          sensitiveWordsStatus({
            sensitiveWordId: id,
            status: type
          }).then(res => {
            _this.getTableData()
          })
        },
        onCancel () {
          console.log('Cancel')
        }
      })
    },
    // 删除敏感词
    delSensitiveWord (id) {
      const _this = this
      this.$confirm({
        title: `是否删除当前的敏感词`,
        content: '',
        okText: '确定',
        cancelText: '取消',
        onOk () {
          delSensitiveWords({
            sensitiveWordId: id
          }).then(res => {
            _this.getTableData()
          })
        },
        onCancel () {
          console.log('Cancel')
        }
      })
    },
    // 移动分组
    moveGroup (id, groupId) {
      this.moveGroupDis = true
      this.moveSensitiveId = id
      this.moveGroupId = groupId
    },
    // 选择移动分组
    changeGroup (id) {
      this.moveGroupId = id
    },
    // 确定移动分组
    moveGroupDefined () {
      sensitiveWordsMove({
        sensitiveWordId: this.moveSensitiveId,
        groupId: this.moveGroupId
      }).then(res => {
        this.moveGroupDis = false
        this.moveGroupId = ''
        this.moveSensitiveId = ''
        this.getTableData()
      })
    }
  }
}
</script>

<style lang="less" scoped>
.wrapper-box {
  .btn-box {
    display: flex;
    justify-content: flex-end;
    .ant-btn {
      margin-right: 10px;
    }
  }
  .ant-table-wrapper {
    margin-top: 20px;
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
