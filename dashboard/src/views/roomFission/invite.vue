<template>
  <div>
    <a-card>
      <div class="step4">
        <div class="block qr-code">
          <div class="title">
            企微群聊二维码设置
            <span>企业邀请可参与活动的客户</span>
          </div>
          <div class="content">
            <div class="mb16">
              <a-radio-group :options="plainOptions" default-value="选择参与员工" v-model="invite.type"/>
            </div>
            <div class="option" v-if="invite.type == 1">
              <div class="item">
                <span class="label required">所属员工：</span>
                <div class="account" @click="$refs.selectMember.setSelect(showSelectMem)">
                  <div class="info">选择员工</div>
                  <div class="icon">
                    <a-icon type="caret-down" :style="{ fontSize: '10px', color: '#cacaca' }"/>
                  </div>
                </div>
              </div>
              <div class="memberRow" v-if="showSelectMem.length!=0">
                <div class="tags" v-for="(item,index) in showSelectMem" :key="index">
                  <a-icon type="user" />
                  {{ item.name }}
                  <a-icon type="close-circle" @click="delIconSpan(index)" class="closeIcon"/>
                </div>
              </div>
              <div class="item">
                <span class="label required">选择客户：</span>
                <a-radio-group :options="serviceOptions" v-model="invite.choose_contact.is_all"/>
              </div>
              <div class="item">
                <!--            <div class="select-all" v-if="invite.choose_contact.is_all == 0">将群发消息给全部账号的 8 个客户</div>-->
                <div class="filter" v-if="invite.choose_contact.is_all == 1">
                  <div class="client-info">
                    <span class="client-title">性别：</span>
                    <a-radio-group :options="genderOptions" v-model="invite.choose_contact.gender"/>
                  </div>
                  <div class="client-info">
                    <span class="client-title">添加时间：</span>
                    <a-range-picker @change="addActivityTime" v-model="showSetTime"/>
                  </div>
                  <div class="client-info">
                    <span class="client-title">客户等级：</span>
                    <div class="tags">
                      <div
                        class="tag"
                        v-for="(item,index) in rankTags"
                        :key="index"
                        @click="selectRankTag(item)"
                        :class="[invite.choose_contact.tag_ids.indexOf(item.id)!=-1?'select_tags':'']"
                      >
                        {{ item.name }}
                      </div>
                    </div>
                  </div>
                  <div class="tagTips" v-if="invite.choose_contact.tag_ids.length!=0">将发送消息给 属于
                    <span v-for="(item,index) in showTags" :key="index">「{{ item }}」</span>
                    标签 的符合条件的粉丝</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="block" v-show="invite.type == 1">
          <div class="title">
            邀请信息
          </div>
          <div class="welcome-box">
            <div class="content welcome-text">
              <div class="item">
                <span class="label required">邀请文案：</span>
                <div class="content text-1">
                  <div class="input">
                    <div class="textarea">
                      <textarea v-model="textField"></textarea>
                    </div>
                  </div>
                </div>
              </div>
              <div class="item">
                <span class="label required">邀请链接：</span>
                <div class="content text-2">
                  <div class="link-form">
                    <div class="item">
                      <span class="label required">链接标题：</span>
                      <div class="input">
                        <a-input v-model="link_title"/>
                      </div>
                    </div>
                    <div class="item">
                      <span class="label required">链接摘要：</span>
                      <div class="input">
                        <a-input v-model="link_desc"/>
                      </div>
                    </div>
                    <div class="item">
                      <span>链接封面：</span>
                      <div class="input">
                        <m-upload @change="linkCover" ref="coverImg"/>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="preview">
              <div class="tips">
                企业微信邀请客户参与预览
              </div>
              <m-preview ref="preview"/>
            </div>
          </div>
        </div>
        <selectMember ref="selectMember" @change="acceptMemberNews"/>
      </div>
      <a-button type="primary" @click="sendOutNotice" v-show="invite.type == 1">通知成员发送</a-button>
    </a-card>
  </div>
