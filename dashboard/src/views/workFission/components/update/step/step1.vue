<template>
  <div class="step1">
    <div class="block">
      <div class="title">
        基本信息
      </div>
      <div class="item">
        <span class="label required">活动名称：</span>
        <div class="input">
          <a-input v-model="form.name" disabled=""></a-input>
        </div>
      </div>
      <div class="item">
        <span class="label required">客服成员：</span>
        <div class="input">
          <div class="add-member mb10 mr16">
            <a-button @click="memberSelectShow">选择成员</a-button>
          </div>
          <div class="members">
            <div class="item" v-if="form.services.length">
              <a-tag v-for="v in form.services" :key="v.id">{{ v.name }}</a-tag>
            </div>
          </div>
        </div>
      </div>
      <div class="item">
        <span class="label">
          自动通过好友
          <a-tooltip>
            <template slot="title">
              开启后，客户添加该企业微信时，无需好友验证，将会自动添加成功
            </template>
            <a-icon type="question-circle"/>
          </a-tooltip>
          ：</span>
        <div class="input">
          <a-switch v-model="form.autoPass" size="small" default-checked/>
        </div>
      </div>
      <div class="item">
        <span class="label">
          客户标签
          <a-tooltip>
            <template slot="title">
              给通过本次活动添加的客户自动打标签
            </template>
            <a-icon type="question-circle"/>
          </a-tooltip>
          ：</span>
        <div class="input">
          <a-switch v-model="form.autoAddTag" size="small"/>
        </div>
      </div>
      <div class="item">
        <span class="label required">活动结束时间：</span>
        <div class="input">
          <a-date-picker v-model="form.endTime" placeholder="请选择结束时间" format="YYYY-MM-DD hh:ss"/>
        </div>
      </div>
      <div class="item">
        <span class="label required">二维码有效期：</span>
        <div class="input">
          <a-radio-group
            :options="radio.qrcodeTime.list"
            v-model="radio.qrcodeTime.value"
          />
        </div>
      </div>
      <div class="item ml100 desc-text">
        * 失效后二维码不可再扫码添加
      </div>
      <div class="item ml30" v-if="radio.qrcodeTime.value === '1'">
        <div class="input flex">
          <a-input-number v-model="form.expireDay" class="mr10" :min="1" :max="60"/>
          天后过期
        </div>
      </div>
    </div>
    <div class="block">
      <div class="title">
        任务设置
      </div>
      <div class="item">
        <span class="label required">裂变任务设置：</span>
        <div class="input">
          <a-table
            :columns="taskTable.col"
            :data-source="taskTable.data"
            :rowKey="record=>record.id"
            :pagination="false"
            bordered
          >
            <span slot="taskTitle" slot-scope="item,v,index">
              {{ index + 1 }}级裂变任务
            </span>
            <span slot="userCount" slot-scope="item">
              <a-input placeholder="请输入人数" v-model="item.count" disabled/>
            </span>
            <span slot="tags">
              <a-tag>11</a-tag>
            </span>
          </a-table>
        </div>
      </div>
      <div class="item">
        <span class="label required">领奖方式：</span>
        <div class="input">
          <a-radio-group
            :options="radio.rewardMethod.list"
            v-model="radio.rewardMethod.value"
          />
        </div>
      </div>
      <div class="item" v-if="radio.rewardMethod.value === '0'">
        <div class="input">
          <a-button @click="prizeMemberShow">选择成员</a-button>
          <div class="mt10">
            <a-tag
              v-if="form.receivePrizeServices.length"
              v-for="v in form.receivePrizeServices"
              :key="v.id"
            >
              {{ v.name }}
            </a-tag>
          </div>
        </div>
      </div>
      <div class="item" v-if="radio.rewardMethod.value === '1'">
        <div class="input">
          <span class="desc-text">
            * 输入兑奖链接，客户完成任务后会跳转到新的链接领取
          </span>
          <a-alert
            message="提示：不建议填写直接领奖页面的链接，若客户复制领奖链接发给其他好友，其他未完成任务的客户也可以领取到奖励哦。为避免此行为，建议填写表单等无风险链接~"
            banner
            closable
            class="mt10"
          />
          <div class="flex mt16" v-for="(v,i) in taskTable.data" :key="i">
            <span>完成{{ i + 1 }}级任务：</span>
            <a-input
              v-model="v.prizeLink"
              placeholder="请输入链接"
              style="width: 300px"
            />
          </div>
        </div>
      </div>
    </div>

    <selectMember ref="selectMember" @change="memberSelectChange"/>
    <selectMember ref="selectPrizeMember" @change="prizeMemberChange"/>
  </div>
