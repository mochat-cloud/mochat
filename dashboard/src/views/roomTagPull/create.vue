<template>
  <div class="new-pull">
    <div class="content">
      <div class="right-wrapper">
        <div class="right">
          <div class="rightTop">
            <a-alert message="每位客户每周最多收到来自客户群发管理员的1条群发消息，如选择的客户本周已收到过群发消息本条邀请将不会成功发送" banner class="rightTips"/>
          </div>
          <a-form class="form-two" :label-col="{ span: 2 }" :wrapper-col="{ span: 15 }">
            <a-row class="titleName">基础信息</a-row>
            <a-divider/>
            <a-form-item label="任务名称" class="form-item">
              <a-col :span="8">
                <a-input v-model="form.name" :maxLength="30" placeholder="请输入任务名称"></a-input>
              </a-col>
            </a-form-item>
            <a-form-item label="选择群发账号" class="form-item">
              <a-button @click="$refs.selectMember.setSelect(employees)">
                选择成员
              </a-button>
              <div class="tags">
                <a-tag v-for="(v,i) in employees" :key="i">
                  {{ v.name }}
                </a-tag>
              </div>
            </a-form-item>

            <a-form-item label="筛选客户" class="form-item">
              <a-radio-group v-model="form.choose_contact.is_all">
                <a-radio :value="1">
                  筛选客户
                </a-radio>
                <a-radio :value="0">
                  全部客户
                </a-radio>
              </a-radio-group>
              <div class="filterPanel">
                <a-card title="筛选客户" :bordered="true" v-if="form.choose_contact.is_all == 1">
                  <a-row class="filterRow">
                    <a-col :span="2" class="lableName">性别：</a-col>
                    <a-col :span="15">
                      <a-radio-group v-model="form.choose_contact.gender">
                        <a-radio :value="''">全部性别</a-radio>
                        <a-radio :value="1">仅男性粉丝</a-radio>
                        <a-radio :value="2">仅女性粉丝</a-radio>
                        <a-radio :value="0">未知性别</a-radio>
                      </a-radio-group>
                    </a-col>
                  </a-row>
                  <a-row class="filterRow">
                    <a-col :span="2" class="lableName">添加时间：</a-col>
                    <a-col :span="15">
                      <a-range-picker v-model="form.time"/>
                    </a-col>
                  </a-row>
                  <a-row class="filterRow">
                    <a-col :span="2" class="lableName">标签：</a-col>
                    <a-col :span="15">
                      <a-row class="filterRow flex">
                        <a-button @click="$refs.labelGroup.show()">选择标签</a-button>
                        <div class="ml16">
                          <a-tag v-for="(tag,i) in tags" :key="i">
                            {{ tag.name }}
                          </a-tag>
                        </div>
                      </a-row>
                    </a-col>
                  </a-row>
                  <div v-if="fansConditionArray.length!=0">
                    <a-divider dashed/>
                    <a-row class="filterRow">
                      将发送消息给
                      <span v-for="(item,index) in fansConditionArray" :key="index" v-if="index<=2">[{{ item }}]</span>
                      符合{{ fansConditionArray.length }}标签
                      <span v-if="fansConditionArray.length>3">等</span>
                      条件的粉丝
                    </a-row>
                  </div>
                </a-card>
                <a-row v-if="isVerified==1">
                  <div class="estimateNum">
                    <!--                    abc-->
                    <span><a-icon type="file-search"/>查看该消息预计送达人数：</span>
                    <span class="estimateBtn" @click="estimateSee" v-if="estimateBtnshow">查看</span>
                    <a-spin v-if="!estimateBtnshow&&!estimateShowNum"/>
                    <span class="estimateBtn" v-if="estimateShowNum">
                      {{ chooseCustomerNum }}
                    </span>
                  </div>
                  <span class="estimateRightTips" v-if="!estimateBtnshow&&!estimateShowNum">人数计算中，请稍后（可能需要数分钟）</span>
                </a-row>
              </div>
            </a-form-item>
            <a-row class="titleName">设置群信息</a-row>
            <a-divider/>
            <a-form-item label="入群引导语" class="form-item">
              <div class="leading">
                <a-row>
                  <a-textarea
                    id="textarea"
                    v-model="form.guide"
                    :maxLength="1000"
                    :rows="4"
                    placeholder="请输入回复内容"/>
                </a-row>
                <a-row class="tipsContent">
                  <a-col :span="1" class="leftTips">提示：</a-col>
                  <a-col :span="23" class="rightTips">
                    <a-alert
                      :show-icon="false"
                      message="1、群人数上限为200人，超过200人时请 【添加多个群聊】，否则可能会导致部分用户 【无法入群】，添加多个群聊时，客户只会收到一条群发邀请"
                      banner/>
                    <a-alert :show-icon="false" message="2、上传群二维码时请【仔细核对】，群二维码和群聊【对应错误】将导致【数据异常】" banner/>
                    <a-alert :show-icon="false" message="3、如果客户添加了多个客服，只会收到一位客服的入群邀请" banner/>
                  </a-col>
                </a-row>
              </div>
            </a-form-item>
            <a-form-item label="添加群聊" class="form-item">
              <a-row>
                <a-button @click="$refs.selectGroup.setSelect(rooms)">
                  选择群聊
                </a-button>
                <div class="groups mt16">
                  <div class="item flex" v-for="(v,i) in rooms" :key="i">
                    <div class="group-card">
                      <img
                        src="../../assets/avatar-room-default.svg">
                      <div class="info">
                        <div class="name">
                          {{ v.name }}
                        </div>
                        <div class="count">{{ v.contact_num }} / {{ v.roomMax }}人</div>
                      </div>
                      <div class="icon">
                        <a-icon
                          type="delete"
                          @click="_=>rooms.splice(i,1)"
                        />
                      </div>
                    </div>
                    <div class="mt16 ml16">
                      <m-upload v-model="v.image"/>
                    </div>
                  </div>
                </div>
              </a-row>
            </a-form-item>
            <a-divider/>
            <a-form-item label="过滤客户" class="form-item">
              <a-switch v-model="form.filter_contact"/>
              <span class="switchPartTips">开启后已在群聊中的客户将不会收到邀请</span>
              <a-row>
                <div class="estimateNum">
                  <!--  abc -->
                  <span><a-icon type="file-search"/>查看过滤后预计送达人数：</span>
                  <span class="estimateBtn" @click="filterEstimateSee" v-if="filterEstimateBtnshow">查看</span>
                  <a-spin v-if="!filterEstimateBtnshow&&!filterEstimateShowNum"/>
                  <span class="estimateBtn" v-if="filterEstimateShowNum">{{ filterCustomerNum }}</span>
                </div>
                <span
                  class="estimateRightTips"
                  v-if="!filterEstimateBtnshow&&!filterEstimateShowNum">人数计算中，请稍后（可能需要数分钟）</span>
              </a-row>
              <div class="btn-wrapper">
                <a-button type="primary" @click="submit" class="submitBtn">通知成员发送邀请</a-button>
                <span class="footBtnTips">发送后需要员工确认发送后才会将群二维码发送给对应的客户</span>
              </div>
            </a-form-item>
          </a-form>
        </div>

        <selectGroup ref="selectGroup" @change="e=> rooms = e"/>
        <selectMember ref="selectMember" @change="e=> employees = e"/>
        <labelGroup ref="labelGroup" @choiceTagsArr="e=> tags = e"/>
      </div>
    </div>
  </div>