</template>
<script>
// eslint-disable-next-line no-unused-vars
import moment from 'moment'
// eslint-disable-next-line no-unused-vars
import { contactTagListApi, department, infoApi, inviteApi } from '@/api/roomFission'
import selectMember from '@/components/Select/member'

export default {
  components: { selectMember },
  mounted () {
    this.$refs.preview.setText(this.textField)
    this.$refs.preview.setLink(this.link_title, this.link_desc, '')
  },
  watch: {
    textField: function () {
      this.$refs.preview.setText(this.textField)
    },
    link_title: function () {
      if (this.link_pic == '') {
        this.$refs.preview.setLink(this.link_title, this.link_desc, '')
      } else {
        this.$refs.preview.setLink(this.link_title, this.link_desc, this.link_pic)
      }
    },
    link_desc: function () {
      if (this.link_pic == '') {
        this.$refs.preview.setLink(this.link_title, this.link_desc, '')
      } else {
        this.$refs.preview.setLink(this.link_title, this.link_desc, this.link_pic)
      }
    },
    link_pic: function () {
      this.$refs.preview.setLink(this.link_title, this.link_desc, this.link_pic)
    }
  },
  data () {
    return {
      imglink: 0,
      // 文本域
      textField: '',
      // 连接标题
      link_title: '',
      // 链接摘要
      link_desc: '',
      // 链接封面
      link_pic: '',
      plainOptions: [
        { label: '选择参与员工', value: 1 },
        { label: '暂不邀请', value: 2 }
      ],
      serviceOptions: [
        { label: '全部客户', value: 0 },
        { label: '筛选客户', value: 1 }
      ],
      genderOptions: [
        { label: '全部性别', value: 3 },
        { label: '仅男性粉丝', value: 1 },
        { label: '仅女性粉丝', value: 2 },
        { label: '未知性别', value: 0 }
      ],
      // 客户等级标签
      rankTags: [],
      showTags: [],
      //  请求数据  invite.choose_contact.gender
      invite: {
        // 类型
        type: 1,
        // 所属员工
        employees: [],
        // 筛选条件
        choose_contact: {
          is_all: 0,
          gender: 3,
          tag_ids: [],
          start_time: '',
          end_time: ''
        },
        // 邀请文案
        text: '',
        // 连接标题
        link_title: '',
        // 链接摘要
        link_desc: '',
        // 链接封面
        link_pic: ''
      },
      //  成员数据
      showSelectMem: [],
      employeeNews: [],
      //  显示添加时间
      showSetTime: []
    }
  },
  created () {
    this.id = this.$route.query.id
    this.getClientTags()
    this.getEmployeesData()
  },
  methods: {
    getEmployeesData () {
      department().then((res) => {
        this.employeeNews = res.data.employee
      })
    },
    // 获取详情数据
    getDetailsData (id) {
      infoApi({ id }).then((res) => {
        // console.log(res)
        this.invite.fission_id = res.data.invite.id
        // 类型
        this.invite.type = res.data.invite.type
        // 所属员工
        this.invite.employees = res.data.invite.employees
        //  筛选条件
        this.invite.choose_contact = res.data.invite.chooseContact
        // 展示邀请文案
        this.textField = res.data.invite.text
        this.link_title = res.data.invite.linkTitle
        this.link_desc = res.data.invite.linkDesc
        // this.text = res.data.text
        this.$refs.coverImg.setUrl(res.data.invite.linkPic)
        this.handleShowNews()
      })
    },
    // 处理显示后台数据
    handleShowNews () {
      this.employeeNews.forEach((item) => {
        const employeesIndex = this.invite.employees.indexOf(item.id)
        if (employeesIndex != -1) {
          this.showSelectMem[employeesIndex] = item
        }
      })
      //  处理开始时间和结束时间
      if (this.invite.choose_contact.start_time !== '') {
        this.showSetTime[0] = moment(this.invite.choose_contact.start_time)
      }

      if (this.invite.choose_contact.end_time !== '') {
        this.showSetTime[1] = moment(this.invite.choose_contact.end_time)
      }

      // 处理客户等级
      this.rankTags.forEach((item, index) => {
        const tagIndex = this.invite.choose_contact.tag_ids.indexOf(item.id)
        if (tagIndex != -1) {
          this.showTags[tagIndex] = item.name
        }
      })
    },
    // 获取客户等级标签
    getClientTags () {
      contactTagListApi().then((res) => {
        res.data.forEach((item) => {
          if (item.groupName == '客户等级') {
            this.rankTags = item.tags
          }
        })
        this.getDetailsData(this.id)
      })
    },
    // 设置活动时间
    addActivityTime (date, dateString) {
      this.invite.choose_contact.start_time = dateString[0]
      this.invite.choose_contact.end_time = dateString[1]
    },
    // 通知成员发送
    sendOutNotice () {
      if (this.invite.type == 1) {
        //  验证所属员工
        if (this.invite.employees.length == 0) {
          this.$message.error('请选择所属员工')
          return false
        }
        // 选择客服
        if (this.invite.choose_contact.is_all == 1) {
          if (this.invite.choose_contact.start_time == '') {
            this.$message.error('开始时间不能为空')
            return false
          }
          if (this.invite.choose_contact.tag_ids.length == 0) {
            this.$message.error('请选择客户等级标签')
            return false
          }
        }
        //  邀请信息
        if (this.textField == '') {
          this.$message.error('邀请文案不能为空')
          return false
        }
        this.invite.text = this.textField
        if (this.link_title == '') {
          this.$message.error('链接标题不能为空')
          return false
        }
        this.invite.link_title = this.link_title
        if (this.link_desc == '') {
          this.$message.error('链接摘要不能为空')
          return false
        }
        this.invite.link_desc = this.link_desc
        if (this.link_pic == '') {
          this.$message.error('链接封面不能为空')
          return false
        }
        this.invite.link_pic = this.link_pic
      }
      this.invite.id = this.id
      console.log(this.invite)
      inviteApi(this.invite).then((res) => {
        this.$message.success('通知成功')
        this.$router.push({ path: '/roomFission/index' })
      })
    },
    // 获取封面
    linkCover (e) {
      if (this.imglink <= 1) {
        this.imglink++
        this.link_pic = e
      } else {
        this.link_pic = e
      }
    },
    // 选中客户等级标签
    selectRankTag (item) {
      const indexTag = this.invite.choose_contact.tag_ids.indexOf(item.id)
      if (indexTag == -1) {
        this.invite.choose_contact.tag_ids.push(item.id)
        this.showTags.push(item.name)
        return true
      } else {
        this.invite.choose_contact.tag_ids.splice(indexTag, 1)
        this.showTags.splice(indexTag, 1)
        return false
      }
    },
    // 删除
    delIconSpan (index) {
      this.showSelectMem.splice(index, 1)
      this.invite.employees.splice(index, 1)
    },
    //  获取成员信息
    acceptMemberNews (e) {
      this.showSelectMem = e
      e.forEach((item, index) => {
        this.invite.employees[index] = item.id
      })
    }
  }
}
</script>

