<template>
  <div class="interactive_radar_link">
    <a-card class="mb16" title="基础信息">
      <div class="form">
        <!--        标题-->
        <div class="item must">
          <span>雷达标题：</span>
          <div class="input">
            <a-input placeholder="名称不会展示给用户，用于企业记录渠道名称或使用场景" v-model="formAskData.title" disabled></a-input>
          </div>
        </div>
        <!--        链接-->
        <div class="item must">
          <span>雷达链接：</span>
          <div class="input">
            <a-input placeholder="请在此输入链接" v-model="formAskData.link"></a-input>
          </div>
        </div>
        <!--        链接标题-->
        <div class="item must mt38">
          <span>链接标题：</span>
          <div class="input">
            <a-input placeholder="请在此输入链接标题" v-model="formAskData.link_title"></a-input>
          </div>
        </div>
        <!--        链接标题-->
        <div class="item must mt38">
          <span>链接摘要：</span>
          <div class="input">
            <a-textarea
              placeholder="请在此输入链接摘要"
              :rows="5"
              :autosize="false"
              v-model="formAskData.link_description"
              style="resize: none;"
            ></a-textarea>
          </div>
        </div>
        <!--        封面-->
        <div class="item must">
          <span>链接封面：</span>
          <div class="input">
            <m-upload @change="linkCover" ref="overlinkImg" />
          </div>
        </div>
      </div>
    </a-card>
    <!--    链接追踪设置-->
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
        <!--   弹窗           -->
        <addlableIndex @choiceTagsArr="acceptArray" ref="childRef"/>
      </div>
      <div class="btn mt32">
        <a-button type="primary" size="large" @click="linkSetup">
          修改雷达链接
        </a-button>
      </div>
    </a-card>
  </div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import { updateApi, infoApi } from '@/api/radar'
import addlableIndex from '@/components/addlabel'
export default {
  components: { addlableIndex },
  data () {
    return {
      // 控制复选框
      checkData: {
        action_notice: true,
        //  动态通知
        dynamic_notice: true,
        // 成员名片
        tag_status: false
      },
      // 封面图片
      overImg: 0,
      //  展示标签
      showContactTags: [],
      // 提交表单
      formAskData: {
        type: 1,
        // 标题
        title: '',
        //  链接
        link: '',
        //  标题
        link_title: '',
        //  摘要
        link_description: '',
        // 封面
        link_cover: '',
        //  行为通知
        action_notice: 1,
        //  动态通知
        dynamic_notice: 1,
        // 成员名片
        tag_status: 0,
        //  客户标签
        contact_tags: []
      }
    }
  },
  created () {
    this.id = this.$route.query.id
    this.getDetailsData({
      id: this.id
    })
  },
  methods: {
    // 获取详情数据
    getDetailsData (params) {
      infoApi(params).then((res) => {
        this.formAskData.type = res.data.type
        this.formAskData.title = res.data.title
        this.formAskData.link = res.data.link
        this.formAskData.link_title = res.data.linkTitle
        this.formAskData.link_description = res.data.linkDescription
        this.formAskData.link_cover = res.data.linkCover
        // 包装后台数据
        this.packAcceptData(res.data)
      })
    },
    // 包装后台数据
    packAcceptData (data) {
      //  链接封面
      this.$refs.overlinkImg.setUrl(this.formAskData.link_cover)
      // 追踪设置
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
    linkSetup () {
      if (this.formAskData.title == '') {
        this.$message.error('雷达标题不能为空')
        return false
      }
      const rep = /^(((ht|f)tps?):\/\/)?[\w-]+(\.[\w-]+)+([\w.,@?^=%&:/~+#-]*[\w@?^=%&/~+#-])?$/
      if (!rep.test(this.formAskData.link)) {
        this.$message.error('雷达链接不正确')
        return false
      }
      if (this.formAskData.link_title == '') {
        this.$message.error('链接标题不能为空')
        return false
      }
      if (this.formAskData.link_description == '') {
        this.$message.error('链接摘要不能为空')
        return false
      }
      if (this.formAskData.link_cover == '') {
        this.$message.error('链接封面不能为空')
        return false
      }
      // 处理链接追踪
      this.linkTrackData()
      // 验证客户标签
      if (this.formAskData.tag_status == 1) {
        if (this.formAskData.contact_tags == '') {
          this.$message.error('客户标签不能为空')
          return false
        }
      }
      this.formAskData.id = this.id
      updateApi(this.formAskData).then((res) => {
        this.$message.success('修改成功')
        this.$router.push({ path: '/radar/index' })
      })
    },
    // 处理追踪链接
    linkTrackData () {
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
    },
    // 接收子组件数据
    acceptArray (e) {
      this.formAskData.contact_tags = []
      this.showContactTags = e
      e.forEach((item) => {
        const tagsArray = {
          tagid: '',
          tagname: ''
        }
        tagsArray.tagid = item.id
        tagsArray.tagname = item.name
        this.formAskData.contact_tags.push(tagsArray)
      })
    },
    // 获取链接封面
    linkCover (e) {
      if (this.overImg <= 1) {
        this.overImg++
      } else {
        this.formAskData.link_cover = e
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
</style>
