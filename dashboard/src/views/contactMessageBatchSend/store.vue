<template>
  <div class="bg-white-module">
    <div class="c">
      <div class="left">
        <a-form-model ref="ruleForm" :model="form" :label-col="labelCol" :wrapper-col="wrapperCol">
          <div class="title">基础信息</div>
          <a-form-model-item label="选择群发账号">
            <a-button @click="$refs.selectMember.show()">+点击选择</a-button>
            <div>
              <a-tag v-for="v in form.employeeIds" :key="v.id">
                {{ v.name }}
              </a-tag>
            </div>
          </a-form-model-item>
          <a-form-model-item ref="sendWay" label="选择客户">
            <a-row>
              <a-radio-group v-model="form.type">
                <a-radio :value="1">全部客户</a-radio>
                <a-radio :value="2">筛选客户</a-radio>
              </a-radio-group>
            </a-row>
            <a-row v-show="form.type == 2" class="client_module">
              <a-form-model-item required :label-col="childLabelCol" :wrapper-col="wrapperCol" label="性别">
                <a-radio-group v-model="form.filterParams.gender">
                  <a-radio :value="3">全部性别</a-radio>
                  <a-radio :value="1">仅男性粉丝</a-radio>
                  <a-radio :value="2">仅女性粉丝</a-radio>
                  <a-radio :value="0">未知性别</a-radio>
                </a-radio-group>
              </a-form-model-item>
              <a-form-model-item :label-col="childLabelCol" :wrapper-col="wrapperCol" label="所在群聊">
                <a-button @click="$refs.selectGroup.show()">选择群聊</a-button>
                <div>
                  <a-tag v-for="(item,index) in form.filterParams.rooms" :key="index">{{ item.name }}</a-tag>
                </div>
              </a-form-model-item>
              <a-form-model-item :label-col="childLabelCol" :wrapper-col="wrapperCol" label="添加时间">
                <a-range-picker @change="setUpTime" style="width: 280px;" />
              </a-form-model-item>
              <a-form-model-item :label-col="childLabelCol" :wrapper-col="wrapperCol" label="标签">
                <div @click="showModal(1)" class="choiceAdmin">
                  <span class="operationTips" v-if="form.filterParams.tags.length==0">请选择标签</span>
                  <a-tag v-for="(obj,idx) in form.filterParams.tags" :key="idx">
                    {{ obj.name }}
                    <a-icon type="close" @click.stop="delTagsArr(1,idx)" />
                  </a-tag>
                </div>
              </a-form-model-item>
              <a-form-model-item :label-col="childLabelCol" :wrapper-col="wrapperCol" label="排除客户">
                <div @click="showModal(2)" class="choiceAdmin">
                  <span class="operationTips" v-if="form.filterParams.excludeContacts.length==0">请选择标签</span>
                  <a-tag v-for="(obj,idx) in form.filterParams.excludeContacts" :key="idx">
                    {{ obj.name }}
                    <a-icon type="close" @click.stop="delTagsArr(2,idx)" />
                  </a-tag>
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
                <a-radio-group class="msg_type" v-model="msgType" @change="switchRadio">
                  <a-radio value="1">图片</a-radio>
                  <a-radio value="2">链接</a-radio>
                  <a-radio value="3">小程序</a-radio>
                </a-radio-group>
              </a-row>
              <a-row v-show="msgType == 1">
                <upload :file-type="1" ref="overImg" @success="uploadImageSuccess" v-model="form.content.image.pic_url"></upload>
              </a-row>
              <a-row v-show="msgType == 2">
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
                  <upload @success="uploadLinkSuccess" :file-type="1" ref="linkOver" v-model="form.content.link.picture"></upload>
                </a-form-model-item>
              </a-row>
              <a-row v-show="msgType == 3">
                <!--                <a-form-model-item label="导入数据">-->
                <!--                  <a-button @click="importMedium('小程序')">+导入数据</a-button>-->
                <!--                </a-form-model-item>-->
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
                <a-form-model-item required label="图片封面：" :label-col="childLabelCol" :wrapper-col="wrapperCol">
                  <upload :file-type="1" ref="appletImg" v-model="form.content.miniprogram.pic_media_id" @success="uploadMiniprogramSuccess"></upload>
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
              <a-date-picker show-time placeholder="请选择时间" @change="selectTime" />
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
      <div style="margin-left: 30px;margin-top: 180px;">
        <m-preview ref="preview"/>
      </div>
    </div>
    <!--    选择企业弹窗-->
    <selectMember ref="selectMember" @change="(e)=> form.employeeIds = e" />
    <!--    选择群聊弹窗-->
    <selectGroup ref="selectGroup" @change="(e)=>form.filterParams.rooms=e" />
    <!--    选择标签-->
    <addlableIndex ref="childRef" @choiceTagsArr="acceptArray"></addlableIndex>
  </div>
