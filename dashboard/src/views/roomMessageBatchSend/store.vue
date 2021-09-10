<template>
  <div class="bg-white-module">
    <div style="display: flex;">
      <div style="min-width: 600px;">
        <a-form-model ref="ruleForm" :model="form" :rules="rules" :label-col="labelCol" :wrapper-col="wrapperCol">
          <div class="title">基础信息</div>
          <a-form-model-item ref="batchTitle" label="群发名称" prop="batchTitle">
            <a-input v-model="form.batchTitle" placeholder="群发名称仅内部可见"/>
          </a-form-model-item>
          <a-form-model-item label="选择群主">
            <a-button @click="$refs.selectMember.setSelect(employees)">+点击选择</a-button>
            <div class="tag">
              <a-tag v-for="(v,i) in employees" :key="i">
                {{ v.name }}
              </a-tag>
            </div>
          </a-form-model-item>
          <a-form-model-item :wrapper-col="{ span: 14, offset: 4 }">
            <div class="hint">
              提示，群主收到群发任务后，可在企业微信上选择群聊发送拳法消息，
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
                <a-radio-group class="msg_type" v-model="msgType" @change="switchRadio">
                  <a-radio value="1">图片</a-radio>
                  <a-radio value="2">链接</a-radio>
                  <a-radio value="3">小程序</a-radio>
                </a-radio-group>
              </a-row>
              <!--          a1-->
              <a-row v-show="msgType == 1" style="min-width: 650px;">
                <upload @success="uploadImageSuccess" :file-type="1" ref="overImg" v-model="form.content.image.pic_url"></upload>
              </a-row>
              <a-row v-show="msgType == 2" style="min-width: 650px;">
                <a-form-model-item required :label-col="childLabelCol" :wrapper-col="wrapperCol" label="链接地址">
                  <a-input v-model="form.content.link.url" placeholder="请输入链接地址" style="width: 300px;"/>
                </a-form-model-item>
                <a-form-model-item :label-col="childLabelCol" :wrapper-col="wrapperCol" label="链接标题">
                  <a-input v-model="form.content.link.title" placeholder="请输入标题" style="width: 300px;"/>
                </a-form-model-item>
                <a-form-model-item :label-col="childLabelCol" :wrapper-col="wrapperCol" label="链接摘要">
                  <a-input v-model="form.content.link.desc" placeholder="请输入链接摘要" style="width: 300px;"/>
                </a-form-model-item>
                <a-form-model-item :label-col="childLabelCol" :wrapper-col="wrapperCol" label="链接封面">
                  <upload @success="uploadLinkSuccess" :file-type="1" ref="linkOver" v-model="form.content.link.picture"></upload>
                </a-form-model-item>
              </a-row>
              <a-row v-show="msgType == 3" style="min-width: 650px;">
                <a-form-model-item label="导入数据">
                  <a-button @click="importMedium('小程序')">+导入数据</a-button>
                </a-form-model-item>
                <a-form-model-item required label="图片封面" :label-col="childLabelCol" :wrapper-col="wrapperCol">
                  <upload
                    ref="appletImg"
                    @success="uploadMiniprogramSuccess"
                    :file-type="1"
                    v-model="form.content.miniprogram.pic_media_id"
                  ></upload>
                </a-form-model-item>
                <a-form-model-item required label="填写标题" :label-col="childLabelCol" :wrapper-col="wrapperCol">
                  <a-input
                    v-model="form.content.miniprogram.title"
                    placeholder="请填写小程序标题(4-12个字符)"
                    :maxLength="12"
                    style="width: 380px;"
                  />
                </a-form-model-item>
                <a-form-model-item required label="AppID" :label-col="childLabelCol" :wrapper-col="wrapperCol">
                  <a-input
                    v-model="form.content.miniprogram.appid"
                    placeholder="请填写小程序AppID,必须是关联到企业的小程序应用"
                    style="width: 380px;"
                  />
                </a-form-model-item>
                <a-form-model-item required label="page路径" :label-col="childLabelCol" :wrapper-col="wrapperCol">
                  <a-input v-model="form.content.miniprogram.page" placeholder="请填写小程序路径，例如：pages/index" style="width: 380px;"/>
                </a-form-model-item>
              </a-row>
            </a-row>
          </a-form-model-item>
          <a-form-model-item ref="sendWay" label="群发时间">
            <a-row>
              <a-radio-group v-model="form.sendWay">
                <a-radio value="1">立即发送</a-radio>
                <a-radio value="2">定时发送</a-radio>
              </a-radio-group>
            </a-row>
            <a-row v-if="form.sendWay == 2">
              <a-date-picker
                show-time
                placeholder="请选择时间"
                @change="selectTime"
              />
            </a-row>
          </a-form-model-item>
          <a-form-model-item :wrapper-col="{ span: 24, offset: 4 }">
            <div class="hint hint1">
              注意，每个群聊每个自然月最多接受来自同一企业管理员的4条群发消息。4条消息可在同一天发送
            </div>
          </a-form-model-item>
          <a-form-model-item :wrapper-col="{ span: 14, offset: 3 }">
            <a-button type="primary" :loading="btnLoading" :disabled="btnLoading" @click="onSubmit">通知成员立即发送
            </a-button
            >
          </a-form-model-item>
        </a-form-model>
      </div>
      <div style="margin-left: 30px;margin-top: 180px;">
        <m-preview ref="preview"/>
      </div>
    </div>
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
            <img :src="item.content.imageFullPath" alt=""/>
            <span>{{ item.content.title }}</span>
          </div>
        </div>
        <template slot="footer">
          <a-button @click="resetImport">取消</a-button>
          <a-button type="primary" @click="importDefined">确定</a-button>
        </template>
      </a-modal>
    </div>
    <selectMember ref="selectMember" @change="e=>employees = e"/>
  </div>
