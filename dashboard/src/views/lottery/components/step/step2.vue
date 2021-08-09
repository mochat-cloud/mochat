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
            <a-input :disabled="row.id === 1" style="width: 130px" placeholder="请输入奖品名称" v-model="row.name"/>
          </div>
          <div slot="total" slot-scope="row">
            <a-input v-if="row.id !== 1" style="width: 85px" default-value="0" v-model="row.num" @input="row.num=row.num.replace(/[^\d.]/g,'')"/>
            <a-input v-else style="width: 85px" value="-" disabled></a-input>
          </div>
          <div slot="probability" slot-scope="row">
            <a-input style="width: 85px;" class="mr4" default-value="0" v-model="row.rate" @input="row.rate=row.rate.replace(/[^\d.]/g,'')"/>
            %
          </div>
          <div class="prize-img" slot="img" slot-scope="row">
            <m-upload :def="false" text="上传奖品图片" type="btn" v-model="row.image"/>
          </div>
          <div slot="exchange" slot-scope="row">
            <a @click="$refs.exchange.show(row.id, row)" v-if="row.id !== 1">
              设置
            </a>
            <div v-if="reset">
              <span v-if="row.type">
                {{ row.type === 1 ? '(客户二维码)' : '(兑换码)' }}
              </span>
            </div>
          </div>
          <div slot="operation" slot-scope="row,index,indext">
            <a @click="delPrize(indext)" v-if="row.id !== 1">删除</a>
          </div>
        </a-table>
        <div class="add">
          <a @click="pushPrize">
            <a-icon type="plus-circle"/>
            <span>
              添加奖项
            </span>
          </a>
        </div>
      </div>
      <div class="phone-box">
        <!--        <m-preview class="phone"/>-->
      </div>
    </div>
    <div class="record mt18">
      <a-switch size="small" default-checked class="mr6" v-model="form.is_show"/>
      <span style="font-size: 6px">
        实时展示已中奖客户记录
      </span>
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
            <a-radio-group name="radioGroup" :default-value="1" v-model="form.draw_set.type_total_num">
              <a-radio :value="1">
                不限
              </a-radio>
              <a-radio :value="2">
                限制
              </a-radio>
              <span v-show="form.draw_set.type_total_num === 2">
                每人最多有
                <a-input style="width: 60px" size="small" v-model="form.draw_set.max_total_num"/>
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
            <a-radio-group name="radioGroup" :default-value="1">
              <a-radio :value="1">
                限制
              </a-radio>
            </a-radio-group>
          </div>
          <span>
            每人每天最多有
            <a-input style="width: 60px" size="small" v-model="form.draw_set.max_every_day_num"/>
            次抽奖机会
          </span>
        </div>
        <!--        <div class="sum required">-->
        <!--          <span>-->
        <!--            每次抽奖消耗积分：-->
        <!--          </span>-->
        <!--          <div class="sum-radio">-->
        <!--            <a-radio-group name="radioGroup" :default-value="1" v-model="form.draw_set.type_point">-->
        <!--              <a-radio :value="1">-->
        <!--                不消耗-->
        <!--              </a-radio>-->
        <!--              <a-radio :value="2">-->
        <!--                消耗-->
        <!--              </a-radio>-->
        <!--            </a-radio-group>-->
        <!--            <span v-show="form.draw_set.type_point === 2">-->
        <!--              每人每次抽奖消耗-->
        <!--              <a-input style="width: 60px" size="small" v-model="form.draw_set.point_deplete"/>-->
        <!--              分-->
        <!--            </span>-->
        <!--          </div>-->
        <!--        </div>-->
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
            <a-radio-group name="radioGroup" :default-value="2" v-model="form.win_set.type_total_num">
              <a-radio :value="2">
                限制
              </a-radio>
            </a-radio-group>
          </div>
          <span>
            用户每人最多中奖
            <a-input style="width: 60px" size="small" v-model="form.win_set.max_total_num"/>
            次
          </span>
        </div>
        <div class="sum required">
          <span>
            每人每天中奖次数：
          </span>
          <div class="sum-radio">
            <a-radio-group name="radioGroup" :default-value="1" v-model="form.win_set.type_every_day_num">
              <a-radio :value="1">
                不限
              </a-radio>
              <a-radio :value="2">
                限制
              </a-radio>
              <span v-show="form.win_set.type_every_day_num === 2">
                每人每天最多中奖
                <a-input style="width: 60px" size="small" v-model="form.win_set.max_every_day_num"/>
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
            <m-upload :def="false" text="请上传头像" v-model="form.corp_card.logo"/>
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

    <exchange ref="exchange" @change="exchangeChange"/>
  </div>
</template>

<script>
import exchange from '@/views/lottery/components/exchange'

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
          },
          {
            title: '兑奖方式',
            scopedSlots: { customRender: 'exchange' }
          },
          {
            title: '操作',
            scopedSlots: { customRender: 'operation' }
          }
        ],
        data: [
          {
            name: '谢谢参与',
            num: 10000000,
            rate: '50',
            image: '',
            id: 1
          },
          {
            name: '奖项1',
            num: 10,
            rate: '10',
            image: '',
            id: 2
          },
          {
            name: '奖项2',
            num: 10,
            rate: '10',
            image: '',
            id: 3
          },
          {
            name: '奖项3',
            num: 10,
            rate: '30',
            image: '',
            id: 4
          }
        ]
      },
      form: {
        is_show: true,
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
      tagSet: false,
      reset: true
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

        if (Number(v.rate) > 100 || Number(v.rate) < 0) return '中奖概率填写错误'
      }

      if (this.form.draw_set.type_total_num === 2 && !this.form.draw_set.max_total_num) return '请填写总抽奖机会'
      if (!this.form.draw_set.max_every_day_num) return '请填写每日抽奖机会'
      if (!this.form.win_set.max_total_num) return '请填写每人总中奖次数'
      if (this.form.win_set.type_every_day_num === 2 && !this.form.win_set.max_every_day_num) return '请填写每人每天中奖次数'
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

    // 兑换设置回调
    exchangeChange (e) {
      for (const v of this.table.data) {
        if (v.id === e.id) {
          v.type = e.type
          v.employee_qr = e.qrCode
          v.exchange_code = e.code
          v.description = e.description
          v.exchange_code_old = e.exchange_code_old

          this.reset = false
          this.reset = true

          break
        }
      }
    },

    // 删除奖项
    delPrize (indext) {
      if (this.table.data.length === 3) {
        this.$message.error('最少添加3个奖项')
        return false
      }
      this.table.data.splice(indext, 1)
    },

    // 添加奖项
    pushPrize () {
      if (this.table.data.length === 7) {
        this.$message.error('最多添加7个奖项')

        return false
      }

      this.table.data.push({
        name: `奖项${this.table.data.length + 1}`
      })
    },

    // 删除
    delTag (i) {
      if (this.pirzeList.length === 1) {
        this.$message.error('不能删除最后一个')

        return false
      }

      this.tagList.splice(i, 1)
    }
  },
  components: { exchange }
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
