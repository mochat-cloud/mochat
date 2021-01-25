<template>
  <div class="new-pull">
    <div class="content">
      <div class="left-wrapper">
        <a-card class="service-wrapper">
          <div class="service">客服账号</div>
          <div class="leading-wrapper">
            <div class="user-wrapper">
              <a-icon type="user" class="user" />
            </div>
            <div class="user-info" v-if="leadingWords">
              <div class="info">{{ leadingWords }}</div>
            </div>
          </div>
        </a-card>
      </div>
      <div class="right-wrapper">
        <div class="right">
          <a-form class="form-one" :label-col="{ span: 3 }" :wrapper-col="{ span: 7}">
            <a-form-item label="选择分组" class="form-group">
              <a-select v-model="workRoomGroupId">
                <a-select-option :value="0">
                  未分组
                </a-select-option>
                <a-select-option
                  :value="item.workRoomGroupId"
                  v-for="(item, index) in group"
                  :key="index">
                  {{ item.workRoomGroupName }}
                </a-select-option>
              </a-select>
            </a-form-item>
          </a-form>
          <a-form class="form-two" :label-col="{ span: 3 }" :wrapper-col="{ span: 20 }">
            <a-form-item label="扫码名称" class="form-item">
              <a-input v-model="qrcodeName" :disabled="edit" :maxLength="30" placeholder="请输入扫码名称"></a-input>
            </a-form-item>
            <a-form-item label="添加验证" class="form-item">
              <a-radio-group
                v-model="isVerified"
              >
                <a-radio :value="1">
                  需验证
                </a-radio>
                <a-radio :value="2">
                  直接通过
                </a-radio>
              </a-radio-group>
            </a-form-item>
            <a-form-item label="使用成员" class="form-item">
              <div class="employee" v-if="employees.length">
                <a-tag class="tag" v-for="(inner, i) in employees" :key="i">{{ inner.employeeName }}</a-tag>
              </div>
              <a-button @click="addNewPeople">+添加成员</a-button>
            </a-form-item>
            <a-form-item label="入群打标签" class="form-item">
              <div class="label">
                <ul class="tags-wrapper">
                  <div v-for="(item, index) in tags" :key="index">
                    <li class="tag-item" v-if="index < tagShowNum">
                      <a-button class="group-name" @click="chooseTagGroup(item.groupId)">{{ item.groupName }}</a-button>
                      <div class="tag-content">
                        <a-tag class="tag" :color="inner.isSelected == 1 ? '#1890ff' : ''" v-for="(inner, i) in item.list" @click="chooseTag(item, inner.tagId)" :key="i">{{ inner.tagName }}</a-tag>
                        <a-button class="add-new" @click="addNewTag(item.groupId, item.groupName)">+新建标签</a-button>
                      </div>
                    </li>
                  </div>
                  <a-button v-if="tags.length > 5" @click="showMore" type="link">{{ tagShowMore ? '收起' : '展开' }}</a-button>
                </ul>
              </div>
            </a-form-item>
            <a-form-item label="入群引导语" class="form-item">
              <div class="leading">
                <a-row>
                  <a-textarea id="textarea" :disabled="edit" v-model="leadingWords" :maxLength="1000" :rows="4" />
                </a-row>
                <a-row>
                  点击插入<a-button type="primary" :disabled="edit" @click="insert">客户名称</a-button>
                </a-row>
                <a-row>
                  <a-alert :show-icon="false" message="提示：火狐浏览器可能出现无法正确插入客户名称，请使用谷歌、360浏览器" banner />
                </a-row>
              </div>
            </a-form-item>
            <a-form-item label="添加群聊" class="form-item">
              <a-row>
                <p>
                  可添加多个群聊，当前面的群人数达到上限后，自动发送后面的群二维码。即当第一个群达到人数上限，客户自动扫码会添加第二个客服，然后进入第二个群聊，依次往后进行。因系统无法判断群二维码是否真实有效，请您准确上传群对应的群二维码，否则会影响拉群效果
                </p>
              </a-row>
              <a-row>
                <a-table
                  bordered
                  rowKey="roomId"
                  :columns="columns"
                  :pagination="false"
                  :data-source="rooms">
                  <div slot="maxNum" slot-scope="text, record">
                    <div>
                      群人数达到
                      <a-input class="chat-num" v-model="record.maxNum"></a-input>
                      人，换群加入
                    </div>
                  </div>
                  <div slot="longRoomQrcodeUrl" slot-scope="text, record, index">
                    <div @click="upload(record, index)">
                      <upload
                        :imageUrl="record.longRoomQrcodeUrl"
                        @success="uploadSuccess"
                        :file-type="1"></upload>
                    </div>

                  </div>
                  <div slot="action" slot-scope="text, record, index">
                    <a-button type="link" @click="deleteGroup(record, index)">删除</a-button>
                  </div>
                </a-table>
              </a-row>
              <a-row>
                <a-button @click="chooseChat">+选择群聊</a-button>
              </a-row>
              <div class="btn-wrapper">
                <a-button v-permission="'/workRoomAutoPull/store@submit'" type="primary" @click="submit">提交</a-button>
              </div>
            </a-form-item>
          </a-form>
        </div>
      </div>
    </div>
    <a-modal
      title="新建标签"
      :maskClosable="false"
      :width="800"
      :visible="addNewTagShow"
      @ok="newTagConfirm"
      @cancel="addNewTagShow = false"
    >
      <div class="label-wrapper">
        <span class="text">分组名称:</span>
        <div class="containt">
          <a-input v-model="tagGroupName" disabled class="select"></a-input>
          <!-- <a-select v-model="tagGroupid" class="select">
            <a-select-option v-for="(item, index) in tagsGroup" :value="item.groupId" :key="index">
              {{ item.groupName }}
            </a-select-option>
          </a-select> -->
        </div>
      </div>
      <div class="label-wrapper">
        <span class="text">标签名称:</span>
        <div class="containt">
          <div>每个标签名称最多15个字符，同时新建多个标签时，请用“空格”隔开</div>
          <a-input class="input" v-model="tagName"></a-input>
        </div>
      </div>
    </a-modal>

    <a-modal
      title="选择群聊"
      :maskClosable="false"
      :width="550"
      :visible="groupChatShow"
      @ok="addNewChat"
      @cancel="groupChatShow = false"
    >
      <div>
        <a-input-search class="search" v-model="searchGroupWord" @search="getRoomList" placeholder="请输入群名称"></a-input-search>
        <div class="total">全部群聊({{ total }})</div>
      </div>
      <ul class="group-wrapper">
        <li class="group-item" v-for="(item, index) in groupChatList" :key="index">
          <div class="group-left">
            <a-icon type="user" class="user" />
          </div>
          <div class="group-right">
            <div>
              <div>{{ item.roomName }}</div>
              <div>{{ `(${item.currentNum}/${item.roomMax})` }}</div>
            </div>
            <a-checkbox :checked="judgeChecked(item.roomId)" @change="checkChange($event, item)">
            </a-checkbox>
          </div>
        </li>
      </ul>
    </a-modal>
    <a-modal
      title="选择企业成员"
      :maskClosable="false"
      :width="700"
      :bodyStyle="{'max-height':'500px',overflow: 'auto'}"
      :visible="choosePeopleShow"
      okText="确认"
      cancelText="取消"
      @ok="choosePeople"
      @cancel="choosePeopleShow = false"
    >
      <Department :memberKey="employees" @change="peopleChange"></Department>
    </a-modal>
  </div>
