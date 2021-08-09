<template>
  <div class="rule-box">
    <a-card>
      <div class="set-keywords">
        <div class="title">
          规则基础信息
        </div>
        <div class="content mt22">
          <div class="mode elastic">
            <span class="tip-gray">根据规则，当客户在设定时间段内成为企业微信客户，将自动被打上标签</span>
          </div>
          <div class="mode elastic mt24">
            <span>规则名称：</span>
            <div class="add-key">
              <a-input placeholder="请填写规则名称，仅内部可见" v-model="formAskData.name" style="width: 300px"/>
            </div>
          </div>
          <div class="mode elastic mt24">
            <span>生效成员：</span>
            <div class="btn">
              <a-dropdown>
                <a-button
                  style="margin-left: 8px;
                  width: 180px"
                  @click="$refs.selectMember.setSelect(showSelectStaff)"
                > 选择生效成员<a-icon type="down" />
                </a-button>
              </a-dropdown>
            </div>
            <selectMember ref="selectMember" @change="effectStaff"/>
          </div>
          <div class="memberRow">
            <div class="tags" v-for="(item,index) in showSelectStaff" :key="index">
              {{ item.name }}
              <span class="closeIcon" @click="delIconSpan(index)"><a-icon type="close" /></span>
            </div>
          </div>

        </div>
      </div>
      <div class="automatic mt50">
        <div class="title">设置打标签规则</div>
        <div class="content mt30">
          <div class="task">
            <div class="task-padding" v-for="(item,index) in tag_rule" :key="index">
              <div class="task-content">
                规则{{ index+1 }}：为每
                <div class="task-content ml4 mr4">
                  <a-select v-model="item.time_type" style="width: 65px" @change="switchTimeType(index)">
                    <a-select-option :value="1">天</a-select-option>
                    <a-select-option :value="2">周</a-select-option>
                    <a-select-option :value="3">月</a-select-option>
                  </a-select>
                </div>
                <div style="display: flex;">
                  <a-select
                    mode="tags"
                    style="width: 490px;"
                    placeholder="请选择日期"
                    v-if="item.time_type==2"
                    v-model="item.schedule">
                    <a-select-option v-for="(obj,idx) in weekData" :key="idx" :value="obj.key">{{ obj.value }}</a-select-option>
                  </a-select>
                  <a-select
                    mode="tags"
                    style="width: 490px;"
                    placeholder="请选择日期"
                    v-if="item.time_type==3"
                    v-model="item.schedule">
                    <a-select-option v-for="(obj,idx) in monthData" :key="idx" :value="obj.key">{{ obj.value }}</a-select-option>
                  </a-select>
                  <a-time-picker
                    placeholder="开始时间"
                    v-model="item.start_time"
                    @change="addStartTime()"
                    class="mr10 ml8"
                    style="width: 100px;"
                  />
                  <a-time-picker
                    placeholder="结束时间"
                    v-model="item.end_time"
                    style="width: 100px;"
                    class="mr6"/>
                </div>
                <span>添加的用户</span>
              </div>
              <div class="tag">打上
                <div @click="showModal(index)" class="choiceAdmin">
                  <span class="operationTips" v-if="item.clientTagArr.length==0">请选择标签</span>
                  <a-tag v-for="(obj,idx) in item.clientTagArr" :key="idx">
                    {{ obj.name }}
                    <a-icon type="close" @click.stop="delTagsArr(index,idx)" />
                  </a-tag>
                </div>
                的标签
                <span class="delete-icon" style="margin-left: 7px;cursor: pointer;" @click="delTag(index)" v-if="tag_rule.length>1">
                  <a-icon type="minus-circle"/>
                </span>
              </div>
            </div>
            <!--   弹窗           -->
            <addlableIndex @choiceTagsArr="acceptArray" ref="childRef"/>
            <div class="add-task">
              <a @click="addRuleName" v-if="tag_rule.length<10"><a-icon type="plus-circle" class="icon"/>添加规则</a>
              <span v-else><a-icon type="plus-circle" class="icon"/>添加规则</span>
              <span class="tip-gray">（添加的多条规则可同时生效，最多设置5个规则）</span>
            </div>
          </div>
        </div>
      </div>
      <div class="create-btn mt60">
        <a-button type="primary" @click="setUpRule">创建规则</a-button>
      </div>
    </a-card>
  </div>
</template>

