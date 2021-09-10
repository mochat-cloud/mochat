<template>
  <div class="step1">
    <div class="block">
      <div class="title">基本信息</div>
      <div class="block">
        <div class="title">
          选择公众号
          <span>通过公众号授权将参与活动的微信客户和企业微信客户进行匹配，获取精准客户数据</span>
        </div>
        <!--    选择公众号-->
        <div class="item">
          <span class="label required">选择公众号：</span>
          <div class="input">
            <a-select
              style="width: 220px"
              placeholder="请选择公众号"
              option-label-prop="label"
              @change="selectPublic"
              :disabled="banModify"
              v-model="showData.officialId"
            >
              <a-select-option
                :value="item.id"
                :label="item.nickname"
                v-for="(item,index) in publiclist"
                :key="index">
                <div class="account-item">
                  <div class="avatar">
                    <img :src="item.avatar">
                  </div>
                  <div class="info">
                    <div class="name">{{ item.nickname }}</div>
                    <div class="type">服务号<a-tag color="green">已认证</a-tag></div>
                  </div>
                </div>
              </a-select-option>
            </a-select>
          </div>
          <div class="type" style="margin-left: 10px;font-size: 13px;" v-if="fission.official_account_id!=''">服务号<a-tag color="green" style="margin-left: 10px;">已认证</a-tag></div>
        </div>
      </div>
      <!--      活动名称-->
      <div class="item">
        <span class="label required">活动名称：</span>
        <div class="input">
          <a-input placeholder="活动名称" allowClear v-model="fission.active_name" :disabled="banModify"></a-input>
        </div>
      </div>
      <!--      活动结束时间-->
      <div class="item">
        <span class="label required">活动结束时间：</span>
        <div class="input">
          <a-date-picker placeholder="请选择结束时间" format="L LTS" v-model="seniorSet.end_time"/>
        </div>
      </div>
    </div>
    <div class="block">
      <div class="title">活动设置</div>
      <!--      活动目标人数-->
      <div class="item">
        <span class="label required">活动目标人数
          <a-popover>
            <template slot="content">
              群裂变活动需求邀请好友数，最大限制100人
            </template>
            <a-icon type="question-circle"/>
          </a-popover>
          ：
        </span>
        <div class="input">
          <a-input-number
            size="small"
            :min="1"
            v-model="fission.target_count"
            style="width: 70px"
            :disabled="banModify"></a-input-number>
        </div>
      </div>
      <div class="item user-count-setup">
        <span class="label">高级设置：</span>
        <div class="input">
          <div class="row">
            <a-switch size="small" v-model="seniorSet.new_friend" :disabled="banModify"/>
            <span>新好友才能助力
              <a-popover>
                <template slot="content">
                  开启后，若客户在活动前已进入任何此次裂变活动群聊，该客户将不被计入助力次数
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
            </span>
          </div>
          <div class="row">
            <a-switch size="small" v-model="seniorSet.delete_invalid" :disabled="banModify"/>
            <span>好友退出全部群聊后助力失效</span>
          </div>
        </div>
      </div>
      <div class="item user-count-setup">
        <span class="label required">
          领奖方式：
        </span>
        <div class="input">
          <div class="select mb10">
            <a-radio defaultChecked :disabled="banModify">联系客服</a-radio>
          </div>
          <div class="add-member mb10">
            <span>客服成员：</span>
            <a-button @click="selectMemberShow">选择成员</a-button>
          </div>
          <div class="memberRow">
            <div class="tags" v-for="(item,index) in showSelectStaff" :key="index">
              <a-icon type="user"/>
              {{ item.name }}
              <span class="closeIcon" @click="delIconSpan(index)"><a-icon type="close"/></span>
            </div>
          </div>
        </div>
      </div>
      <div class="item">
        <span class="label required">
          自动通过好友
          <a-popover>
            <template slot="content">
              开启后，客户添加客服企业微信时，无需好友验证，将会自动添加成功
            </template>
            <a-icon type="question-circle"/>
          </a-popover>
          ：
        </span>
        <div class="input">
          <a-switch size="small" v-model="seniorSet.auto_pass"/>
        </div>
      </div>
    </div>
    <selectMember ref="selectMember" @change="acceptMemberNews"/>
    <!--    授权提示-->
    <warrantTip ref="warrantTip" />
  </div>
</template>

<script>
import selectMember from '@/components/Select/member'
import warrantTip from '@/components/warrantTip/warrantTip'
// eslint-disable-next-line no-unused-vars
import { department, publicApi } from '@/api/roomFission'
// eslint-disable-next-line no-unused-vars
import moment from 'moment'