</template>

<script>
import Department from '@/components/department'
import { mapGetters } from 'vuex'
import selectGroup from '@/components/Select/group'
import selectMember from '@/components/Select/member'
import labelGroup from '@/components/addlabel/index'
// eslint-disable-next-line no-unused-vars
import { chooseContactRoomTag, chooseFilterContact, addGroup } from '@/api/workRoom'
import moment from 'moment'

export default {
  components: {
    Department,
    selectGroup,
    selectMember,
    labelGroup
  },
  watch: {
    employees (value) {
      this.filterEstimateBtnshow = true
      this.filterEstimateShowNum = false
      this.estimateBtnshow = true
      this.estimateShowNum = false
    },
    rooms (value) {
      this.filterEstimateBtnshow = true
      this.filterEstimateShowNum = false
    },
    tags (value) {
      this.filterEstimateBtnshow = true
      this.filterEstimateShowNum = false
      this.estimateBtnshow = true
      this.estimateShowNum = false
    },
    'form.choose_contact.is_all': {
      handler () {
        this.filterEstimateBtnshow = true
        this.filterEstimateShowNum = false
        this.estimateBtnshow = true
        this.estimateShowNum = false
      },
      deep: true
    },
    'form.choose_contact.gender': {
      handler () {
        this.filterEstimateBtnshow = true
        this.filterEstimateShowNum = false
        this.estimateBtnshow = true
        this.estimateShowNum = false
      },
      deep: true
    },
    'form.time': {
      handler () {
        this.filterEstimateBtnshow = true
        this.filterEstimateShowNum = false
        this.estimateBtnshow = true
        this.estimateShowNum = false
      },
      deep: true
    }
  },
  data () {
    return {
      // 已选成员
      employees: [],
      // 已选标签
      tags: [],
      // 已选群聊
      rooms: [],
      // 成员人数
      employeeNum: 0,
      // 扫码名称
      qrcodeName: '',
      // 添加验证
      isVerified: 1,
      // 客户性别
      customerGender: 1,
      // 筛选客户数量
      chooseCustomerNum: -1,
      // 过滤客户数量
      filterCustomerNum: -1,
      // 符合条件的粉丝
      fansCondition: '',
      //  符合条件的粉丝数组
      fansConditionArray: [],
      //  控制查看按钮显示
      estimateBtnshow: true,
      //    控制人数显示
      estimateShowNum: false,
      // 是否选中开关
      selectSwitch: false,
      //  控制过滤后查看按钮显示
      filterEstimateBtnshow: true,
      //    控制过滤后人数显示
      filterEstimateShowNum: false,
      //  入群引导语
      leadingWords: '',

      form: {
        name: '',
        employees: '',
        choose_contact: {
          is_all: 1,
          gender: '',
          tag_ids: '',
          start_time: '',
          end_time: ''
        },
        guide: '',
        filter_contact: true,
        time: []
      }
    }
  },
  computed: {
    ...mapGetters(['corpId'])
  },
  methods: {
    getParams () {
      return {
        name: this.form.name,
        employees: this.form.employees,
        choose_contact: this.form.choose_contact,
        guide: this.form.guide,
        filter_contact: this.form.filter_contact
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
    // 过滤客户查看人数
    filterEstimateSee () {
      if (!this.employees.length) {
        this.$message.error('未选择成员')
        return false
      }
      if (!this.rooms.length) {
        this.$message.error('未选择群聊')
        return false
      }
      if (this.form.time == '') {
        this.$message.error('请设置添加时间')
        return false
      }
      this.filterEstimateBtnshow = false
      const params = {
        choose_contact: {
          is_all: this.form.choose_contact.is_all,
          gender: this.form.choose_contact.gender,
          start_time: moment(this.form.time[0]).format('YYYY-MM-DD'),
          end_time: moment(this.form.time[1]).format('YYYY-MM-DD')
        }
      }
      params.rooms = this.rooms.map(v => {
        return { id: v.id }
      })
      params.employees = this.employees.map(v => {
        return v.id
      })
      params.choose_contact.tag_ids = this.tags.map(v => {
        return v.id
      })
      chooseFilterContact(params).then(res => {
        this.filterCustomerNum = res.data[0]
        this.filterEstimateShowNum = true
      })
    },

    // 提交按钮
    submit () {
      if (!this.form.name) {
        this.$message.error('名称未填写')
        return false
      }
      if (!this.employees.length) {
        this.$message.error('未选择成员')
        return false
      }
      if (!this.rooms.length) {
        this.$message.error('未选择群聊')
        return false
      }
      if (!this.form.guide) {
        this.$message.error('未输入引导语')
        return false
      }
      for (const room of this.rooms) {
        if (!room.image) {
          this.$message.error(`${room.name}未上传群二维码`)
          return false
        }
      }
      const params = {
        name: this.form.name,
        guide: this.form.guide,
        rooms: this.rooms,
        filter_contact: +this.form.filter_contact,
        choose_contact: {
          is_all: this.form.choose_contact.is_all,
          gender: this.form.choose_contact.gender,
          start_time: moment(this.form.time[0]).format('YYYY-MM-DD'),
          end_time: moment(this.form.time[1]).format('YYYY-MM-DD')
        }
      }

      params.choose_contact.tag_ids = this.tags.map(v => {
        return v.id
      })

      params.employees = this.employees.map(v => {
        return v.id
      })

      addGroup(params).then(res => {
        this.$message.success('添加成功')

        this.$router.push('/roomTagPull/index')
      })
    },

    // 筛选客户查看人数
    estimateSee () {
      if (!this.employees.length) {
        this.$message.error('未选择成员')
        return false
      }
      this.estimateBtnshow = false
      const params = {
        is_all: this.form.choose_contact.is_all,
        gender: this.form.choose_contact.gender
      }
      if (this.form.time != '') {
        params.start_time = moment(this.form.time[0]).format('YYYY-MM-DD')
        params.end_time = moment(this.form.time[1]).format('YYYY-MM-DD')
      }
      params.employees = this.employees.map(v => {
        return v.id
      })
      params.tag_ids = this.tags.map(v => {
        return v.id
      })
      chooseContactRoomTag(params).then(res => {
        this.chooseCustomerNum = res.data[0]
        this.estimateShowNum = true
      })
    },
    // 点击标签
    clickLable (event) {
      if (event.target.className.indexOf('lableSelect') == -1) {
        event.target.className = event.target.className + ' lableSelect'
      } else {
        event.target.className = event.target.className.replace('lableSelect', '')
      }
      var lableContent = event.currentTarget.innerHTML
      var lableIndex = this.fansConditionArray.indexOf(lableContent)
      if (lableIndex == -1) {
        this.fansConditionArray.unshift(lableContent)
      } else {
        this.fansConditionArray.splice(lableIndex, 1)
      }
    }
  }
}
</script>

<style lang="less">
.tipsContent {
  background: #FFFBE6;
}

.leftTips {
  width: 4%;
  text-align: right;
  color: #bb5223;
  min-width: 46px;
}

.rightTips {
  width: 90%;
}

.switchPartTips {
  margin-left: 5px;
  //font-size: 13px;
}

.titleName {
  font-size: 15px;
  font-weight: bold;
}

.filterPanel .ant-card-head-title {
  font-size: 14px;
  padding: 12px 0;
}

.filterPanel .ant-card-head {
  min-height: 40px;
}

.filterPanel .lableName {
  width: 100px;
  text-align: right;
}

.filterRow {
  margin-top: 18px;
}

.filterRow:first-child {
  margin-top: 0;
}

.lableOption {
  display: inline-block;
  border: 1px solid #D9D9D9;
  padding: 3px 0;
  border-radius: 5px;
  margin-left: 15px;
  cursor: pointer;
  min-width: 49px;
  text-align: center;
}

.lableTop {
  margin-top: 10px;
}

.lableSelect {
  border: 1px solid #1890FF;
  background: #E7F7FF;
  color: #1890FF;
}

.lableOption:hover {
  border: 1px solid #108EE9;
  color: #108EE9;
}

.wholeCustomer {
  background: #ECF8FE;
  padding: 0 0 0 10px;
}

.estimateNum {
  width: 225px;
  padding: 0 7px;
  display: inline-block;
  background: #ECF8FE;
  margin-top: 20px;
}

.estimateRightTips {
  font-size: 13px;
  margin-left: 10px;
}

.estimateBtn {
  color: #1890FF;
  cursor: pointer;
}

.new-pull {
  .service-wrapper {
    border: none;
  }

  .content {
    display: flex;

    .left-wrapper {
      flex: 0 0 350px;
      background: #fff;
    }

    .right-wrapper {
      flex: 1
    }
  }

  .right {
    height: auto;
    background: #fff;
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

    .form-two {
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
  }

  .btn-wrapper {
    display: flex;
    margin-top: 20px;
  }

  .submitBtn {
    height: 37px;
  }

  .footBtnTips {
    margin-left: 10px;
    color: #B5B5B5;
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

.rightTop {
  width: 100%;
  padding: 15px;
}

.rightTips .ant-alert-message {
  color: #bb5223;
}

.groups {
  .item {
    margin-bottom: 23px;
  }

  .group-card {
    max-width: 240px;
    min-width: 130px;
    -webkit-box-flex: 1;
    flex: 1;
    height: 60px;
    background: #fff;
    border: 1px solid #e6e6e6;
    box-sizing: border-box;
    border-radius: 1px;
    display: flex;
    align-items: center;
    padding-left: 13px;
    padding-right: 5px;
    margin-right: 8px;

    img {
      width: 36px;
      height: 36px;
      margin-right: 9px;
    }

    .info {
      flex: 1;
    }

    .icon {
      margin-right: 8px;
      cursor: pointer;
    }

    .name {
      color: #222;
      height: 27px;
      overflow: hidden;
    }

    .count {
      color: #999;
      font-size: 13px;
      height: 36px;
      display: flex;
      align-items: center;
    }
  }
}
</style>
