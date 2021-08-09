<template>
  <div class="interactive_radar_pdf">
    <a-spin size="large" :spinning="loadShow">
      <a-card class="mb16" title="基础信息">
        <div class="form">
          <div class="item must">
            <span>雷达标题：</span>
            <div class="input">
              <a-input placeholder="名称不会展示给用户，用于企业记录渠道名称或使用场景" v-model="formAskData.title" disabled></a-input>
            </div>
          </div>
          <div class="item must">
            <span>雷达文章：</span>
            <div class="input">
              <a-radio-group v-model="formAskData.article_type" @change="tabTitleType">
                <a-radio :value="1">提取公众号文章</a-radio>
                <a-radio :value="2">新建文章素材</a-radio>
              </a-radio-group>
            </div>
          </div>
          <div class="add_title" v-if="formAskData.article_type==1">
            <div class="tips">粘贴公众号文章链接，自动提取文章内容转化为雷达文章，追踪客户阅读和分享行为<a href="">如何提取公众号文章？</a></div>
            <div><a-textarea placeholder="请在此粘贴公众号文章链接" :rows="4" v-model="textFieldlink" /></div>
            <a-button type="primary" style="margin-top: 15px;" @click="createTitle">生成雷达文章</a-button>
          </div>
          <div class="add_title" v-else>
            <a-button type="primary" @click="customContent">新建文章素材</a-button>
          </div>
          <div class="source_title" v-show="showRichText">
            <addSourceTitle ref="addSourceTitle" @change="receiveCustomData" />
          </div>
          <div class="pdfRow" v-if="formAskData.article_type==2&&!showRichText&&JSON.stringify(customData) !='{}'">
            <div class="pdfStyle">
              <img class="pdfImg" :src="customData.cover_url" alt="">
              <div class="fileName">
                <div>{{ customData.title }}</div>
                <div style="font-size: 12px;color: gray;">{{ customData.desc }}</div>
              </div>
            </div>
          </div>

          <div class="pdfRow" v-if="showpdf">
            <div class="pdfStyle">
              <img class="pdfImg" :src="formAskData.article.cover_url" alt="">
              <div class="fileName">
                <div>{{ formAskData.article.title }}</div>
                <div style="font-size: 12px;color: gray;">{{ formAskData.article.desc }}</div>
              </div>
            </div>
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
          <a-button type="primary" size="large" @click="pdfSetup">创建雷达文章</a-button>
        </div>
      </a-card>
    </a-spin>
  </div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import { infoApi, updateApi, radarArticleApi } from '@/api/radar'
import addlableIndex from '@/components/addlabel'
import addSourceTitle from '@/views/radar/components/addSourceTitle'
import storage from 'store'

export default {
  components: { addlableIndex, addSourceTitle },
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
      // 显示富文本组件
      showRichText: false,
      // 文本域
      textFieldlink: '',
      loadShow: false,
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
        type: 3,
        // 标题
        title: '',
        // 雷达文章类型
        article_type: 1,
        // 雷达文章
        article: {},
        contact_tags: []
      },
      customData: {},
      // 是否显示pdf文件
      showpdf: false
    }
  },
  created () {
    this.id = this.$route.query.id
    this.getDataTitle(this.id)
  },
  methods: {
    // 获取标题数据
    getDataTitle (id) {
      infoApi({ id }).then((res) => {
        this.formAskData.id = id
        this.formAskData.title = res.data.title
        this.formAskData.article_type = res.data.articleType
        if (this.formAskData.article_type == 1) {
          this.formAskData.article = res.data.article
          this.showpdf = true
          this.textFieldlink = this.formAskData.article.url
        } else {
          this.customData = res.data.article
          this.formAskData.article = res.data.article
        }
        //  包装后台数据
        this.packAcceptData(res.data)
      })
    },
    // 包装后台数据
    packAcceptData (data) {
      if (data.employeeCard == 1) {
        this.checkData.employee_card = true
      } else {
        this.checkData.employee_card = false
      }
      if (data.actionNotice == 1) {
        this.checkData.action_notice = true
      } else {
        this.checkData.action_notice = false
      }
      if (data.dynamicNotice == 1) {
        this.checkData.dynamic_notice = true
      } else {
        this.checkData.dynamic_notice = false
      }
      if (data.tagStatus == 1) {
        this.checkData.tag_status = true
      } else {
        this.checkData.tag_status = false
      }
      // 处理客户标签
      if (this.checkData.tag_status) {
        this.formAskData.contact_tags = data.contactTags
        this.formAskData.contact_tags.forEach((item, index) => {
          const tagArr = {
            id: '',
            name: ''
          }
          tagArr.id = item.tagid
          tagArr.name = item.tagname
          this.showContactTags.push(tagArr)
        })
      }
    },
    // 创建
    pdfSetup () {
      if (this.formAskData.title == '') {
        this.$message.error('雷达标题不能为空')
        return false
      }
      if (this.formAskData.article_type == 2) {
        this.formAskData.article = this.customData
      }
      if (JSON.stringify(this.formAskData.article) == '{}') {
        this.$message.error('雷达文章不能为空')
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
      updateApi(this.formAskData).then((res) => {
        this.$message.success('修改成功')
        this.$router.push({ path: '/radar/index' })
      })
    },
    // 接收自定义数据
    receiveCustomData (e) {
      this.customData = e
      this.showRichText = false
    },
    // 自定义文章
    customContent () {
      this.showRichText = true
      this.$refs.addSourceTitle.setUpCustomData(this.customData)
    },
    // 切换文章类型
    tabTitleType () {
      this.showpdf = false
      this.showRichText = false
      this.formAskData.article = {}
      this.textFieldlink = ''
    },
    // 生成雷达文章
    createTitle () {
      if (this.textFieldlink == '') {
        this.$message.warning('未填写文章链接')
        return false
      }
      radarArticleApi({
        url: this.textFieldlink
      }).then((res) => {
        setTimeout(() => {
          this.$message.success('生成成功')
          this.formAskData.article = res.data
          this.showpdf = true
        }, 500)
      })
    },
    // 删除pdf展示数据
    delpdfFile () {
      this.showpdf = false
      this.formAskData.article = {}
      this.textFieldlink = ''
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
.add_title{
  margin-left: 85px;
  margin-bottom: 20px;
  .tips{
    width: 800px;
    margin-left: 0px;
    color: rgba(0,0,0,.65);
    background: #effaff;
    border-radius: 2px;
    padding: 6px 12px;
    margin-bottom: 12px;
  }
  textarea{
    width: 800px;
    resize:none;
  }
}
.source_title{
  width: 800px;
  margin-left: 85px;
  margin-bottom: 20px;
}
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
  div{
    width:190px;
    overflow : hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
  }
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