export default {
  components: { selectMember, warrantTip },
  data () {
    return {
      // 公众号id
      showData: {
      },
      // 公众号列表
      publiclist: [],
      // 禁止修改
      banModify: false,
      // 高级设置
      seniorSet: {
        // 新好友
        new_friend: false,
        // 退出失效
        delete_invalid: true,
        // 自动通过好友
        auto_pass: false,
        // 结束时间
        end_time: ''
      },
      // 显示选择的成员
      showSelectStaff: [],
      employeeNews: [],
      // 活动基本信息
      fission: {
        // 选择公众号
        official_account_id: '',
        // 活动名称
        active_name: '',
        // 结束时间
        // moment('2021-07-08 16:33:08')
        end_time: '',
        //  活动目标人数
        target_count: 1,
        // 客服成员
        receive_employees: []
      }
    }
  },
  created () {
    this.getEmployeesData()
    // 获取公众号列表
    this.getPublicList()
  },
  methods: {
    selectMemberShow () {
      this.$refs.selectMember.setSelect(this.showSelectStaff)
    },
    // 选择公众号
    selectPublic (e) {
      // console.log(e)
      this.fission.official_account_id = e
    },
    // 获取公众号列表
    getPublicList () {
      publicApi().then((res) => {
        this.publiclist = res.data
      })
    },
    getEmployeesData () {
      department().then((res) => {
        this.employeeNews = res.data.employee
      })
    },
    // 接收成员组件的数据
    acceptMemberNews (e) {
      this.showSelectStaff = e
      e.forEach((item, index) => {
        this.fission.receive_employees[index] = item.id
      })
    },
    // 接收父组件传值
    parentNews (data) {
      console.log(data)
      // abc
      this.banModify = true
      //  父组件传来的数据
      this.fission.official_account_id = data.officialAccountId
      this.showData.officialId = data.officialAccountId
      this.fission.active_name = data.activeName
      // 处理结束时间
      this.seniorSet.end_time = moment(data.endTime)
      // 新好友
      if (data.newFriend == 1) {
        this.seniorSet.new_friend = true
      } else {
        this.seniorSet.new_friend = false
      }
      // 退出失效
      if (data.deleteInvalid == 1) {
        this.seniorSet.delete_invalid = true
      } else {
        this.seniorSet.delete_invalid = false
      }
      // 自动通过好友
      if (data.autoPass == 1) {
        this.seniorSet.auto_pass = true
      } else {
        this.seniorSet.auto_pass = false
      }
      // 目标人数
      this.fission.target_count = data.targetCount
      //  客户成员请求数据
      this.fission.receive_employees = data.receiveEmployees
      //  客户成员展示数据
      this.employeeNews.forEach((item) => {
        const employeesIndex = data.receiveEmployees.indexOf(item.id)
        if (employeesIndex != -1) {
          this.showSelectStaff[employeesIndex] = item
        }
      })
    },
    // 删除成员
    delIconSpan (index) {
      this.showSelectStaff.splice(index, 1)
      this.fission.receive_employees.splice(index, 1)
    },
    //  抛出数据
    outputStep1 () {
      // console.log(this.fission.end_time.format('YYYY-MM-DD HH:mm:ss'))
      if (this.fission.official_account_id == '') {
        this.$message.error('请选择公众号')
        return false
      }
      if (this.fission.active_name == '') {
        this.$message.error('活动名称不能为空')
        return false
      }
      if (this.seniorSet.end_time == '') {
        this.$message.error('结束时间不能为空')
        return false
      }
      // 处理结束时间
      this.fission.end_time = this.seniorSet.end_time.format('YYYY-MM-DD HH:mm:ss')
      if (this.fission.receive_employees == '') {
        this.$message.error('请选择客服成员')
        return false
      }
      if (this.seniorSet.new_friend) {
        this.fission.new_friend = 1
      } else {
        this.fission.new_friend = 0
      }
      if (this.seniorSet.delete_invalid) {
        this.fission.delete_invalid = 1
      } else {
        this.fission.delete_invalid = 0
      }
      if (this.seniorSet.auto_pass) {
        this.fission.auto_pass = 1
      } else {
        this.fission.auto_pass = 0
      }
      return this.fission
    }

  }
}
</script>

<style lang="less" scoped>
.step1 {
  margin: 10px;
}

.memberRow {
  margin-left: 70px;
  margin-top: 17px;
  display: flex;
}

.memberRow .tags {
  margin-left: 17px;
  background: #f7f7f7;
  border-radius: 2px;
  padding: 5px 10px;
  font-size: 14px;
  line-height: 22px;
  color: rgba(0, 0, 0, 0.85);
  position: relative;
}

.tags .closeIcon {
  width: 15px;
  height: 15px;
  line-height: 12px;
  text-align: center;
  position: absolute;
  background: #a9a9a9;
  border-radius: 50%;
  cursor: pointer;
  font-size: 9px;
  right: -6px;
  top: -5px;
  color: #fff;
}

.memberRow .tags:first-child {
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

.add-account {
  border-bottom: 1px dashed #e8e8e8;
  padding-left: 8px;
  padding-top: 4px;
  padding-bottom: 4px;
}

.add-account-select {
  width: 187px;
}

.account-item {
  display: flex;
  align-items: center;
  padding: 0 13px;
  margin-bottom: 5px;

  .avatar {
    margin-right: 10px;

    img {
      width: 30px;
      height: 30px;
      border-radius: 2px;
    }
  }

  .info {
    .name {
      font-size: 13px;
    }
    .type {
      font-size: 12px;
      span{
        margin-left: 10px;
      }
    }
  }
}

.user-count-setup {
  align-items: end !important;
  margin-left: 40px;

  .row {
    display: flex;
    align-items: center;
    margin-bottom: 10px;

    span {
      margin-left: 5px;
    }
  }
}

.mb10 {
  margin-bottom: 10px;
}

/deep/ .ant-dropdown-menu-item {
  padding-bottom: 5px;
  padding-left: 8px;
}
</style>