</template>
<script>
import department from '@/components/department'
import upload from '@/components/MaUpload'
// eslint-disable-next-line no-unused-vars
import { addMessage } from '@/api/roomMessageBatchSend'
import { materialLibraryList, mediumGroup } from '@/api/mediumGroup'
import selectMember from '@/components/Select/member'

export default {
  components: {
    department,
    upload,
    selectMember
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
        content: {
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
        batchTitle: [{ required: true, message: '请输入群发名称', trigger: 'blur' }],
        textContent: [{ required: true, message: '请输入消息内容', trigger: 'blur' }]
      },
      btnLoading: false,
      // 消息2类型
      msgType: '1',
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
      searchValue: ''
    }
  },
  mounted () {
  },
  created () {
  },
  watch: {
    /**
     * 欢迎语字数限制
     */
    'form.textContent' () {
      this.$refs.preview.setText(this.form.textContent)
      if (this.form.textContent.length > 400) {
        this.$message.warning('最多输入400个字')
        this.form.textContent = this.form.textContent.substring(0, 1500)
      }
    },
    // 链接监听
    'form.content.link': {
      deep: true,
      handler: function () {
        const data = this.form.content.link
        this.$nextTick(() => {
          this.$refs.preview.setLink(data.title, data.desc, data.pictureImg)
        })
      }
    },
    //  监听小程序
    'form.content.miniprogram': {
      deep: true,
      handler: function () {
        const data = this.form.content.miniprogram
        this.$nextTick(() => {
          this.$refs.preview.setApplets(data.title, data.pic_media_idImg)
        })
      }
    }
  },
  methods: {
    switchRadio () {
      if (this.msgType == 1) {
        this.$refs.preview.setImage(this.form.content.image.pic_urlImg)
        this.$refs.preview.setLink()
        this.$refs.preview.setApplets()
      } else if (this.msgType == 2) {
        this.$refs.preview.setImage()
        const linkOverData = this.form.content.link
        this.$refs.preview.setLink(linkOverData.title, linkOverData.desc, linkOverData.pictureImg)
        this.$refs.preview.setApplets()
      } else if (this.msgType == 3) {
        this.$refs.preview.setImage()
        this.$refs.preview.setLink()
        const appletsOverData = this.form.content.miniprogram
        this.$refs.preview.setApplets(appletsOverData.title, appletsOverData.pic_media_idImg)
      }
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
      let msgArray = {}
      const contentJSon = []
      this.employeeIdList = []
      // form.batchTitle
      if (this.form.batchTitle === '') {
        this.$message.warning('群发名称不能为空')
        return
      }
      this.employees.forEach((item, index) => {
        this.employeeIdList[index] = item.employeeId
      })
      if (this.employeeIdList === '') {
        this.$message.warning('请选择群主')
        return
      }
      if (this.form.textContent !== '') {
        msgArray = {
          msgType: 'text',
          content: this.form.textContent
        }
        contentJSon.push(msgArray)
      }

      if (this.msgType === '1') {
        delete dataJSon.content.image
        delete dataJSon.content.miniprogram
        if (this.form.textContent === '' && this.form.content.image.pic_url === '') {
          this.$message.warning('请上传图片')
          return
        }

        if (this.form.content.image.pic_url !== '') {
          msgArray = {
            msgType: 'image',
            media_id: new Date().getTime(),
            pic_url: this.form.content.image.pic_url
          }
          contentJSon.push(msgArray)
        }
      } else if (this.msgType === '2') {
        if (
          this.form.textContent === '' &&
          this.form.content.link.url.indexOf('http://') === -1 &&
          this.form.content.link.url.indexOf('https://') === -1
        ) {
          this.$message.warning('链接地址输入不正确')
          return
        }
        if (this.form.content.link.url !== '') {
          msgArray = {
            msgType: 'link',
            title: this.form.content.link.title,
            pic_url: this.form.content.link.picture,
            desc: this.form.content.link.desc,
            url: this.form.content.link.url
          }
          contentJSon.push(msgArray)
        }
      } else if (this.msgType === '3') {
        if (this.form.textContent === '') {
          if (this.form.content.miniprogram.pic_media_id === '') {
            this.$message.warning('请选择封面')
            return
          }
          if (this.form.content.miniprogram.title === '') {
            this.$message.warning('请填写标题')
            return
          }
          if (this.form.content.miniprogram.appid === '') {
            this.$message.warning('请输入appId')
            return
          }
          if (this.form.content.miniprogram.page === '') {
            this.$message.warning('请输入小程序路径')
            return
          }
        }

        if (this.form.content.miniprogram.page !== '') {
          msgArray = {
            msgType: 'miniprogram',
            title: this.form.content.miniprogram.title,
            pic_media_id: this.form.content.miniprogram.pic_media_id,
            appid: this.form.content.miniprogram.appid,
            page: this.form.content.miniprogram.page
          }
          contentJSon.push(msgArray)
        }
      }
      if (this.form.sendWay == 2) {
        if (this.form.definiteTime == '') {
          this.$message.warning('请选择时间')
          return
        }
      } else {
        dataJSon.definiteTime = this.getDateTime()
      }
      dataJSon.employeeIds = this.employeeIdList
      dataJSon.content = JSON.stringify(contentJSon)

      addMessage(dataJSon).then(_ => {
        this.btnLoading = false
        this.$message.success('内容已提交')
        this.$router.push('/roomMessageBatchSend/index')
      }).catch(e => {
        this.btnLoading = false
      })
    },
    selectTime (date, dateString) {
      this.form.definiteTime = dateString
    },
    // 获取当前时间
    getDateTime () {
      const yy = new Date().getFullYear()
      const mm = new Date().getMonth() + 1 < 10 ? '0' + (new Date().getMonth() + 1) : new Date().getMonth() + 1
      const dd = new Date().getDate() < 10 ? '0' + new Date().getDate() : new Date().getDate()
      const hh = new Date().getHours()
      const mf = new Date().getMinutes() < 10 ? '0' + new Date().getMinutes() : new Date().getMinutes()
      const ss = new Date().getSeconds() < 10 ? '0' + new Date().getSeconds() : new Date().getSeconds()
      const currentTime = yy + '-' + mm + '-' + dd + ' ' + hh + ':' + mf + ':' + ss
      return currentTime
    },
    /**
     * image类型  abc
     */
    uploadImageSuccess (res) {
      this.form.content.image.pic_urlImg = res.fullPath
      this.$refs.preview.setImage(res.fullPath)
    },
    /**
     * 链接图标
     */
    uploadLinkSuccess (res) {
      this.form.content.link.pictureImg = res.fullPath
    },
    /**
     * 小程序图标
     */
    uploadMiniprogramSuccess (res) {
      this.form.content.miniprogram.pic_media_idImg = res.fullPath
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
      mediumGroup()
        .then(res => {
          this.mediumGroupList = res.data
          this.mediumGroupId = 0
        })
        .catch(res => {
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

.title {
  font-size: 16px;
  padding-bottom: 15px;
  margin-bottom: 15px;
  border-bottom: 1px solid #f2f2f2;
}

.hint {
  margin-top: -15px;
  padding-left: 15px;
  margin-left: -50px;
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
</style>
