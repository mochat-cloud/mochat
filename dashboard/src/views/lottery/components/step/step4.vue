<template>
  <div class="prize-setting">

    <div class="title">
      奖品设置
      <span class="ml10">
        设置的所有奖项中奖概率总和应等于100%
      </span>
    </div>
    <div class="middle">
      <div class="prize-table">
        <a-table
          :columns="table.col"
          style="width: 800px"
          :data-source="table.data">
          <div slot="name" slot-scope="row">
            {{ row.name }}
          </div>
          <div slot="total" slot-scope="row">
            <div v-if="row.id === 1">
              -
            </div>
            <div v-else>
              <a-input style="width: 85px" default-value="0" v-model="row.num"/>
            </div>
          </div>
          <div slot="probability" slot-scope="row">
            {{ row.rate }}
            %
          </div>
          <div class="prize-img" slot="img" slot-scope="row">
            <img :src="row.image">
          </div>
        </a-table>
      </div>
      <div class="phone-box">
        <!--        <m-preview class="phone"/>-->
      </div>
    </div>
    <div class="limit">
      <div class="title mt30">
        抽奖限制设置
      </div>
      <div class="limit-set">
        <div class="sum required">
          <span>
            总抽奖机会：
          </span>
          <div class="sum-radio ml42">
            <a-radio-group name="radioGroup" :default-value="1" v-model="form.draw_set.type_total_num" disabled>
              <a-radio :value="1">
                不限
              </a-radio>
              <a-radio :value="2">
                限制
              </a-radio>
              <span v-show="form.draw_set.type_total_num === 2">
                每人最多有
                <a-input style="width: 60px" size="small" v-model="form.draw_set.max_total_num" disabled/>
                次抽奖机会
              </span>
            </a-radio-group>
          </div>
        </div>
        <div class="sum required">
          <span>
            每日抽奖机会：
          </span>
          <div class="sum-radio ml28">
            <a-radio-group name="radioGroup" :default-value="1" disabled>
              <a-radio :value="1">
                限制
              </a-radio>
            </a-radio-group>
          </div>
          <span>
            每人每天最多有
            <a-input style="width: 60px" size="small" v-model="form.draw_set.max_every_day_num" disabled/>
            次抽奖机会
          </span>
        </div>
        <div class="sum required">
          <span>
            每次抽奖消耗积分：
          </span>
          <div class="sum-radio">
            <a-radio-group name="radioGroup" :default-value="1" v-model="form.type_point" disabled>
              <a-radio :value="1">
                不消耗
              </a-radio>
              <a-radio :value="2">
                消耗
              </a-radio>
            </a-radio-group>
            <span v-show="form.type_point === 2">
              每人每次抽奖消耗
              <a-input style="width: 60px" size="small" v-model="form.draw_set.point_deplete" disabled/>
              分
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="select-limit">
      <div class="title mt30">
        中奖限制设置
      </div>
      <div class="select-limit-set mt24">
        <div class="select-sum required">
          <span>
            每人总中奖次数：
          </span>
          <div class="sum-radio ml14">
            <a-radio-group name="radioGroup" :default-value="2" v-model="form.win_set.type_total_num" disabled>
              <a-radio :value="2">
                限制
              </a-radio>
            </a-radio-group>
          </div>
          <span>
            用户每人最多中奖
            <a-input style="width: 60px" size="small" v-model="form.win_set.max_total_num" disabled/>
            次
          </span>
        </div>
        <div class="sum required">
          <span>
            每人每天中奖次数：
          </span>
          <div class="sum-radio">
            <a-radio-group name="radioGroup" :default-value="1" v-model="form.win_set.type_every_day_num" disabled>
              <a-radio :value="1">
                不限
              </a-radio>
              <a-radio :value="2">
                限制
              </a-radio>
              <span v-show="form.win_set.type_every_day_num === 2">
                每人每天最多中奖
                <a-input style="width: 60px" size="small" v-model="form.win_set.max_every_day_num" disabled/>
                次
              </span>
            </a-radio-group>
          </div>
        </div>
      </div>
    </div>
    <div class="business-card">
      <div class="title mt30">
        企业名片
      </div>
      <div class="information">
        <div class="portrait interval">
          <div class="corporate-avatar">企业头像：</div>
          <div class="upload-two">
            <m-upload ref="logo" v-model="form.corp_card.logo"/>
          </div>
        </div>
        <div class="corporate-name">
          <span class="cr-name interval">企业名称：
            <a-input placeholder="请输入企业名称" style="width: 340px" v-model="form.corp_card.name"/>
          </span>
        </div>
        <div class="mb14">
          <div class="profile-title">
            企业简介：
            <div class="switch">
              <a-switch size="small" default-checked v-model="tagSet"/>
            </div>
          </div>
        </div>
        <div class="profile" v-show="tagSet">
          <a-textarea
            placeholder="请输入企业简介"
            :rows="5"
            style="width: 340px"
            v-model="form.corp_card.description"/>
        </div>
      </div>
    </div>
  </div>
</template>

<script>

