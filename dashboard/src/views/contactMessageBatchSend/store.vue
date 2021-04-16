<template>
  <div class="bg-white-module">
    <div class="c">
      <div class="left">
        <a-form-model ref="ruleForm" :model="form" :rules="rules" :label-col="labelCol" :wrapper-col="wrapperCol">
          <div class="title">基础信息</div>
          <a-form-model-item label="选择群发账号">
            <a-button @click="choosePeopleShow = true">+点击选择</a-button>
          </a-form-model-item>
          <a-form-model-item ref="sendWay" label="选择客户">
            <a-row>
              <a-radio-group v-model="form.user">
                <a-radio value="1">全部客户</a-radio>
                <a-radio value="2">筛选客户</a-radio>
              </a-radio-group>
            </a-row>
            <a-row v-if="form.user == 2" class="client_module">
              <a-form-model-item required :label-col="childLabelCol" :wrapper-col="wrapperCol" label="性别">
                <a-radio-group v-model="form.user">
                  <a-radio value="1">全部性别</a-radio>
                  <a-radio value="2">仅男性粉丝</a-radio>
                  <a-radio value="3">仅女性粉丝</a-radio>
                  <a-radio value="4">未知性别</a-radio>
                </a-radio-group>
              </a-form-model-item>
              <a-form-model-item required :label-col="childLabelCol" :wrapper-col="wrapperCol" label="所在群聊">
                <a-select mode="tags" placeholder="请选择标签" @change="handleChange"> </a-select>
              </a-form-model-item>
              <a-form-model-item :label-col="childLabelCol" :wrapper-col="wrapperCol" label="添加时间">
                <a-range-picker @change="onChange" />
              </a-form-model-item>
              <a-form-model-item :label-col="childLabelCol" :wrapper-col="wrapperCol" label="标签">
                <div class="tag_client" @click="tagVisible = true">
                  <span class="text">请选择标签</span>
                  <a-icon class="icon" type="caret-down" />
                </div>
              </a-form-model-item>
              <a-form-model-item :label-col="childLabelCol" :wrapper-col="wrapperCol" label="排除客户">
                <div class="tag_client" @click="tagVisible = true">
                  <div class="tag">
                    <a-tag>Tag 1</a-tag>
                    <a-tag><a href="https://github.com/vueComponent/ant-design">Link</a></a-tag>
                    <a-tag closable @close="log"> Tag 2 </a-tag>
                    <a-tag closable @close="preventDefault"> Prevent Default </a-tag>
                  </div>
                  <a-icon class="icon" type="caret-down" />
                </div>
                <div class="hint_text">
                  可根据标签选择客户，群发时将不会发送给该标签内的客户。若选择了排除的客户，需要较长的时间创建本条群发消息哦
                </div>
                <div class="hint_text hint_text1">将消息发送给符合条件的粉丝</div>
              </a-form-model-item>
            </a-row>
            <div class="hint">
              将群发消息给全部账号的查看客户
              <span></span>
            </div>
          </a-form-model-item>
          <div class="title">编辑群发消息</div>
          <a-form-model-item ref="textContent" label="群发消息1:" prop="textContent">
            <a-row class="msg_module">
              <a-input
                class="form_textarea"
                v-model="form.textContent"
                :autoSize="{ minRows: 6 }"
                :maxLength="400"
                type="textarea"
                placeholder="请输入回复内容"
              />
              <div class="text_num">{{ form.textContent.length }}/400</div>
            </a-row>
          </a-form-model-item>
          <a-form-model-item ref="name6" label="群发消息2:">
            <a-row class="msg_module">
              <a-row>
                选择消息类型
                <a-radio-group class="msg_type" v-model="msgType">
                  <a-radio value="1">图片</a-radio>
                  <a-radio value="2">链接</a-radio>
                  <a-radio value="3">小程序</a-radio>
                </a-radio-group>
              </a-row>
              <a-row v-if="msgType == 1">
                <upload :imageUrl="form.content.image.pic_url" @success="uploadImageSuccess" :file-type="1"></upload>
              </a-row>
              <a-row v-if="msgType == 2">
                <a-form-model-item required :label-col="childLabelCol" :wrapper-col="wrapperCol" label="链接地址：">
                  <a-input v-model="form.content.link.url" placeholder="请输入链接地址" />
                </a-form-model-item>
                <a-form-model-item :label-col="childLabelCol" :wrapper-col="wrapperCol" label="链接标题：">
                  <a-input v-model="form.content.link.title" placeholder="请输入标题" />
                </a-form-model-item>
                <a-form-model-item :label-col="childLabelCol" :wrapper-col="wrapperCol" label="链接摘要：">
                  <a-input v-model="form.content.link.desc" placeholder="请输入链接摘要" />
                </a-form-model-item>
                <a-form-model-item :label-col="childLabelCol" :wrapper-col="wrapperCol" label="链接封面：">
                  <upload :imageUrl="form.content.link.picture" @success="uploadLinkSuccess" :file-type="1"></upload>
                </a-form-model-item>
              </a-row>
              <a-row v-if="msgType == 3">
                <a-form-model-item label="导入数据">
                  <a-button @click="importMedium('小程序')">+导入数据</a-button>
                </a-form-model-item>
                <a-form-model-item required label="图片封面：" :label-col="childLabelCol" :wrapper-col="wrapperCol">
                  <upload
                    :imageUrl="form.content.miniprogram.pic_media_id"
                    @success="uploadMiniprogramSuccess"
                    :file-type="1"
                  ></upload>
                </a-form-model-item>
                <a-form-model-item required label="填写标题：" :label-col="childLabelCol" :wrapper-col="wrapperCol">
                  <a-input
                    v-model="form.content.miniprogram.title"
                    placeholder="请填写小程序标题(4-12个字符)"
                    :maxLength="12"
                  />
                </a-form-model-item>
                <a-form-model-item required label="AppID" :label-col="childLabelCol" :wrapper-col="wrapperCol">
                  <a-input
                    v-model="form.content.miniprogram.appid"
                    placeholder="请填写小程序AppID,必须是关联到企业的小程序应用"
                  />
                </a-form-model-item>
                <a-form-model-item required label="page路径：" :label-col="childLabelCol" :wrapper-col="wrapperCol">
                  <a-input v-model="form.content.miniprogram.page" placeholder="请填写小程序路径，例如：pages/index" />
                </a-form-model-item>
              </a-row>
            </a-row>
          </a-form-model-item>
          <a-form-model-item ref="sendWay" label="群发时间：">
            <a-row>
              <a-radio-group v-model="form.sendWay">
                <a-radio value="1">立即发送</a-radio>
                <a-radio value="2">定时发送</a-radio>
              </a-radio-group>
            </a-row>
            <a-row v-if="form.sendWay == 2">
              <a-date-picker show-time placeholder="请选择时间" v-model="form.definiteTime" />
            </a-row>
            <div class="hint hint1">
              注意，客户每个月最多接收来自同一企业的管理员的4条群发消息，4条消息可在同一天发送
            </div>
          </a-form-model-item>
          <a-form-model-item :wrapper-col="{ span: 14, offset: 4 }">
            <a-button type="primary" :loading="btnLoading" :disabled="btnLoading" @click="onSubmit">通知成员立即发送</a-button
            >
          </a-form-model-item>
        </a-form-model>
      </div>
      <div class="right">
        <div class="msg_title">客户收到的消息</div>
        <msgModel :width="300"></msgModel>
      </div>
    </div>
    <a-modal
      title="选择标签"
      :maskClosable="false"
      :footer="null"
      :width="600"
      :visible="tagVisible"
      @cancel="tagVisible = false"
    >
      <div class="select_tag">
        <a-tabs type="card" default-active-key="1" @change="callback">
          <a-tab-pane key="1" tab="以下标签满足其中之一"> </a-tab-pane>
          <a-tab-pane key="2" tab="以下标签同时满足" force-render> </a-tab-pane>
        </a-tabs>
        <div class="text">
          <a-icon type="bell" class="icon" />
          <div>
            可根据标签选择客户，群发时将不会发送给该标签内的客户。若选择了排除的客户，需要较长的时间创建本条群发消息哦~
          </div>
        </div>
        <a-input-search placeholder="请输入企业标签" />
        <div class="grade">
          <span :style="{ marginRight: 8 }">请选择客户等级：</span>
          <template v-for="tag in tags">
            <a-checkable-tag
              :key="tag"
              :checked="selectedTags.indexOf(tag) > -1"
              @change="(checked) => handleChange(tag, checked)"
            >
              {{ tag }}
            </a-checkable-tag>
          </template>
        </div>
        <div class="btns">
          <a-button type="defaule" @click="tagVisible = false"> 取消 </a-button>
          <a-button class="savebtn" @click="tagVisible = false" type="primary"> 保存 </a-button>
        </div>
      </div>
    </a-modal>
    <a-modal
      title="选择企业成员"
      :maskClosable="false"
      :width="700"
      :visible="choosePeopleShow"
      @cancel="
        () => {
          this.choosePeopleShow = false
          this.employeeIdList = ''
          this.employees = []
        }
      "
    >
      <department
        v-if="choosePeopleShow"
        :isSelected="selectList"
        :isChecked="employees"
        :memberKey="employees"
        @change="peopleChange"
        @search="searchEmp"
      ></department>
      <template slot="footer">
        <a-button @click="cancel">取消</a-button>
        <a-button type="primary" @click="choosePeopleShow = false">确定</a-button>
      </template>
    </a-modal>
    <div class="pbox" ref="pbox">
      <a-modal
        width="800px"
        title="选择素材"
        :getContainer="() => $refs.pbox"
        :visible="importModal"
        @cancel="
          () => {
            this.importModal = false
          }
        "
      >
        <a-row>
          <a-col :lg="8">
            <a-select v-model="mediumGroupId" @change="changeGroup" style="width: 100%">
              <a-select-option v-for="item in mediumGroupList" :key="item.id">
                {{ item.name }}
              </a-select-option>
            </a-select>
          </a-col>
          <a-col :lg="8" :offset="1">
            <a-input-search
              v-model="searchValue"
              placeholder="输入要搜索的内容"
              enter-button="搜索"
              @search="getMaterialLibrary"
            />
          </a-col>
          <a-col :lg="3">
            <a-button @click="searchValue = ''">清空</a-button>
          </a-col>
        </a-row>
        <div class="picture-box">
          <div class="nothing" v-if="materialData.length === 0">暂无数据</div>
          <div
            :class="mediumId === item.id ? 'active' : null"
            v-else
            v-for="item in materialData"
            :key="item.id"
            @click="mediumImport(item.id, item.content)"
          >
            <img :src="item.content.imageFullPath" alt="" />
            <span>{{ item.content.title }}</span>
          </div>
        </div>
        <template slot="footer">
          <a-button @click="resetImport">取消</a-button>
          <a-button type="primary" @click="importDefined">确定</a-button>
        </template>
      </a-modal>
    </div>
  </div>