<style lang="less" scoped>
.tagTips{
  padding-left: 20px;
  margin-top: -12px;
  padding-top: 15px;
  padding-bottom: 12px;
  border-top: 1px dashed #e8e8e8;
}
.memberRow{
  margin-left: 70px;
  margin-top: 17px;
  display: flex;
  margin-bottom: 15px;
  .tags{
    margin-left: 17px;
    background: #f7f7f7;
    border-radius: 2px;
    padding: 5px 10px;
    font-size: 14px;
    line-height: 22px;
    color: rgba(0, 0, 0, 0.85);
    position: relative;
    .closeIcon{
      position: absolute;
      cursor: pointer;
      right: -8px;
      top: -8px;
      font-size: 16px;
      color: #B5B5B5;
    }
  }
}
.memberRow .tags:first-child{
  margin-left: 0;
}
.block {
  margin-bottom: 60px;

  .title {
    font-size: 15px;
    line-height: 21px;
    color: rgba(0, 0, 0, .85);
    border-bottom: 1px solid #e9ebf3;
    padding-bottom: 16px;
    margin-bottom: 16px;
    position: relative;

    span {
      font-size: 13px;
      margin-left: 11px;
      color: rgba(0, 0, 0, .45);
      font-weight: 400;
    }
  }

  .required:after {
    content: "*";
    display: inline-block;
    margin-right: 4px;
    color: #f5222d;
    font-size: 14px;
    line-height: 1;
    position: absolute;
    left: -10px;
    top: 6px;
  }

  .item {
    display: flex;
    align-items: center;
    margin-bottom: 16px;

    .label {
      color: rgba(0, 0, 0, .85);
      position: relative;
    }
  }
}