</template>

<script>
import { workRoomGroupList, autoPullShow, workContactTagGroup, addWorkContactTag, roomList, autoPullUpdate, autoPullCreate, tagList } from '@/api/workRoom'
import upload from './components/upload'
import Department from '@/components/department'
import { mapGetters } from 'vuex'
const columns = [
  {
    title: '群名称(人数)',
    dataIndex: 'roomName',
    customRender: (test, record) => `${record.roomName} (${record.num}/${record.roomMax})`
  },
  {
    title: '群人数上限',
    dataIndex: 'maxNum',
    scopedSlots: { customRender: 'maxNum' }
  },
  {
    title: '群二维码',
    dataIndex: 'longRoomQrcodeUrl',
    scopedSlots: { customRender: 'longRoomQrcodeUrl' }
  },
  {
    title: '操作',
    dataIndex: 'action',
    width: '150px',
    scopedSlots: { customRender: 'action' }
  }
]
export default {
  components: {
    upload,
    Department
  },
  data () {
    return {
      columns: columns,
      id: '',
      // 是否编辑页面
      edit: false,
      // 所选群聊分组id
      workRoomGroupId: 0,
      // 扫码名称
      qrcodeName: '',
      // 添加验证
      isVerified: 1,
      // 使用成员
      employees: [],
      // 入群引导语
      leadingWords: '',
      // 群聊
      rooms: [],
      // 标签
      tags: [],
      // 选中标签
      checkedTags: [],
      // 分组下拉
      group: [],
      // 新增标签
      addNewTagShow: false,
      // 标签分组列表
      tagsGroup: [],
      // 新增标签分组id
      tagGroupid: '',
      // 新增标签 分组名称
      tagGroupName: '',
      // 新增标签名称
      tagName: '',
      // 群聊
      groupChatShow: false,
      // 群聊列表
      groupChatTable: [],
      // 搜索群聊名称
      searchGroupWord: '',
      total: 0,
      // 群聊下拉
      groupChatList: [],
      // 上传群二维码所在索引
      uploadIndex: 0,
      // 企业成员
      choosePeopleShow: false,
      // 所选成员
      choosePeopleKey: [],
      tagShowMore: false,
      tagShowNum: 5
    }
  },
  computed: {
    ...mapGetters(['corpId'])
  },
  watch: {
    addNewTagShow (value) {
      if (!value) {
        this.tagName = ''
      }
    },
    groupChatShow (value) {
      if (!value) {
        this.groupChatTable = []
      }
    }
  },
  created () {
    const id = this.$route.query.id
    if (id) {
      this.edit = true
      this.id = id
      this.$setPageBreadcrumb('修改拉群')
      this.getDetail(id)
    } else {
      this.getTagGroups()
    }
    this.getGroupList()
  },
  methods: {
    // 获取分组
    async getGroupList () {
      const params = {
        corpId: this.corpId,
        page: 1,
        perPage: 100
      }
      try {
        const { data: { list } } = await workRoomGroupList(params)
        this.group = list
      } catch (e) {
        console.log(e)
      }
    },
    // 详情
    async getDetail (id) {
      try {
        const { data: { qrcodeName, isVerified, leadingWords, employees, tags, rooms, selectedTags } } = await autoPullShow({ workRoomAutoPullId: id })
        this.qrcodeName = qrcodeName
        this.isVerified = isVerified
        this.employees = employees
        this.tags = tags
        this.checkedTags = selectedTags || []
        this.leadingWords = leadingWords
        this.rooms = rooms
      } catch (e) {
        console.log(e)
      }
    },
    // 标签分组
    async getTagGroups () {
      try {
        const { data } = await workContactTagGroup()
        this.tagsGroup = data
        this.tags = data.map(item => {
          return {
            ...item,
            list: []
          }
        })
      } catch (e) {
        console.log(e)
      }
    },
    showMore () {
      this.tagShowMore = !this.tagShowMore
      if (this.tagShowMore) {
        this.tagShowNum = this.tags.length
      } else {
        this.tagShowNum = 5
      }
    },
    // 添加成员
    addNewPeople () {
      this.choosePeopleShow = true
    },
    // 成员更改
    peopleChange (data) {
      this.choosePeopleKey = data
    },
    // 选择成员 确定
    choosePeople () {
      if (this.choosePeopleKey.length == 0) {
        this.$message.warn('请选择企业成员')
        return
      }
      this.employees = [...this.choosePeopleKey]
      this.choosePeopleShow = false
    },
    // 获取所在分组标签
    async chooseTagGroup (groupId) {
      if (this.edit) {
        return
      }
      const tags = this.tags.filter(item => {
        return item.groupId == groupId
      })[0]
      const param = {
        groupId: groupId
      }
      try {
        const { data } = await tagList(param)
        tags.list = data.map(item => {
          return {
            tagId: item.id,
            tagName: item.name,
            isSelected: 2
          }
        })
      } catch (e) {
        console.log(e)
      }
    },
    // 选择标签
    chooseTag (item, tagId) {
      const index = this.checkedTags.indexOf(tagId)
      if (index > -1) {
        this.checkedTags.splice(index, 1)
      } else {
        this.checkedTags.push(tagId)
      }
      item.list.filter(inner => {
        if (inner.tagId == tagId) {
          inner.isSelected = index > -1 ? 2 : 1
          return true
        }
      })
    },
    // 新增标签
    addNewTag (groupId, groupName) {
      this.tagGroupid = groupId
      this.tagGroupName = groupName
      this.addNewTagShow = true
    },
    // 新增 确定
    async newTagConfirm () {
      if (!this.tagName) {
        this.$message.warn('请输入标签名称')
        return
      }
      const nameAry = this.tagName.split(' ')
      const name = nameAry.filter(item => {
        return item
      })
      const param = {
        groupId: this.tagGroupid,
        tagName: name
      }
      try {
        await addWorkContactTag(param)
      } catch (e) {
        console.log(e)
      }
      this.addNewTagShow = false
      const newTag = name.map(item => {
        return {
          tagName: item
        }
      })
      this.tags.map(item => {
        if (item.groupId == this.tagGroupid) {
          item.list = item.list.concat(newTag)
        }
      })
      this.$message.success('成功')
    },
    // 获取群聊数据
    async getRoomList () {
      const param = {
        roomGroupId: this.workRoomGroupId,
        name: this.searchGroupWord
      }
      try {
        const { data: { total, list } } = await roomList(param)
        this.groupChatList = list
        this.total = total
      } catch (e) {
        console.log(e)
      }
    },
    // 插入客户名称
    insert () {
      const textarea = document.getElementById('textarea')
      const start = textarea.selectionStart// input 第0个字符到选中的字符
      this.leadingWords = this.leadingWords.slice(0, start) + '##客户名称##' + this.leadingWords.slice(start)
    },
    // 选择群聊
    chooseChat () {
      this.groupChatShow = true
      this.getRoomList()
    },
    // 群聊 确定
    addNewChat () {
      if (this.groupChatTable.length == 0) {
        this.$message.warn('请选择群聊')
        return
      }
      const ary = this.groupChatTable.map(item => {
        return {
          roomId: item.roomId,
          roomName: item.roomName,
          num: item.currentNum,
          maxNum: '',
          roomMax: item.roomMax,
          roomQrcodeUrl: '',
          longRoomQrcodeUrl: ''
        }
      })
      this.rooms.push(...ary)
      this.groupChatShow = false
    },
    // 群聊是否选中
    judgeChecked (roomId) {
      const ary = this.groupChatTable.filter(item => {
        return item.roomId == roomId
      })
      return ary.length > 0
    },
    // 群聊选中 取消
    checkChange (e, data) {
      if (e.target.checked) {
        const ary = this.groupChatTable.filter(item => {
          return item.roomId == data.roomId
        })
        if (ary.length > 0) {
          this.$message.warn('该群聊已添加过，请不要重复选择')
          return
        }
        this.groupChatTable.push(data)
      } else {
        this.groupChatTable = this.groupChatTable.filter(item => {
          return item.roomId != data.roomId
        })
      }
    },
    // 删除群聊
    deleteGroup (data, index) {
      this.rooms.splice(index, 1)
    },
    // 上传
    upload (data, index) {
      this.uploadIndex = index
    },
    // 上传成功回调
    uploadSuccess (data) {
      const item = this.rooms[this.uploadIndex]
      item.longRoomQrcodeUrl = data.fullPath
      item.roomQrcodeUrl = data.path
    },
    async submit () {
      const employees = this.employees.map(item => {
        return item.employeeId
      })
      const rooms = this.rooms.map(item => {
        return {
          roomId: item.roomId,
          maxNum: item.maxNum,
          roomQrcodeUrl: item.roomQrcodeUrl
        }
      })
      let param = {
        isVerified: this.isVerified,
        employees: employees.join(','),
        tags: this.checkedTags.join(','),
        rooms: JSON.stringify(rooms)
      }
      if (this.edit) {
        param = {
          ...param,
          workRoomAutoPullId: this.id
        }
        try {
          await autoPullUpdate(param)
          this.$message.success('成功')
          this.$router.push({
            path: '/workRoomAutoPull/index'
          })
        } catch (e) {
          console.log(e)
        }
      } else {
        param = {
          ...param,
          corpId: this.corpId,
          qrcodeName: this.qrcodeName,
          leadingWords: this.leadingWords
        }
        try {
          await autoPullCreate(param)
          this.$message.success('成功')
          this.$router.push({
            path: '/workRoomAutoPull/index'
          })
        } catch (e) {
          console.log(e)
        }
      }
    }
  }
}
</script>