</template>
<script>

import department from '@/components/department'
import upload from '@/components/upload'
import msgModel from '@/components/MsgModel'
import { addMessage } from '@/api/roomMessageBatchSend'
import { materialLibraryList, mediumGroup } from '@/api/mediumGroup'
export default {
  components: {
    department,
    upload,
    msgModel
  },
  data () {
    return {
      labelCol: { span: 3 },
      childLabelCol: { span: 3 },
      wrapperCol: { span: 20 },
      // 成员显示
      choosePeopleShow: false,
      // 成员列表
      employeeIdList: '',
      // 已选成员
      employees: [],
      // 成员人数
      employeeNum: 0,
      selectList: [],
      form: {
        batchTitle: '',
        employeeIds: '',
        textContent: '',
        definiteTime: '',
        sendWay: '1',
        user: '2',
        content: {
          msgType: '',
          textContent: '',
          // 链接
          link: {
            url: '',
            title: '',
            desc: '',
            picture: ''
          },
          // 图片
          image: {
            media_id: '',
            pic_url: ''
          },
          // 小程序
          miniprogram: {
            title: '',
            pic_media_id: '',
            appid: '',
            page: ''
          }
        }
      },
      rules: {
        batchTitle: [
          { required: true, message: '请输入群发名称', trigger: 'blur' }
        ],
        textContent: [{ required: true, message: '请输入消息内容', trigger: 'blur' }]
      },
      btnLoading: false,
      // 消息2类型
      msgType: '3',
      isImport: 0,
      mediumId: 0,
      mediumTitle: '',
      // 弹窗选择
      importModal: false,
      // 素材库分组列表
      mediumGroupList: [],
      // 素材列表
      materialData: [],
      // 素材库分组id
      mediumGroupId: '',
      // 搜索内容
      searchValue: '',
      tagVisible: false,
      tags: ['Movies', 'Books', 'Music', 'Sports'],
      selectedTags: []
    }
  },
  mounted () {
  },
  methods: {
    handleChange (tag, checked) {
      const { selectedTags } = this
      const nextSelectedTags = checked
        ? [...selectedTags, tag]
        : selectedTags.filter(t => t !== tag)
      console.log('You are interested in: ', nextSelectedTags)
      this.selectedTags = nextSelectedTags
    },
    // 成员选择
    peopleChange (data) {
      const arr = []
      data.map(item => {
        arr.push(item.employeeId)
      })
      this.employeeNum = arr.length
      this.employeeIdList = arr.join(',')
    },
    searchEmp (data) {
      this.employeeNum = data.length
      if (data.length === 0) {
        this.employeeIdList = '空'
      } else {
        this.employeeIdList = data[0].id
      }
    },
    onSubmit () {
      const dataJSon = JSON.parse(JSON.stringify(this.form))
      if (this.form.textContent == '') {
        this.$message.warning('请输入消息')
        return
      }
      if (this.employeeIdList == '') {
        this.$message.warning('请选择群主')
        return
      }
      if (this.msgType === '1') {
        delete dataJSon.content.image
        delete dataJSon.content.miniprogram
        if (this.form.content.image.pic_url == '') {
          this.$message.warning('请上传图片')
          return
        }
      } else if (this.msgType === '2') {
        delete dataJSon.content.miniprogram
        delete dataJSon.content.image
        if (this.form.content.link.url.indexOf('http://') == -1 && this.form.content.link.url.indexOf('https://') == -1) {
          this.$message.warning('链接地址输入不正确')
          return
        }
      } else if (this.msgType === '3') {
        delete dataJSon.content.link
        delete dataJSon.content.image
        if (this.form.content.miniprogram.pic_media_id == '') {
          this.$message.warning('请选择封面')
          return
        }
        if (this.form.content.miniprogram.title == '') {
          this.$message.warning('请填写标题')
          return
        }
        if (this.form.content.miniprogram.appid == '') {
          this.$message.warning('请输入appId')
          return
        }
        if (this.form.content.miniprogram.page == '') {
          this.$message.warning('请输入小程序路径')
          return
        }
      }
      if (this.form.sendWay == 2) {
        if (this.form.definiteTime == '') {
          this.$message.warning('请选择时间')
          return
        }
      }
      dataJSon.employeeIds = this.employeeIdList
      console.log('sdfg:' + JSON.stringify(dataJSon))
      this.btnLoading = true
      addMessage(dataJSon).then(_ => {
        this.btnLoading = false
        this.$message.success('内容已提交')
        this.$router.push('/roomMessageBatchSend/index')
      }).catch(e => {
        this.btnLoading = false
      })
    },
    /**
     * image类型
     */
    uploadImageSuccess (res) {
      this.form.content.image.pic_url = res.fullPath
    },
    /**
     * 链接图标
     */
    uploadLinkSuccess (res) {
      this.form.content.link.picture = res.fullPath
    },
    /**
     * 小程序图标
     */
    uploadMiniprogramSuccess (res) {
      this.form.content.miniprogram.pic_media_id = res.fullPath
    },
    /**
     * 选择群主取消
     */
    cancel () {
      this.choosePeopleShow = false
      this.employeeIdList = ''
      this.employees = []
    },
    // 导入
    importMedium () {
      this.importModal = true
      this.mediumType = 6
      this.getMaterialLibrary()
    },
    // 导入选择
    mediumImport (id, content) {
      this.mediumId = id
      this.form.content.miniprogram = {
        title: content.title,
        pic_media_id: content.imageFullPath,
        appid: content.appid,
        page: content.page
      }
    },
    // 素材库分组改变
    changeGroup (value) {
      this.mediumGroupId = value
      this.getMaterialLibrary()
    },
    // 获取素材库
    getMaterialLibrary () {
      materialLibraryList({
        mediumGroupId: this.mediumGroupId,
        searchValue: this.searchValue,
        type: this.mediumType
      }).then(res => {
        this.materialData = res.data.list
      })
      this.getMediumGroup()
    },
    // 获取素材库分组列表
    getMediumGroup () {
      mediumGroup().then(res => {
        this.mediumGroupList = res.data
        this.mediumGroupId = 0
      }).catch(res => {
        this.mediumGroupId = ''
      })
    },
    // 导入确定
    importDefined () {
      if (this.mediumId == '') {
        this.$message.error('请选择素材')
        return
      }
      this.importModal = false
    },
    // 取消导入
    resetImport () {
      this.importModal = false
      this.mediumDetail = {}
      this.mediumTitle = ''
      this.mediumId = ''
    }
  }
}
</script>
<style lang="less" scoped>
.bg-white-module {
  background: #ffffff;
  padding: 20px;
  border-radius: 5px;
}
.c {
  display: flex;
  justify-content: flex-start;
  flex-wrap: nowrap;
  .left {
    flex-shrink: 1;
    max-width: 877px;
    margin-right: 40px;
  }
  .right {
    width: 300px;
    flex-shrink: 0;
    padding-top: 100px;
    .msg_title {
      text-align: center;
      padding-bottom: 10px;
    }
  }
}
.msg_module {
  background: #fbfbfb;
  padding: 15px;
  border-radius: 5px;
  color: #999;
  .msg_type {
    margin-left: 15px;
  }
  .form_textarea {
    background: none;
    border: 0;
    padding: 0;
    resize: none;
  }
  .form_textarea:focus {
    box-shadow: none;
  }
  .text_num {
    line-height: 20px;
  }
}
.client_module {
  background: #fbfbfb;
  padding: 15px;
  border-radius: 5px;
  padding-bottom: 0;
  .ant-calendar-picker {
    width: 100%;
  }
  .tag_client {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-right: 15px;
    border: 1px solid #d9d9d9;
    background-color: #fff;
    cursor: pointer;
    flex-wrap: nowrap;
    span.text {
      color: #999;
      text-indent: 15px;
    }
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
    }
  }
  .hint_text {
    color: #999;
    line-height: 25px;
    margin-top: 10px;
  }
  .hint_text1 {
    margin-left: -70px;
    padding-top: 15px;
    text-indent: 15px;
    border-top: 1px dashed #d9d9d9;
  }
}
.title {
  font-size: 16px;
  padding-bottom: 15px;
  margin-bottom: 15px;
  margin-right: 25px;
  border-bottom: 1px solid #f2f2f2;
}

