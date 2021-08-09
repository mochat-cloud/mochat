<template>
  <div class="set" >
    <div class="title">
      抽奖活动设置
    </div>
    <div class="content">
      <div class="template required">
        <span>
          活动模板：
        </span>
        <a-radio :defaultChecked="true" class="ml27">转盘</a-radio>
      </div>
      <div class="activity-name mt16 required">
        <span>
          抽奖活动名称：
        </span>
        <a-input style="width: 200px" v-model="form.name" disabled/>
      </div>
      <div class="explain mt16 required">
        <span>
          抽奖活动说明：
        </span>
        <a-textarea placeholder="请输入活动说明" :rows="6" style="width: 360px" v-model="form.description"/>
      </div>
      <div class="end">
        <span>
          活动结束时间：
        </span>
        <div>
          <a-radio-group disabled name="radioGroup" :default-value="1" v-model="form.time_type">
            <a-radio :value="1">
              永久有效
            </a-radio>
            <a-radio :value="2">
              自定义活动时间
            </a-radio>
            <div class="mt10 ml98" v-show="form.time_type === 2">
              <a-range-picker v-model="form.time"/>
            </div>
          </a-radio-group>
        </div>
      </div>
    </div>
    <div class="customer-label">
      <div class="title mt30">
        <span>
          客户标签设置
        </span>
        <!--        -->
        <a-switch size="small" :checked="form.tagList[0].tags.length!=0" class="ml4"/>
        <span class="tips ml10">
          最多可设置2个标签规则，已设置的标签规则不能进行修改
        </span>
      </div>
      <div v-show="form.tagList[0].tags.length!=0">
        <div class="warning">
          <a-alert message="企业微信接口限制，仅支持为已添加企业员工的客户打标签" type="info" show-icon class="warning"/>
        </div>
        <div class="label">
          <div>
            <span>
              客户标签：
            </span>
          </div>
          <div class="label-rule">
            <div class="label-box">
              <span>
                规则：
              </span>
              <div class="label-top mt8">
                <div v-for="(v,i) in form.tagList" :key="i" class="mb8 label-tag">
                  <div class="tag">
                    为
                    <a-select disabled style="width: 140px" default-value="请选择用户行为" v-model="v.action">
                      <a-select-option value="1">
                        已参与活动
                      </a-select-option>
                      <a-select-option value="2">
                        已抽中商品
                      </a-select-option>
                    </a-select>
                    的客户,打上
                    <div class="choiceAdmin">
                      <span class="operationTips" v-if="v.clientTagArr.length==0">请选择标签</span>
                      <a-tag v-for="(obj,idx) in v.clientTagArr" :key="idx">
                        {{ obj.name }}
                      </a-tag>
                    </div>
                    标签
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <addlableIndex @choiceTagsArr="acceptArray" ref="childRef"/>
  </div>
</template>

<script>
import addlableIndex from '@/components/addlabel/index'
import moment from 'moment'

export default {
  components:
    {
      addlableIndex
    },

  data () {
    return {
      visible: false,
      data: [],
      tagSet: false,
      form: {
        name: '',
        description: '',
        type: 'roulette',
        time_type: 1,
        tagList: [
          {
            action: '1',
            tags: [],
            type: '2',
            grade: '',
            scoreShow: true,
            clientTagArr: []
          }
        ],
        time: []
      },
      //  选中的输入框
      selectInputIndex: 0,
      //  删除所选输入框中的标签
      delSelectlable: 0,

      reset: true
    }
  },
  methods: {
    getErrMsg () {
      if (!this.form.name) return '活动名称未填写'
      if (!this.form.description) return '活动说明未填写'
      if (this.form.time_type === 2 && !this.form.time) return '活动说明未填写'
    },
    getParams () {
      return {
        name: this.form.name,
        description: this.form.description,
        type: this.form.type,
        time_type: this.form.time_type,
        contact_tags: this.form.tagList,
        start_time: moment(this.form.time[0]).format('YYYY-MM-DD HH:mm:ss'),
        end_time: moment(this.form.time[1]).format('YYYY-MM-DD HH:mm:ss')
      }
    },
    setParams (res) {
      console.log(res)
      this.form = {
        name: res.info.name,
        description: res.info.description,
        type: res.info.type,
        time_type: res.info.timeType,
        time: [
          moment(res.info.startTime),
          moment(res.info.endTime)
        ],
        tagList: res.info.contactTags
      }
      console.log(this.form)
    },
    acceptArray (e) {
      if (e) {
        const pagArray = []
        e.forEach((item, index) => {
          const tagsArray = {
            tagid: '',
            tagname: ''
          }
          tagsArray.tagid = item.id
          tagsArray.tagname = item.name
          this.form.tagList[this.selectInputIndex].tags.push(tagsArray)
          pagArray[index] = item.name
        })
        this.form.tagList[this.selectInputIndex].clientTagArr = pagArray
      } else {
        this.form.tagList[this.selectInputIndex].clientTagArr = []
        this.form.tagList[this.selectInputIndex].tags = []
      }

      this.reset = false
      this.reset = true
    },

    // 删除标签
    lableCutto (value) {
      this.form.tagList[this.selectInputIndex].clientTagArr.forEach((item, index) => {
        if (item.tagname === value) {
          this.form.tagList[this.selectInputIndex].clientTagArr.splice(index, 1)
        }
      })

      this.reset = false
      this.reset = true
    },
    handchange (index) {
      this.selectInputIndex = index
    }
  }
}
</script>

<style lang="less" scoped>
.fraction-box{
  display: flex;
  align-items: center;
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

  span{
    font-size: 8px;
    color: #8d8d8d;
    font-weight: lighter;
  }
}

.tag{
  display: flex;
  align-items: center;
}

.small-text{
  font-size: 8px;
  color: #8d8d8d;
}

.required{
  position: relative;
}

.exchange-code{
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

.activity-name{
  display: flex;
}

.end{
  display: flex;
  margin-top: 16px;
}
.required{
  display: flex;
  margin-bottom: 16px;
}

.textarea-box{
  width: 460px;
  color: #999999;
  padding: 10px;
  background-color: #f8f8f8;
  border: 1px solid #e7e7e7;
}

.explain,
.label{
  display: flex;
  margin-top: 16px;
}

.tips{
  font-size: 8px;
  color: #8d8d8d;
}

.warning{
  width: 400px;
  font-size: 10px;
}

.label-box{
  width: 600px;
  padding-left: 18px;
  padding-top: 10px;
  padding-bottom: 10px;
  background-color: #f6f6f6;
}

.template{
  display: flex;
}

.fraction{
  display: flex;
  align-items: center;
  margin-top: 10px;
}

.profile-title{
  display: flex;
}

.prize-table{
  border: 1px solid #e7e7e7;
  width: 802px;
}

.profile{
  margin-left: 70px;
}

.phone-box{
  position: relative;
  width: 272px;

  .phone{
    position: absolute;
    top: 0;
    right: 20px;
    width: 100px;
  }
}

.middle{
  display: flex;
}

.add{
  padding-left: 17px;
  padding-top: 8px;
  padding-bottom: 8px;
}

.upload-img{
  margin-left: 110px;
}

.ft-box{
  width: 94px;
  margin-left: 6px;
}

.sum{
  margin-bottom: 20px;
  margin-top: 25px;
}

.interval{
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

.label-box{
  border-bottom: 1px dashed #dcdcdc;
}
.add{
  background-color: #f6f6f6;
}
</style>