<script>
import { storeApi } from '@/api/autoTag'
import moment from 'moment'
import addlableIndex from '@/components/addlabel/index'
import selectMember from '@/components/Select/member'
export default {
  components: { addlableIndex, selectMember },
  data () {
    return {
      weekData: [
        {
          key: '0',
          value: '周日'
        },
        {
          key: '1',
          value: '周一'
        },
        {
          key: '2',
          value: '周二'
        }, {
          key: '3',
          value: '周三'
        }, {
          key: '4',
          value: '周四'
        }, {
          key: '5',
          value: '周五'
        }, {
          key: '6',
          value: '周六'
        }
      ],
      monthData: [
        {
          key: '1',
          value: '1号'
        }, {
          key: '2',
          value: '2号'
        }, {
          key: '3',
          value: '3号'
        }, {
          key: '4',
          value: '4号'
        }, {
          key: '5',
          value: '5号'
        }, {
          key: '6',
          value: '6号'
        }, {
          key: '7',
          value: '7号'
        }, {
          key: '8',
          value: '8号'
        }, {
          key: '9',
          value: '9号'
        }, {
          key: '10',
          value: '10号'
        }, {
          key: '11',
          value: '11号'
        }, {
          key: '12',
          value: '12号'
        }, {
          key: '13',
          value: '13号'
        }, {
          key: '14',
          value: '14号'
        }, {
          key: '15',
          value: '15号'
        }, {
          key: '16',
          value: '16号'
        }, {
          key: '17',
          value: '17号'
        }, {
          key: '18',
          value: '18号'
        }, {
          key: '19',
          value: '19号'
        }, {
          key: '20',
          value: '20号'
        }, {
          key: '21',
          value: '21号'
        }, {
          key: '22',
          value: '22号'
        }, {
          key: '23',
          value: '23号'
        }, {
          key: '24',
          value: '24号'
        }, {
          key: '25',
          value: '25号'
        }, {
          key: '26',
          value: '26号'
        }, {
          key: '27',
          value: '27号'
        }, {
          key: '28',
          value: '28号'
        }, {
          key: '29',
          value: '29号'
        }, {
          key: '30',
          value: '30号'
        }, {
          key: '31',
          value: '31号'
        }, {
          key: '32',
          value: '32号'
        }
      ],
      //  表单请求数据
      formAskData: {
        // 打标签方式
        type: 3,
        // 规则名称
        name: '',
        // 生效成员
        employees: [],
        //  规则
        tag_rule: []
      },
      // 打标签规则
      tag_rule: [
        {
          // 时间类型
          time_type: 1,
          // 开始时间
          start_time: '',
          // 结束时间
          end_time: '',
          schedule: [],
          // 请求标签
          tags: [],
          //  客户选中标签
          clientTagArr: []
        }
      ],
      //  弹窗数据
      popupMemberData: [],
      //  展示数据
      showSelectStaff: []

    }
  },
  created () {
  },
  methods: {
    moment,
    // 切换时间类型
    switchTimeType (index) {
      this.tag_rule[index].schedule = []
    },
    // 获取员工选中的员工数据
    effectStaff (e) {
      this.showSelectStaff = e
      e.forEach((item, index) => {
        this.formAskData.employees[index] = item.wxUserId
      })
    },
    // 删除生效成员
    delIconSpan (index) {
      this.showSelectStaff.splice(index, 1)
      this.formAskData.employees.splice(index, 1)
    },
    // 组件
    // 显示弹窗
    showModal (index) {
      this.selectInputIndex = index
      this.$refs.childRef.show(this.tag_rule[this.selectInputIndex].clientTagArr)
    },
    // 删除标签
    delTagsArr (index, idx) {
      this.tag_rule[index].tags.splice(idx, 1)
      this.tag_rule[index].clientTagArr.splice(idx, 1)
    },
    // 接收子组件数据
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
          this.tag_rule[this.selectInputIndex].tags.push(tagsArray)
          pagArray[index] = item
        })
        this.tag_rule[this.selectInputIndex].clientTagArr = pagArray
      } else {
        this.tag_rule[this.selectInputIndex].clientTagArr = []
        this.tag_rule[this.selectInputIndex].tags = []
      }
    },

    // 删除标签
    delTag (index) {
      this.tag_rule.splice(index, 1)
    },
    // 添加标签
    addRuleName () {
      const ruleTemplate = {
        // 时间类型
        time_type: 1,
        // 开始时间
        start_time: '',
        // 结束时间
        end_time: '',
        // 请求标签
        tags: [],
        schedule: [],
        //  客户选中标签
        clientTagArr: []
      }
      this.tag_rule.push(ruleTemplate)
    },
    // 添加开始时间
    addStartTime (index) {
    },
    //  创建规则
    setUpRule () {
      if (this.formAskData.name == '') {
        this.$message.error('规则名称不能为空')
        return false
      }
      if (this.formAskData.employees == '') {
        this.$message.error('生效成员不能为空')
        return false
      }
      //  标签规则
      for (let i = 0; i < this.tag_rule.length; i++) {
        const ruleContent = {
          // 时间类型
          time_type: 1,
          // 开始时间
          start_time: '',
          // 结束时间
          end_time: '',
          schedule: [],
          // 请求标签
          tags: []
        }
        if (this.tag_rule[i].time_type != 1) {
          if (this.tag_rule[i].schedule.length == 0) {
            this.$message.error('规则' + (i + 1) + '选择的日期不能为空')
            return false
          }
        }
        if (this.tag_rule[i].start_time == '') {
          this.$message.error('规则' + (i + 1) + '开始时间不能为空')
          return false
        }
        if (this.tag_rule[i].end_time == '') {
          this.$message.error('规则' + (i + 1) + '结束时间不能为空')
          return false
        }
        if (this.tag_rule[i].start_time > this.tag_rule[i].end_time) {
          this.$message.error('规则' + (i + 1) + '开始时间不得大于结束时间')
          return false
        }
        if (this.tag_rule[i].tags == '') {
          this.$message.error('规则' + (i + 1) + '需要打上的标签不能为空')
          return false
        }
        ruleContent.time_type = this.tag_rule[i].time_type
        ruleContent.start_time = this.tag_rule[i].start_time
        ruleContent.end_time = this.tag_rule[i].end_time
        ruleContent.tags = this.tag_rule[i].tags
        ruleContent.schedule = this.tag_rule[i].schedule
        this.formAskData.tag_rule.push(ruleContent)
      }
      this.formAskData.tag_rule.forEach((item, index) => {
        item.start_time = item.start_time.format('HH:mm:ss')
        item.end_time = item.end_time.format('HH:mm:ss')
      })
      storeApi(this.formAskData).then((res) => {
        this.$message.success('创建成功')
        this.$router.push({ path: '/autoTag/ruleTagging' })
      })
    }
  }
}
</script>