.hint {
  margin-left: 0px;
  margin-top: 10px;
  padding: 10px 15px;
  line-height: 20px;
  background: #ecf8fe;
}

.pbox {
  .picture-box {
    width: 100%;
    padding: 10px;
    display: flex;
    flex-wrap: wrap;
    div {
      width: 150px;
      height: 150px;
      border-radius: 4px;
      margin: 5px;
      position: relative;
      img {
        width: 100%;
        height: auto;
        max-height: 100%;
        border-radius: 4px;
      }
      span {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 35px;
        background: #000;
        opacity: 0.4;
        color: #fff;
        line-height: 35px;
        padding: 0 5px;
        border-radius: 0 0 4px 4px;
      }
    }
    .active {
      width: 150px;
      height: 150px;
      border-radius: 4px;
      margin: 5px;
      position: relative;
      border: 2px solid blue;
      img {
        width: 100%;
        height: auto;
        max-height: 100%;
        border-radius: 4px;
      }
      span {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 35px;
        background: #000;
        opacity: 0.4;
        color: #fff;
        line-height: 35px;
        padding: 0 5px;
        border-radius: 0 0 4px 4px;
      }
    }
    .nothing {
      margin: 20px auto;
    }
  }
}
.select_tag {
  .grade {
    margin-top: 15px;
  }
  .text {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    background-color: #effaff;
    padding: 10px;
    flex-wrap: nowrap;
    margin-bottom: 15px;
    .icon {
      color: #1990ff;
      margin-right: 10px;
    }
  }
  .btns {
    display: flex;
    margin-top: 15px;
    justify-content: flex-end;
    .savebtn {
      margin-left: 15px;
    }
  }
}
</style>