.welcome-box {
  display: flex;
  align-items: center;

  .preview {
    margin-left: 30px;

    .tips {
      text-align: center;
      margin-bottom: 16px;
    }
  }
}

.welcome-text {
  width: 760px;
  margin-left: 50px;

  .label {
    width: 80px;
  }
}

.account {
  height: 38px;
  border-radius: 4px;
  border: 1px solid #e5e5e5;
  padding-left: 17px;
  padding-right: 11px;
  font-size: 14px;
  color: rgba(0, 0, 0, .85);
  cursor: pointer;
  display: flex;
  align-items: center;
  transition: .3s all;

  .info {
    display: flex;
    align-items: center;
    flex: 1;

    img {
      width: 23px;
      height: 23px;
      margin-right: 4px;
      font-size: 14px;
      border-radius: 2px;
    }
  }

  .icon {
    margin-left: 10px;
  }

  &:hover {
    border: 1px solid #1890ff;
  }
}

.select-all {
  display: inline-block;
  font-size: 13px;
  height: 40px;
  color: rgba(0, 0, 0, .65);
  line-height: 20px;
  padding: 10px 8px 8px;
  background: #effaff;
  margin-left: 70px;
}

.option {
  margin-left: 40px;
}

.filter {
  width: 850px;
  background: #fbfbfb;
  border-radius: 2px;
  border: 1px solid #ebebeb;
  padding-top: 13px;

  .client-info {
    display: flex;
    margin-bottom: 25px;

    .client-title {
      width: 90px;
      text-align: right;
      margin-right: 15px;
    }
  }

  .tags {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    cursor: pointer;
    .tag {
      height: 27px;
      line-height: 25px;
      padding: 0 14px;
      background: #fff;
      border: 1px solid #d9d9d9;
      color: rgba(0,0,0,.65);
      border-radius: 4px;
      margin-right: 10px;
      margin-bottom: 10px;
    }
    .select_tags{
      background: #e7f7ff;
      border: 1px solid #1890ff;
      color: #1890ff;
    }
  }
}

.text-1 {
  width: 100%;
  border: 1px solid #eee;
  background: #fbfbfb;
  border-radius: 2px;

  .insert-btn-group {
    width: 100%;
    flex: 1;
    border-bottom: 1px dashed #e9e9e9;
    padding: 6px 15px;
    color: #e8971d;
    cursor: pointer;
  }

  .textarea {
    overflow-y: auto;
    overflow-x: hidden;
    white-space: pre-wrap;
    word-break: break-all;

    textarea {
      width: 100%;
      height: 190px;
      padding: 6px 13px;
      border: none;
      background: #fbfbfb;
      outline: none;
      resize: none;
    }

    .word-count {
      font-size: 13px;
      color: rgba(0, 0, 0, .25);
      margin-left: 10px;
    }
  }
}

.text-2 {
  width: 100%;
  border: 1px solid #eee;
  background: #fbfbfb;
  border-radius: 2px;
  padding-left: 20px;
}

.link-form {
  margin-top: 16px;

  .item {
    display: flex;
    align-items: center;
    margin-bottom: 14px;

    .ant-input {
      width: 348px;
    }
  }
}

.instructions-img {
  width: 758px;
  margin-bottom: 40px;
}

.mb16 {
  margin-bottom: 16px;
}

/deep/ .ant-alert-description {
  font-size: 13px;
}

/deep/ .ant-alert-with-description {
  padding: 9px 31px 3px 64px !important;
}
</style>