</template>

<script>
import selectMember from '@/components/Select/member'
import selectPublic from '@/components/Select/Public'
import moment from 'moment'

export default {
  data () {
    return {
      form: {
        name: '',
        services: [],
        autoPass: false,
        autoAddTag: false,
        endTime: '',
        expireDay: 0,
        newFriend: false,
        deleteInvalid: false,
        receivePrizeServices: [],
        receivePrizeLinks: []
      },
      radio: {
        qrcodeTime: {
          list: [
            { label: '立即失效', value: '0' },
            { label: '自定义日期', value: '1' }
          ],
          value: '0'
        },
        rewardMethod: {
          list: [
            { label: '联系客服', value: '0' },
            { label: '兑换链接', value: '1' }
          ],
          value: '0'
        }
      },
      taskTable: {
        col: [
          {
            title: '任务阶梯',
            scopedSlots: { customRender: 'taskTitle' }
          },
          {
            title: '任务目标人数',
            scopedSlots: { customRender: 'userCount' }
          }
        ],
        data: []
      }
    }
  },
  methods: {
    memberSelectShow () {
      this.$refs.selectMember.setSelect(this.form.services)
    },

    prizeMemberShow () {
      this.$refs.selectPrizeMember.setSelect(this.form.receivePrizeServices)
    },

    setOrgData (data) {
      this.form.name = data.fission.active_name
      this.form.services = JSON.parse(data.fission.service_employees)
      this.form.autoPass = data.fission.auto_pass === 'true'
      this.form.autoAddTag = data.fission.auto_add_tag === 'true'
      this.form.endTime = moment(data.fission.end_time)

      if (data.fission.qr_code_invalid >= 1) {
        this.radio.qrcodeTime.value = '1'
        this.form.expireDay = data.fission.qr_code_invalid
      }

      if (data.fission.receive_prize === 0) {
        this.radio.rewardMethod.value = '0'
        this.form.receivePrizeServices = JSON.parse(data.fission.receive_prize_employees)
      }

      this.taskTable.data = JSON.parse(data.fission.tasks).map((v, i) => {
        v.id = i
        return v
      })
    },

    getFormData () {
      const params = {
        ...this.form,
        tasks: this.taskTable.data,
        receivePrizeType: this.radio.rewardMethod.value
      }

      if (this.radio.rewardMethod.value === '1') {
        params.receivePrizeLinks = this.taskTable.data.map(v => {
          return v.prizeLink
        })
      }

      return params
    },

    prizeMemberChange (e) {
      this.form.receivePrizeServices = e
    },

    memberSelectChange (e) {
      this.form.services = e
    }
  },
  components: { selectMember, selectPublic }
}
</script>

<style lang="less" scoped>
.step1 {
  margin: 10px;
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
    margin-bottom: 23px;

    .label {
      color: rgba(0, 0, 0, .85);
      position: relative;
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

.add-task {
  width: 100%;
  height: 35px;
  border: 1px solid #e8e8e8;
  border-top: none;
  display: flex;
  align-items: center;
  padding-left: 10px;
  color: #1990ff;
  cursor: pointer;
}

.mb10 {
  margin-bottom: 10px;
}

/deep/ .ant-dropdown-menu-item {
  padding-bottom: 5px;
  padding-left: 8px;
}
</style>