<style lang="less" scoped>
.new-pull {
  .service-wrapper {
    border: none;
  }
  .content {
    display: flex;
    .left-wrapper {
      flex: 0 0 350px;
      background:  #fff;
    }
    .right-wrapper {
      flex: 1
    }
  }
  .service {
    text-align: center;
    padding-bottom: 10px;
  }
  .chat-num {
    max-width: 100px
  }
  .right {
    height: auto;
    background:  #fff;
    margin: 0 15px;
    .form-item {
      margin-bottom: 15px;
    }
    .form-group {
      margin-bottom: 0;
    }
    .form-one {
      padding: 15px 0 0 15px;
    }
    .form-two{
      padding: 15px;
      .label {
        background: #fafafa;
        min-height: 100px;
      }
      .leading {
        background: #fafafa;
        padding: 10px;
        .ant-input {
          margin-bottom: 10px;
        }
        .ant-btn {
          margin: 0 0 20px 10px;
        }
      }
    }
    .employee {
      background: #fafafa;
      min-height: 100px;
      .tag {
        margin: 0 5px 10px 0
      }
    }
  }
  .btn-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 20px;
  }
  .tags-wrapper {
    padding: 0;
    .tag-item {
      padding-top: 10px;
      padding-left: 10px;
      display: flex;
      flex-wrap: wrap;
      .group-name {
        margin-right: 20px;
        margin-bottom: 5px;
        flex: 0 0 100px;
      }
      .tag-content {
        flex: 1
      }
      .tag {
        margin-bottom: 10px;
      }
      .add-new {
        margin-bottom: 10px;
      }
    }
  }
}
.label-wrapper {
  display: flex;
  margin-bottom: 20px;
  .text {
    flex: 0 0 120px;
    text-align: right;
    margin-right: 10px;
  }
  .containt {
    flex: 1;
    .select,.input {
      width: 100%;
      max-width: 400px;
    }
  }
}
.leading-wrapper {
  display: flex;
  .user-wrapper {
    flex: 0 0 30px;
    .user {
      color: #1890ff;
      font-size: 28px;
    }
  }
  .user-info {
    margin-top: 10px;
    border-radius: 10px;
    padding: 10px;
    word-break: break-word;
    background: rgba(0,0,0,.1);
    flex: 1;
  }
}
.total {
  margin-top:10px
}
.search {
  max-width: 400px;
}
.group-wrapper {
  padding: 0;
  .group-item {
    display: flex;
    margin-top: 10px;
    max-width: 460px;
    .group-left {
      flex: 0 0 40px;
      .user {
        font-size: 40px;
      }
    }
    .group-right {
      display: flex;
      flex: 1;
      max-width: 400px;
      margin-left: 10px;
      align-items: center;
      justify-content: space-between;
    }
  }
  .group-item:not(:last-child) {
    border-bottom: 1px solid rgba(0,0,0,.1);
  }
}

</style>
