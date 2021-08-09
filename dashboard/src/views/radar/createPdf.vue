<template>
  <div class="interactive_radar_pdf">
    <a-card class="mb16" title="基础信息">
      <div class="form">
        <div class="item must">
          <span>雷达标题：</span>
          <div class="input">
            <a-input placeholder="名称不会展示给用户，用于企业记录渠道名称或使用场景" v-model="formAskData.title"></a-input>
          </div>
        </div>
        <div class="item must">
          <span>雷达PDF：</span>
          <div class="input">
            <a-upload
              name="file"
              :multiple="false"
              :showUploadList="false"
              :headers="headers"
              accept=".pdf"
              :action="uploadUrl"
              @change="uploadPdfChange"
            >
              <a-button> <a-icon type="upload" /> 上传PDF文件 </a-button>
            </a-upload>
          </div>
        </div>
        <div class="pdfRow" v-if="showpdf">
          <a :href="formAskData.pdf" target="_blank">
            <div class="pdfStyle">
              <img class="pdfImg" src="../../assets/quick-reply-pdf-cover.png" alt="">
              <div class="fileName">{{ formAskData.pdf_name }}</div>
            </div>
          </a>
          <span class="delIcon" @click="delpdfFile"><a-icon type="close-circle" /></span>
        </div>
        <div class="item">
          <span>成员名片：</span>
          <div class="input flex">
            <a-switch v-model="checkData.employee_card"/>
            <div class="ml6" v-if="checkData.employee_card">已开启</div>
            <div class="ml6" v-else>已关闭</div>
            <div class="tips">开启后，将会在雷达PDF页面展示发送成员的名片</div>
          </div>
        </div>
      </div>
    </a-card>
    <a-card title="链接追踪设置">
      <div class="select">
        <div class="flex">
          <a-checkbox v-model="checkData.action_notice"/>
          <div class="desc">行为通知 （当客户点击雷达链接时，发送雷达链接的员工将会收到消息提醒）</div>
        </div>
        <div class="flex">
          <a-checkbox v-model="checkData.dynamic_notice"/>
          <div class="desc">动态通知（当客户点击雷达链接时，会将客户的打开行为记录在客户动态里）</div>
        </div>
        <div class="flex">
          <a-checkbox v-model="checkData.tag_status"/>
          <div class="desc">客户标签（给点击雷达链接的客户打上选中的标签）</div>
        </div>
        <div class="tag flex" v-if="checkData.tag_status">
          <a-button @click="$refs.childRef.show()">选择标签</a-button>
          <div class="tags ml16">
            <a-tag v-for="(item,index) in showContactTags" :key="index" class="tagStyle">
              {{ item.name }}
            </a-tag>
          </div>
        </div>
      </div>
      <!--   弹窗           -->
      <addlableIndex @choiceTagsArr="receiveData" ref="childRef"/>
      <div class="btn mt32">
        <a-button type="primary" size="large" @click="pdfSetup">创建雷达PDF</a-button>
      </div>
    </a-card>
  </div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import { storeApi } from '@/api/radar'
import addlableIndex from '@/components/addlabel'
import storage from 'store'

export default {
  components: { addlableIndex },
  computed: {
    headers () {
      const token = storage.get('ACCESS_TOKEN')
      return {
        Accept: `application/json`,
        Authorization: token
      }
    }
  },
  data () {
    return {
      uploadUrl: process.env.VUE_APP_API_BASE_URL + '/dashboard/common/uploadFile',
      // 控制复选框
      checkData: {
        // 成员名片
        employee_card: false,
        action_notice: true,
        //  动态通知
        dynamic_notice: true,
        tag_status: false
      },
      // 展示选中的标签
      showContactTags: [],
      formAskData: {
        type: 2,
        // 标题
        title: '',
        // 文件
        pdf: '',
        contact_tags: []
      },
      // 是否显示pdf文件
      showpdf: false
    }
  },
  methods: {
    // 上传
    uploadPdfChange (e) {
      if (e.file.response) {
        this.showpdf = true
        this.formAskData.pdf = e.file.response.data.fullPath
        this.formAskData.pdf_name = e.file.response.data.name
      }
    },
    // 删除pdf展示数据
    delpdfFile () {
      this.formAskData.pdf = ''
      this.formAskData.pdf_name = ''
      this.showpdf = false
    },
    // 接收子组件数据
    receiveData (e) {
      this.formAskData.contact_tags = []
      this.showContactTags = JSON.parse(JSON.stringify(e))
      e.forEach((item, index) => {
        const tagsArr = {
          tagid: '',
          tagname: ''
        }
        tagsArr.tagid = item.id
        tagsArr.tagname = item.name
        this.formAskData.contact_tags.push(tagsArr)
      })
    },
    // 创建
    pdfSetup () {
      if (this.formAskData.title == '') {
        this.$message.error('雷达标题不能为空')
        return false
      }
      if (this.formAskData.pdf == '') {
        this.$message.error('请上传PDF文件')
        return false
      }
      //  处理链接追踪设置
      this.transformData()
      if (this.formAskData.tag_status == 1) {
        if (this.formAskData.contact_tags == '') {
          this.$message.error('客户标签不能为空')
          return false
        }
      }
      storeApi(this.formAskData).then((res) => {
        this.$message.success('创建成功')
        this.$router.push({ path: '/radar/index' })
      })
    },
    //  处理链接追踪设置数据转换
    transformData () {
      if (this.checkData.employee_card) {
        this.formAskData.employee_card = 1
      } else {
        this.formAskData.employee_card = 0
      }
      if (this.checkData.action_notice) {
        this.formAskData.action_notice = 1
      } else {
        this.formAskData.action_notice = 0
      }
      if (this.checkData.dynamic_notice) {
        this.formAskData.dynamic_notice = 1
      } else {
        this.formAskData.dynamic_notice = 0
      }
      if (this.checkData.tag_status) {
        this.formAskData.tag_status = 1
      } else {
        this.formAskData.tag_status = 0
      }
    }
  }
}
</script>

<style lang="less" scoped>
.form {
  .item {
    display: flex;
    align-items: center;
    position: relative;
    margin-left: 6px;
    margin-bottom: 23px;

    span {
      width: 80px;
    }

    .input {
      width: 450px;
    }
  }

  .must:after {
    content: "*";
    color: red;
    position: absolute;
    left: -12px;
    top: 50%;
    transform: translateY(-50%);
  }
}
.tagStyle{
  font-size: 13px;
  padding: 5px 7px;
}
.select {
  .flex {
    margin-bottom: 16px;
  }

  .desc {
    margin-left: 10px;
  }
}
.tips {
  color: rgba(0, 0, 0, .45);
  font-size: 13px;
  line-height: 20px;
  margin-left: 16px;
}
.pdfRow{
  position: relative;
  width: 285px;
  height: 88px;
  display: inline-block;
  border-radius: 3px;
  border: 1px solid #dadada;
  padding: 10px 12px;
  margin-bottom: 26px;
  margin-left:85px;
}
.pdfStyle{
  display: flex;
}
.pdfImg{
  width: 64px;
  height: 64px;
}
.fileName{
  font-size: 14px;
  width: 160px;
  color: rgba(0,0,0,.85);
  font-weight: 500;
  margin-left: 10px;
}
.delIcon{
  position: absolute;
  font-size: 18px;
  top: -13px;
  right: -9px;
  cursor: pointer;
  color: #d1d1d1;
}

</style>