<style lang="less" scoped>
.title {
  font-size: 15px;
  line-height: 21px;
  color: rgba(0, 0, 0, .85);
  border-bottom: 1px solid #e9ebf3;
  padding-bottom: 16px;
  margin-bottom: 16px;
  position: relative;
}
.choiceAdmin{
  width: 280px;
  min-height: 32px;
  background: #fff;
  border-radius: 4px;
  border: 1px solid #dcdfe6;
  cursor: pointer;
  padding-left: 5px;
  margin-right: 8px;
  line-height: 30px;
}
.operationTips{
  color: #b2b2b2;
}
.memberRow{
  margin-left: 78px;
  margin-top: 17px;
  display: flex;
}
.memberRow .tags{
  margin-left: 17px;
  background: #f7f7f7;
  border-radius: 2px;
  padding: 5px 10px;
  font-size: 14px;
  line-height: 22px;
  color: rgba(0, 0, 0, 0.85);
  position: relative;
}
.tags .closeIcon{
  width: 14px;
  height: 14px;
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
.memberRow .tags:first-child{
  margin-left: 0;
}
.elastic{
  display: flex;
  align-items: center;
}
.tip-gray{
  margin-top: 6px;
  font-size: 8px;
  color: #b8b8b8;
}
.mode{
  .block{
    background: #fff;
    border: 1px solid rgba(0,0,0,.15);
    box-sizing: border-box;
    border-radius: 4px;
    width: 160px;
    height: 32px;
    line-height: 32px;
    padding-left: 8px;
    font-size: 14px;
    color: rgba(0,0,0,.85);
  }
}
.tips{
  font-size: 12px;
  margin-left: 6px;
  color: #b8b8b8;
}
.task {
  background-color: #fbfbfb;
  border: 1px solid #e2e2e2;
}
.task-content {
  display: flex;
  align-items: center;
}
.task-padding {
  padding: 15px;
  border-bottom: 1px dashed #e2e2e2;
}
.add-task {
  padding-left: 14px;
  padding-top: 8px;
  padding-bottom: 8px;
  .icon {
    padding-right: 3px;
  }
}
.tag{
display: flex;
  align-items: center;
  margin-top: 10px;
  margin-left: 50px;
}
</style>