</template>
<script>
// eslint-disable-next-line no-unused-vars
import { storeApi } from '@/api/contactMessageBatchSend'
import selectMember from '@/components/Select/member'
import selectGroup from '@/components/Select/group'
import addlableIndex from '@/components/addlabel/index'
import upload from '@/components/MaUpload'
export default {
  components: {
    selectMember,
    selectGroup,
    addlableIndex,
    upload
  },
  data () {
    return {
      labelCol: { span: 4 },
      childLabelCol: { span: 4 },
      wrapperCol: { span: 20 },
      btnLoading: false,

      //  最新  abc
      form: {
        employeeIds: [],
        type: 1,
        filterParams: {
          //  性别
          gender: 3,
          // 群聊
          rooms: [],
          addTimeStart: '',
          addTimeEnd: '',
          // 标签
          tags: [],
          //  排除客户标签
          excludeContacts: []
        },
        textContent: '',
        definiteTime: '',
        sendWay: '1',
        content: {
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
      //  标签类型
      tagsType: 1,
      // 消息2类型
      msgType: '1'
    }
  },
  mounted () {
  },
  watch: {
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
    // 切换类型
    switchRadio () {
      console.log(this.msgType)
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
    // 小程序
    uploadMiniprogramSuccess (res) {
      this.form.content.miniprogram.pic_media_idImg = res.fullPath
    },
    // 链接类型图片上传
    uploadLinkSuccess (res) {
      this.form.content.link.pictureImg = res.fullPath
    },
    // 图片类型-上传图片
    uploadImageSuccess (res) {
      this.form.content.image.pic_urlImg = res.fullPath
      this.$refs.preview.setImage(res.fullPath)
    },
    // 选择时间
    selectTime (date, dateString) {
      this.form.definiteTime = dateString
    },
    onSubmit () {
      const dataJSon = JSON.parse(JSON.stringify(this.form))
      let msgArray = {}
      const contentJSon = []
      if (this.form.employeeIds.length === 0) {
        this.$message.warning('请选择群发账号')
        return
      }
      dataJSon.employeeIds = []
      this.form.employeeIds.forEach((item, index) => {
        dataJSon.employeeIds[index] = item.employeeId
      })
      if (this.form.type == 1) {
        dataJSon.filterParams = JSON.stringify({})
      } else {
        if (this.form.filterParams.rooms.length == 0) {
          dataJSon.filterParams.rooms = []
        } else {
          dataJSon.filterParams.rooms = []
          this.form.filterParams.rooms.forEach((item, index) => {
            dataJSon.filterParams.rooms[index] = item.id
          })
        }
        if (this.form.filterParams.addTimeStart == '') {
          this.$message.warning('筛选客户时间不能为空')
          return false
        }
        dataJSon.filterParams.tags = []
        if (this.form.filterParams.tags.length != 0) {
          this.form.filterParams.tags.forEach((item, index) => {
            dataJSon.filterParams.tags[index] = item.id
          })
        }
        dataJSon.filterParams.excludeContacts = []
        if (this.form.filterParams.excludeContacts.length != 0) {
          this.form.filterParams.excludeContacts.forEach((item, index) => {
            dataJSon.filterParams.excludeContacts[index] = item.id
          })
        }
        dataJSon.filterParams = JSON.stringify(dataJSon.filterParams)
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
        if (this.form.definiteTime === '') {
          this.$message.warning('请选择时间')
          return
        }
      } else {
        dataJSon.definiteTime = this.getDateTime()
      }

      dataJSon.content = JSON.stringify(contentJSon)
      storeApi(dataJSon).then((res) => {
        this.$message.success('创建成功')
        this.$router.push({ path: '/contactMessageBatchSend/index' })
      })
      // this.btnLoading = true
    },
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
    // 选择标签弹窗返回数据
    acceptArray (e) {
      if (this.tagsType == 1) {
        this.form.filterParams.tags = e
      } else {
        this.form.filterParams.excludeContacts = e
      }
    },
    // 选择标签
    showModal (type) {
      this.tagsType = type
      if (this.tagsType == 1) {
        this.$refs.childRef.show(this.form.filterParams.tags)
      } else {
        this.$refs.childRef.show(this.form.filterParams.excludeContacts)
      }
    },
    delTagsArr (type, idx) {
      if (type == 1) {
        this.form.filterParams.tags.splice(idx, 1)
      } else {
        this.form.filterParams.excludeContacts.splice(idx, 1)
      }
    },
    // 设置时间
    setUpTime (date, dateString) {
      this.form.filterParams.addTimeStart = dateString[0]
      this.form.filterParams.addTimeEnd = dateString[1]
    }
  }
}
</script>
<style lang="less" scoped>
.choiceAdmin{
  width: 300px;
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
