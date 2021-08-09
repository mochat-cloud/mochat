<template>
  <div class="label-wrapper">
    <a-row>
      <a-col>
        <div :split="false" class="lists">
          <a-alert :show-icon="false" message="1、标签分组名称不能重复。" banner />
          <a-alert :show-icon="false" message="2、标签总数量不限制，但给每个客户打标签最多为20个。" banner />
          <a-alert :show-icon="false" message="3、标签分组一旦被删除，归属于该分组的标签均将被删除。" banner />
          <a-alert :show-icon="false" message="4、【未分组】为固定存在，不可修改名称，亦不可删除。" banner />
        </div>
        <a-modal
          title="新增分组"
          :visible="visible"
          @cancel="() => {this.visible = false}"
        >
          <a-input v-model="newGroupName" :maxLength="15" placeholder="请输入分组名（不得超过15个字符）"></a-input>
          <template slot="footer">
            <a-button key="back" @click="() => {this.visible = false}">
              取消
            </a-button>
            <a-button :loading="btnLoading" key="submit" @click="addGroup" type="primary">
              确定
            </a-button>
          </template>
        </a-modal>
        <a-modal
          title="修改分组"
          :visible="editGroupModal"
          @cancel="() => {this.editGroupModal = false; this.editGroupId = ''; this.editGroupName = '';}"
        >
          <a-form-model :label-col="{ span: 5 }" :wrapper-col="{ span: 14 }">
            <a-form-model-item label="选择分组">
              <a-select v-model="editGroupId">
                <a-select-option v-for="item in groupData" :key="item.groupId" :disabled="item.groupId == 0">
                  {{ item.groupName }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
            <a-form-model-item label="修改分组名称">
              <a-input v-model="editGroupName" :maxLength="15" placeholder="请输入分组名（不得超过15个字符）"></a-input>
            </a-form-model-item>
          </a-form-model>
          <template slot="footer">
            <a-button key="back" @click="() => {this.editGroupModal = false; this.editGroupId = ''; this.editGroupName = '';}">
              取消
            </a-button>
            <a-button key="submit" @click="editGroupDefine" type="primary">
              确定
            </a-button>
          </template>
        </a-modal>
        <a-card>
          <a-form-model :label-col="{ span: 2 }" :wrapper-col="{ span: 5 }">
            <a-form-model-item label="选择分组">
              <a-select v-model="changeGroupId" @change="clickGroup">
                <a-select-option v-for="item in groupData" :key="item.groupId">
                  {{ item.groupName }}
                </a-select-option>
              </a-select>
            </a-form-model-item>
          </a-form-model>
          <div class="btn-box">
            <p>最后一次同步时间：{{ lastTime }}</p>
            <div>
              <a-button v-permission="'/workContactTag/index@sync'" @click="synContactTag">同步企业微信标签</a-button>
              <a-button v-permission="'/workContactTag/index@editGroup'" type="primary" @click="() => {this.editGroupModal = true}" style="marginRight: 10px">修改分组</a-button>
              <a-button v-permission="'/workContactTag/index@addGroup'" type="primary" @click="() => {this.visible = true}">新增分组</a-button>
              <a-button v-permission="'/workContactTag/index@deleteTag'" type="primary" @click="deleteTagModal">删除标签</a-button>
              <a-button v-permission="'/workContactTag/index@move'" type="primary" @click="moveTagModal">移动标签</a-button>
              <a-button v-permission="'/workContactTag/index@add'" type="primary" @click="() => {this.modalVisible = true;}">新建标签</a-button>
            </div>
          </div>
          <a-table
            bordered
            :columns="columns"
            :data-source="tableData"
            :rowKey="record => record.id"
            :row-selection="{ selectedRowKeys: deleteList, onChange: onSelectChange }"
            @change="handleTableChange"
            :pagination="pagination">
            <div slot="action" slot-scope="text, record">
              <template>
                <a-button v-permission="'/workContactTag/index@edit'" type="link" @click="editContactTag(record.id)">编辑</a-button>
                <a-popconfirm
                  v-permission="'/workContactTag/index@delete'"
                  :title="record.name+'标签删除后，则相应的用户失去了该标签属性。确定删除吗？'"
                  ok-text="确认"
                  cancel-text="取消"
                  @confirm="delTag(record.id)"
                >
                  <a-button type="link">删除</a-button>
                </a-popconfirm>
              </template>
            </div>
          </a-table>
          <a-modal
            width="300px"
            :visible="deleteModal"
            @cancel="() => {this.deleteModal = false}">
            <span>确定删除选中的标签?</span>
            <template slot="footer">
              <a-button key="back" @click="() => {this.deleteModal = false}">
                取消
              </a-button>
              <a-button @click="deleteTag" type="primary">
                确定
              </a-button>
            </template>
          </a-modal>
          <a-modal
            width="600px"
            :visible="modalVisible"
            @cancel="() => {this.modalVisible = false}"
            title="新增标签">
            <a-form :label-col="{ span: 5 }" :wrapper-col="{ span: 18 }">
              <a-form-item label="选择分组：">
                <a-select v-model="addTagData.groupId">
                  <a-select-option v-for="item in groupData" :key="item.groupId">
                    {{ item.groupName }}
                  </a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="标签名称">
                <span>每个标签名称最多15个字。同时新建多个标签时，请用“空格隔开”</span>
                <a-input v-model="addTagData.tagName"/>
              </a-form-item>
            </a-form>
            <template slot="footer">
              <a-button key="back" @click="() => {this.modalVisible = false}">
                取消
              </a-button>
              <a-button :loading="btnLoading" @click="saveTag" type="primary">
                确定
              </a-button>
            </template>
          </a-modal>
          <a-modal
            :visible="editVisible"
            @cancel="() => {this.editVisible = false}"
            title="修改标签名称">
            <a-form :label-col="{ span: 5 }" :wrapper-col="{ span: 17 }">
              <a-form-item label="选择分组：">
                <a-select v-model="editTagData.groupId">
                  <a-select-option v-for="item in groupData" :key="item.groupId">
                    {{ item.groupName }}
                  </a-select-option>
                </a-select>
              </a-form-item>
              <a-form-item label="标签名称">
                <a-input v-model="editTagData.tagName" :maxLength="15"/>
              </a-form-item>
            </a-form>
            <template slot="footer">
              <a-button key="back" @click="() => {this.editVisible = false}">
                取消
              </a-button>
              <a-button @click="saveTag" :loading="btnLoading" type="primary">
                确定
              </a-button>
            </template>
          </a-modal>
          <div class="bbox" ref="bbox">
            <a-modal
              :getContainer="() => $refs.bbox"
              class="modal"
              :visible="moveModal"
              @cancel="() => {this.moveModal = false}"
              title="选择分组">
              <a-select v-model="moveTagId" style="width: 200px;marginBottom: 20px;" @change="changeGroupMove">
                <a-select-option v-for="item in groupData" :key="item.groupId">
                  {{ item.groupName }}
                </a-select-option>
              </a-select>
              <div class="tag-box">
                <span :class="classActive === item.groupId ? 'active' : 'box'" v-for="item in groupData" :key="item.groupId" @click="moveTag(item.groupId)">{{ item.groupName }}</span>
              </div>
              <template slot="footer">
                <a-button @click="() => {this.moveModal = false}">取消</a-button>
                <a-button type="primary" @click="moveDefine" :loading="btnLoading">确定</a-button>
              </template>
            </a-modal>
          </div>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>

<script>
import { contactTagList, getContactTagGroup, addContactTag, delContactTag, contactTagDetail, editContactTag, addContactTagGroup, delContactTagGroup, moveContactTag, syncTag, editContactTagGroup } from '@/api/workContactTag'
export default {
  data () {
    return {
      btnLoading: false,
      visible: false,
      modalVisible: false,
      editVisible: false,
      deleteModal: false,
      moveModal: false,
      editGroupModal: false,
      columns: [
        {
          title: '标签名称',
          dataIndex: 'name',
          align: 'center'
        },
        {
          title: '客户数',
          dataIndex: 'contactNum',
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
      // 分组列表
      groupData: [],
      // 新增标签数据
      addTagData: {},
      // 修改时的数据
      editTagData: {},
      // 批量删除
      deleteList: [],
      // 新增分组名字
      newGroupName: '',
      // 编辑分组时Id
      groupId: '',
      // 切换时分组id
      changeGroupId: '',
      // 移动标签数据
      moveTagData: {},
      // 最后同步时间
      lastTime: '',
      moveTagId: null,
      // 点击切换
      classActive: 0,
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      editTagUpdateData: {},
      editGroupUpdateData: {},
      editGroupName: '',
      editGroupId: ''
    }
  },
  created () {
    this.getTableData()
    this.getGroup()
  },
  methods: {
    getTableData () {
      const params = {
        groupId: this.changeGroupId,
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      contactTagList(params).then(res => {
        this.tableData = res.data.list
        this.lastTime = res.data.syncTagTime
        this.pagination.total = res.data.page.total
        this.deleteList = []
      })
    },
    // 获取分组
    getGroup () {
      getContactTagGroup().then(res => {
        this.groupData = res.data
        this.changeGroupId = 0
      })
    },
    // 点击分组
    clickGroup (id) {
      this.changeGroupId = id
      this.getTableData()
    },
    // 新增分组
    addGroup () {
      if (this.newGroupName.trim() == '') {
        this.$message.error('请输入分组名称')
        return
      }
      this.btnLoading = true
      addContactTagGroup({
        groupName: this.newGroupName
      }).then(res => {
        this.newGroupName = ''
        this.btnLoading = false
        this.visible = false
        this.getGroup()
      }).catch(res => {
        this.btnLoading = false
      })
    },
    // 编辑分组
    editGroup (item) {
      this.editGroupModal = true
      this.groupId = item.groupId
      this.newGroupName = item.groupName
      this.editGroupUpdateData = item
    },
    // 确定编辑分组
    editGroupDefine () {
      if (this.editGroupId == '') {
        this.$message.error('请选择分组')
        return
      }
      if (this.editGroupName.trim() == '') {
        this.$message.error('请输入分组名称')
        return
      }
      const reg = /\s+/g
      if (reg.test(this.editGroupName)) {
        this.$message.error('请输入正确分组名称')
        return
      }
      this.btnLoading = true
      editContactTagGroup({
        groupId: this.editGroupId,
        groupName: this.editGroupName,
        isUpdate: 2
      }).then(res => {
        this.btnLoading = false
        this.editGroupModal = false
        this.newGroupName = ''
        this.editGroupId = ''
        this.editGroupName = ''
        this.getGroup()
      }).catch(res => {
        this.btnLoading = false
      })
    },
    // 删除分组
    deleteGroup (id) {
      delContactTagGroup({
        groupId: id
      }).then(res => {
        this.getGroup()
      })
    },
    // 移动分组
    moveTagModal () {
      if (this.deleteList.length !== 0) {
        this.moveModal = true
        this.moveTagId = this.changeGroupId
        this.classActive = this.changeGroupId
      } else {
        this.$message.error('请选择要移动的标签')
      }
    },
    moveTag (id) {
      this.classActive = id
      this.moveTagId = id
      this.moveTagData.groupId = id
    },
    changeGroupMove (value) {
      this.moveTagId = value
      this.moveTagData.groupId = value
      this.classActive = value
    },
    moveDefine () {
      this.btnLoading = true
      this.moveTagData.tagId = this.deleteList.join(',')
      moveContactTag(this.moveTagData).then(res => {
        this.moveModal = false
        this.getTableData()
        this.deleteList = []
        this.btnLoading = false
      })
    },
    // 数据筛选
    onSelectChange (val) {
      this.deleteList = val
    },
    // 新增标签
    saveTag () {
      if (this.editVisible) {
        if (this.editTagData.tagName.trim() == '') {
          this.btnLoading = false
          this.$message.error('请输入标签名称')
        } else {
          let isUpdate = null
          if (this.editTagUpdateData.groupId === this.editTagData.groupId && this.editTagUpdateData.tagName === this.editTagData.tagName) {
            isUpdate = 2
          } else {
            isUpdate = 1
          }
          this.btnLoading = true
          editContactTag({
            tagId: this.editTagData.tagId,
            groupId: this.editTagData.groupId,
            tagName: this.editTagData.tagName,
            isUpdate: isUpdate
          }).then(res => {
            this.getTableData()
            this.btnLoading = false
            this.editVisible = false
          }).catch(res => {
            this.btnLoading = false
          })
        }
      } else {
        if (this.addTagData.tagName == undefined) {
          this.btnLoading = false
          this.$message.error('请输入标签名称')
        } else {
          let flag = false
          let falg = false
          const reg = /\s+/g
          const tagArr = this.addTagData.tagName.replace(reg, ' ').split(' ')
          const newArr = Array.from(new Set(tagArr))
          if (tagArr.length > 1) {
            if (newArr.length !== tagArr.length) {
              this.btnLoading = false
              this.$message.error('标签名称不可重复')
              return
            }
          }
          tagArr.map(item => {
            if (item == '') {
              falg = true
            }
            if (item.length > 15) {
              flag = true
            }
          })
          if (flag) {
            this.$message.error('每个标签名称最多15个字')
            return
          }
          if (falg) {
            this.$message.error('请输入正确的分组名称')
            return
          }
          this.btnLoading = true
          addContactTag({
            groupId: this.addTagData.groupId,
            tagName: tagArr
          }).then(res => {
            this.getTableData()
            this.addTagData = {}
            this.btnLoading = false
            this.modalVisible = false
          }).catch(res => {
            this.btnLoading = false
          })
        }
      }
    },
    // 编辑标签
    editContactTag (id) {
      this.editVisible = true
      contactTagDetail({
        tagId: id
      }).then(res => {
        this.editTagData = res.data
        this.editTagUpdateData = JSON.parse(JSON.stringify(res.data))
      })
    },
    // 删除标签
    delTag (id) {
      delContactTag({
        tagId: id + ''
      }).then(res => {
        this.getTableData()
      })
    },
    // 批量删除判读
    deleteTagModal () {
      if (this.deleteList.length === 0) {
        this.$message.error('请选择要删除的标签')
      } else {
        this.deleteModal = true
      }
    },
    // 批量删除标签
    deleteTag () {
      const list = this.deleteList.join(',')
      delContactTag({
        tagId: list
      }).then(res => {
        this.deleteModal = false
        this.getTableData()
      })
    },
    // 同步标签
    synContactTag () {
      syncTag().then(res => {
        this.getTableData()
        this.$message.info('同步成功')
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
  .label-wrapper {
    height: 100%;
    .left {
      background: #fff;
      padding: 15px 15px;
      .btn {
        margin-top: 10px;
        width: 100%;
      }
      .group-list {
        .tags {
          display: inline-block;
          width: 100%;
          height: 40px;
          line-height: 40px;
          margin-top: 5px
        }
        .activeTag {
          display: inline-block;
          width: 100%;
          height: 40px;
          line-height: 40px;
          padding-left:5px;
          border-radius: 3px;
          background: #1890FF;
          margin-top:5px;
          color: #fff;
        }
      }
    }
    .lists{
      margin-bottom: 20px;
    }
    .group-btn {
      display: flex;
      justify-content: flex-end;
    }
    .btn-box {
      width: 100%;
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
      p{
        margin: 0;
      }
      div {
        padding: 0 10px;
        .ant-btn {
          margin-right: 10px;
        }
      }
    }
    .bbox {
      .tag-box {
        .box {
          display: inline-block;
          height: 60px;
          padding: 0 25px;
          border: 1px solid #ccc;
          text-align: center;
          line-height: 60px;
          margin: 0 20px 20px 0;
          border-radius: 8px;
        }
        .active {
          display: inline-block;
          height: 60px;
          padding: 0 25px;
          border: 1px solid rgb(62, 142, 233);
          color: rgb(62, 142, 233);
          text-align: center;
          line-height: 60px;
          margin: 0 20px 20px 0;
          border-radius: 8px;
        }
      }
    }
  }
</style>