export default {
  data () {
    return {
      table: {
        col: [
          {
            title: '奖品名称',
            scopedSlots: { customRender: 'name' }
          },
          {
            title: '奖品总数',
            scopedSlots: { customRender: 'total' }
          },
          {
            title: '中奖概率',
            scopedSlots: { customRender: 'probability' }
          },
          {
            title: '奖品图片',
            scopedSlots: { customRender: 'img' }
          }
        ],
        data: []
      },
      form: {
        is_show: 1,
        exchange_set: [{
          name: '',
          type: 1,
          employee_qr: '',
          exchange_code: {
            description: ''
          }
        }],
        draw_set: {
          type_total_num: 1,
          max_total_num: '',
          type_every_day_num: 2,
          max_every_day_num: '',
          type_point: 1,
          point_deplete: ''
        },
        win_set: {
          type_total_num: 2,
          max_total_num: '',
          type_every_day_num: 1,
          max_every_day_num: ''
        },
        corp_card: {
          logo: '',
          name: '',
          description: ''
        }
      },
      tagSet: false
    }
  },
  methods: {
    getErrMsg () {
      for (const v of this.table.data) {
        if (v.id !== 1) {
          if (!v.name) return '请填写奖品名称'
          if (!v.num) return '请填写奖品数量'
          if (!v.rate) return '请填写中间概率'
          if (!v.type) return '请填写设置奖品兑换方式'
        }
      }

      if (this.form.draw_set.type_total_num === 2 && !this.form.draw_set.max_total_num) return '请填写总抽奖机会'
      if (!this.form.draw_set.max_every_day_num) return '请填写每日抽奖机会'
      if (!this.form.win_set.max_total_num) return '请填写每人总中奖次数'
      if (this.form.win_set.type_every_day_num === 2 && !this.form.win_set.max_every_day_num) return '请填写每人每天中奖次数'
    },

    setParams (data) {
      console.log(data)
      this.form.corp_card = data.prize.corpCard
      this.form.win_set = data.prize.winSet
      this.form.draw_set = data.prize.drawSet
      this.form.exchange_set = data.prize.exchangeSet
      this.table.data = data.prize.prizeSet
      this.form.corp_card.logo = this.$refs.logo.setUrl(data.prize.corpCard.logo)
      if (this.form.corp_card.description) {
        this.tagSet = true
      }
    },

    getParams () {
      this.table.data.forEach((value, index) => {
        value.id = index + 1
      })
      const exchangeSet = this.table.data.map(v => {
        return {
          name: v.name,
          type: v.type,
          employee_qr: v.employee_qr,
          exchange_code: v.exchange_code,
          description: v.description
        }
      })
      exchangeSet.splice(0, 1)
      return {
        prize_set: this.table.data,
        exchange_set: exchangeSet,
        ...this.form
      }
    },

    // 删除
    delTag (i) {
      if (this.pirzeList.length === 1) {
        this.$message.error('不能删除最后一个')

        return false
      }

      this.tagList.splice(i, 1)
    }
  }
}
</script>

<style lang="less" scoped>
.prize-img {
  /deep/ img {
    width: 50px;
    height: 50px;
  }

  /deep/ button {
    padding-left: 7px;
    padding-right: 8px;
    font-size: 11px;
    line-height: 1;
  }
}

.title {
  font-size: 15px;
  line-height: 21px;
  color: rgba(0, 0, 0, .85);
  border-bottom: 1px solid #e9ebf3;
  padding-bottom: 12px;
  width: 800px;
  margin-bottom: 16px;
  font-weight: bold;
  position: relative;

  span {
    font-size: 8px;
    color: #8d8d8d;
    font-weight: lighter;
  }
}

.small-text {
  font-size: 8px;
  color: #8d8d8d;
}

.required {
  position: relative;
}

.exchange-code {
  align-items: center;
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

.activity-name {
  display: flex;
}

.end {
  align-items: center;
  display: flex;
  margin-top: 16px;
}

.required {
  display: flex;
  margin-bottom: 16px;
}

.textarea-box {
  width: 460px;
  color: #999999;
  padding: 10px;
  background-color: #f8f8f8;
  border: 1px solid #e7e7e7;
}

.explain,
.label {
  display: flex;
  margin-top: 16px;
}

.tips {
  font-size: 8px;
  color: #8d8d8d;
}

.warning {
  width: 400px;
  font-size: 10px;
}

.label-box {
  width: 500px;
  padding-left: 18px;
  padding-top: 10px;
  padding-bottom: 10px;
  background-color: #f6f6f6;
}

.template {
  display: flex;
}

.fraction {
  display: flex;
  align-items: center;
  margin-top: 10px;
}

.profile-title {
  display: flex;
}

.prize-table {
  border: 1px solid #e7e7e7;
  width: 802px;
}

.profile {
  margin-left: 70px;
}

.phone-box {
  position: relative;
  width: 272px;

  .phone {
    position: absolute;
    top: 0;
    right: 20px;
    width: 100px;
  }
}

.middle {
  display: flex;
}

.add {
  padding-left: 17px;
  padding-top: 8px;
  padding-bottom: 8px;
}

.upload-img {
  margin-left: 110px;
}

.ft-box {
  width: 94px;
  margin-left: 6px;
}

.sum {
  margin-bottom: 20px;
  margin-top: 25px;
}

.interval {
  display: flex;
  margin-bottom: 23px;
}

.information {
  width: 600px;
  background-color: #fbf9f9;
  padding: 28px;
}

.portrait {
  a {
    position: relative;
    top: -14px;
    left: -16px;
    font-size: 14px;
  }
}

.upload-two {
  display: flex;
  margin-bottom: 16px;
}

</style>
