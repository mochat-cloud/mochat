<template>
  <div>
    <a-card title="添加质检规则" :bordered="false">
      <a-form-model ref="ruleForm" :model="ruleForm" v-bind="layout">
        <a-form-model-item has-feedback label="规则名称" prop="pass" :wrapper-col="{ span: 10 }">
          <a-input v-model="ruleForm.name" autocomplete="off" :maxLength="20" placeholder="名称不会展示给用户，用于企业记录渠道名称或使用场景" />
        </a-form-model-item>
        <a-form-model-item has-feedback label="添加群聊" prop="pass">
          <div class="addGroup">
            <a-button class="addBtngroup" @click="$refs.selectGroup.show()"><a-icon type="plus" />添加群聊</a-button>
          </div>
          <div class="chosenGroup">
            <div class="chosenElement" v-for="(item,index) in ruleForm.rooms" :key="index">
              <img src="../../assets/avatar-room-default.svg" alt="">
              <div class="chosenRight">
                <div class="name">{{ item.name }}</div>
                <div class="num">{{ item.contact_num }}/{{ item.roomMax }}</div>
              </div>
              <a-popover placement="topLeft" v-if="item.audit_status==0">
                <template slot="content">
                  <span>此群聊内没有员工开通消息存档功能</span>
                </template>
                <div class="notallyWith"></div>
              </a-popover>
            </div>
          </div>
        </a-form-model-item>
        <!--                  选择群聊弹窗-->
        <selectGroup ref="selectGroup" @change="receiveGroup" />
        <a-form-model-item label="质检时间">
          <a-radio-group v-model="ruleForm.quality_type">
            <a-radio :value="1">全天检测</a-radio>
            <a-radio :value="2">自定义质检时间</a-radio>
          </a-radio-group>
          <div class="customizeTime" v-if="ruleForm.quality_type==2">
            <div class="customizeTips">
              <div>自动上下线</div>
              <div class="tipsRule">
                <div>1、可用于员工早晚班等不同上下班时间段使用；</div>
                <div>2、员工在非线上时间将不会收到推送提醒。</div>
              </div>
            </div>
            <div class="customizeContent" v-for="(item,index) in customTimeArray" :key="index">
              <div class="customDate">
                <div>工作周期{{ index+1 }}：</div>
                <div class="cycleOption">
                  <a-tree-select
                    v-model="item.week"
                    style="width: 100%"
                    :tree-data="treeData"
                    tree-checkable
                    placeholder="请选择工作周期"
                  />
                  <span class="downIcon"><a-icon type="down" /></span>
                </div>
              </div>
              <div class="customTime">
                <div>质检时间：</div>
                <div>
                  <a-time-picker format="HH:mm" v-model="item.start_time" placeholder="开始时间" />
                  ~
                  <a-time-picker format="HH:mm" v-model="item.end_time" placeholder="结束时间" />
                </div>
              </div>
              <div class="customDelBtn" v-if="customTimeArray.length!=1" @click="customDelEvent(index)"><a-icon type="delete" /></div>
            </div>
            <div class="addCycleBtn" @click="addCycleEvent"><a-icon type="plus-circle" class="addBtnIcon" />添加其他工作时间</div>
          </div>
        </a-form-model-item>
        <a-form-model-item label="添加规则">
          <div class="addRuleRow">
            <div class="addRuleCont">
              <div v-for="(item,index) in ruleArray" :key="index" class="addRuleContRow">
                <div>
                  <a-icon type="minus-circle" class="delRuleBtn" v-if="index!=0" @click="delRuleEvent(index)" />
                  规则{{ index+1 }}：为超过
                  <a-input-number id="inputNumber" v-model="item.num" :min="1" />
                  <a-select
                    v-model="item.time_type"
                    style="width: 120px;margin-left: 10px"
                  >
                    <a-select-option :value="1">分钟</a-select-option>
                    <a-select-option :value="2">时</a-select-option>
                    <a-select-option :value="3">天</a-select-option>
                  </a-select>
                  未回复客户消息的群聊，
                </div>
                <div class="choiceRow">给
                  <a-select
                    v-model="item.employee_type"
                    style="width: 200px;"
                    class="choice_admin"
                  >
                    <a-select-option :value="1">管理员/部门管理员</a-select-option>
                    <a-select-option :value="2">客户群群主</a-select-option>
                  </a-select>
                  <div style="margin-left: 211px;display: flex;">
                    <div class="choiceAdmin" v-if=" item.employee_type==1 " @click="showMemberPop(index)">
                      <span class="operationTips" v-if="item.showEmployee.length==0">请选择管理员/部门管理员</span>
                      <div>
                        <a-tag v-for="(obj,idx) in item.showEmployee" :key="idx">{{ obj.name }}</a-tag>
                      </div>
                    </div>
                    发送行为
                  </div>
                </div>
              </div>
            </div>
            <selectMember ref="selectMember" @change="effectStaff"/>
            <div class="addRuleBtn" @click="addRuleEvent" v-if="ruleArray.length<8">
              <a-icon type="plus-circle" class="addBtnIcon" />添加规则
              <span class="addLimit">(最多设置8个规则)</span>
            </div>
            <div class="addRuleBtn2" v-else><a-icon type="plus-circle" class="addBtnIcon" />添加规则<span class="addLimit">(最多设置8个规则)</span></div>
          </div>
        </a-form-model-item>
        <a-form-model-item label="群聊质检白名单">
          <a-row>
            <a-switch
              :defaultChecked="ruleForm.white_list_status==1"
              style="margin-right: 10px;"
              size="small"
              @change="()=>{
                if(this.ruleForm.white_list_status==1){
                  this.ruleForm.white_list_status=0
                }else{
                  this.ruleForm.white_list_status=1
                }
              }"
            />
            <span v-if="ruleForm.white_list_status">已开启</span>
            <span v-else>已关闭</span>
            <span class="whiteTips">（多条关键词，可同时生效）</span>
          </a-row>
          <div class="addkeyRow" v-if="ruleForm.white_list_status">
            <a-input
              class="input-new-tag"
              v-model="inputValue"
              ref="saveTagInput"
              v-if="inputVisible"
              @keyup.enter.native="handleInputConfirm"
              @blur="handleInputConfirm"
              :maxLength="8"
            >
            </a-input>
            <a-button v-else class="button-new-tag" @click="showInput">+ 添加关键词</a-button>
            <a-tag
              :key="tag"
              v-for="tag in ruleForm.keyword"
              closable
              :disable-transitions="false"
              @close="handleClose(tag)">
              {{ tag }}
            </a-tag>
          </div>
        </a-form-model-item>
        <a-form-model-item :wrapper-col="{ span: 14 }">
          <a-button type="primary" @click="submitForm" class="establishBtn">创建</a-button>
        </a-form-model-item>
      </a-form-model>
    </a-card>
  </div>
</template>
<script>
// eslint-disable-next-line no-unused-vars
import { storeApi } from '@/api/roomQuality'
import selectGroup from '@/components/Select/group'
import selectMember from '@/components/Select/member'
export default {
  components: { selectGroup, selectMember },
  data () {
    return {
      // 展示自定义工作周期内容
      customTimeArray: [
        {
          week: ['周一', '周二', '周三', '周四', '周五', '周六', '周日'],
          start_time: '',
          end_time: ''
        }
      ],
      // 展示规则内容
      ruleArray: [
        {
          num: 30,
          time_type: 1,
          employee_type: 1,
          employee: [],
          showEmployee: []
        }
      ],
      // 获取保存被点击的规则
      indexRuleNum: 0,
      inputVisible: false,
      inputValue: '',
      treeData: [
        {
          title: '周一',
          value: '周一',
          key: '周一'
        },
        {
          title: '周二',
          value: '周二',
          key: '周二'
        },
        {
          title: '周三',
          value: '周三',
          key: '周三'
        },
        {
          title: '周四',
          value: '周四',
          key: '周四'
        },
        {
          title: '周五',
          value: '周五',
          key: '周五'
        },
        {
          title: '周六',
          value: '周六',
          key: '周六'
        },
        {
          title: '周日',
          value: '周日',
          key: '周日'
        }
      ],
      customTimeValue: ['周一', '周二', '周三', '周四', '周五', '周六', '周日'],
      // 表单数据
      ruleForm: {
        // 规则名称
        name: '',
        // 添加群聊
        rooms: [],
        // 质检时间
        quality_type: 1,
        // 工作周期
        work_cycle: [],
        // 质检规则
        rule: [],
        // 质检白名单
        white_list_status: 0,
        keyword: []
      },
      // 表格布局
      layout: {
        labelCol: { span: 3 },
        wrapperCol: { span: 14 }
      }
    }
  },
  methods: {
    // 显示选择成员弹窗
    showMemberPop (index) {
      this.indexRuleNum = index
      this.$refs.selectMember.show()
    },
    // 接收群聊子组件数据
    receiveGroup (e) {
      this.ruleForm.rooms = e
    },
    // 接收成员子组件数据
    effectStaff (e) {
      const tranRuleData = []
      e.forEach((item, index) => {
        tranRuleData[index] = item.id
      })
      this.ruleArray[this.indexRuleNum].showEmployee = e
      this.ruleArray[this.indexRuleNum].employee = tranRuleData
    },
    handleClose (tag) {
      this.ruleForm.keyword.splice(this.ruleForm.keyword.indexOf(tag), 1)
    },
    showInput () {
      this.inputVisible = true
      this.$nextTick(_ => {
        this.$refs.saveTagInput.$refs.input.focus()
      })
    },
    handleInputConfirm () {
      const inputValue = this.inputValue
      if (inputValue) {
        this.ruleForm.keyword.push(inputValue)
      }
      this.inputVisible = false
      this.inputValue = ''
    },
    // 提交
    submitForm () {
      if (this.ruleForm.name == '') {
        this.$message.error('规则名称不能为空')
        return false
      }
      if (this.ruleForm.rooms == '') {
        this.$message.error('群聊不能为空')
        return false
      }
      // 工作周期
      if (this.ruleForm.quality_type == 2) {
        this.ruleForm.work_cycle = []
        for (let i = 0; i < this.customTimeArray.length; i++) {
          const workCycle = {
            week: [],
            start_time: '',
            end_time: ''
          }
          if (this.customTimeArray[i].week == '') {
            this.$message.error('工作周期' + (i + 1) + '的工作时间不能为空')
            return false
          } else {
            this.customTimeArray[i].week.forEach((item, index) => {
              workCycle.week[index] = index
            })
          }
          if (this.customTimeArray[i].start_time == '') {
            this.$message.error('工作周期' + (i + 1) + '的开始时间不能为空')
            return false
          }
          if (this.customTimeArray[i].end_time == '') {
            this.$message.error('工作周期' + (i + 1) + '的结束时间不能为空')
            return false
          }
          if (this.customTimeArray[i].start_time > this.customTimeArray[i].end_time) {
            this.$message.error('工作周期' + (i + 1) + '的开始时间不得小于结束时间')
            return false
          }
          workCycle.start_time = this.customTimeArray[i].start_time.format('HH:mm')
          workCycle.end_time = this.customTimeArray[i].end_time.format('HH:mm')
          this.ruleForm.work_cycle.push(workCycle)
        }
      }
      // 规则
      for (let i = 0; i < this.ruleArray.length; i++) {
        if (this.ruleArray[i].employee_type == 1) {
          if (this.ruleArray[i].employee == '') {
            this.$message.error('规则' + (i + 1) + '请选择管理员')
            return false
          }
        }
      }
      this.ruleForm.rule = this.ruleArray
      // 表单
      storeApi(this.ruleForm).then((res) => {
        this.$message.success('创建成功')
        this.$router.push({ path: '/roomQuality/index' })
      })
    },
    //  设置其他的工作时间
    addCycleEvent () {
      var array = {
        week: [],
        start_time: '',
        end_time: ''
      }
      array.week = this.customTimeValue
      this.customTimeArray.push(array)
    },
    // 删除自定义工作时间
    customDelEvent (index) {
      this.customTimeArray.splice(index, 1)
    },
    // 删除规则
    delRuleEvent (index) {
      this.ruleArray.splice(index, 1)
    },
    //  添加新的规则
    addRuleEvent () {
      if (this.ruleArray.length < 8) {
        var array = {
          num: 30,
          time_type: 1,
          employee_type: 1,
          employee: [],
          showEmployee: []
        }
        this.ruleArray.push(array)
      }
    }
  }
}
</script>
<style lang="less">
.addGroup{
  display: flex;
}
.addTips{
  margin-left: 12px;
  display: flex;
  align-items: center;
  padding: 0 16px;
  background: #f0f8ff;
  border-radius: 2px;
  height: 32px;
  width: 540px;
  color: rgba(0,0,0,.65);
  font-size: 13px;
}
.newsArchive{
  color: #1890ff;
  cursor: pointer;
}
.chosenGroup{
  display: flex;
  flex-wrap: wrap;
}
.chosenElement{
  min-width: 150px;
  padding: 4px 5px 8px 8px;
  border-radius: 2px;
  border: 1px solid #e8e8e8;
  background: #fff;
  height: 50px;
  display: flex;
  margin-top: 10px;
  margin-left: 10px;
}
.chosenElement:first-child{
  margin-left: 0;
}
.chosenRight{
  margin-left: 5px;
  line-height: 20px;
  .name{
    color: rgba(0,0,0,.65);
    font-size: 10px;
  }
  .num{
    color: rgba(0,0,0,.45);
    font-size: 12px;
  }
}
.notallyWith{
  position: absolute;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.03);
  top: 0;
  left: 0;
}
.shutDown{
  position: absolute;
  width: 18px;
  height: 18px;
  color: #fff;
  background: #878787;
  border-radius: 50%;
  top: -7px;
  right: -8px;
  cursor: pointer;
  font-size: 10px;
  text-align: center;
  line-height: 17px;
  z-index: 10;
}
.chosenElement img{
  width: 30px;
  height: 30px;
  margin-top: 7px;
}
.customizeTips{
  width: 686px;
  height: 72px;
  background: #f7fcff;
  border-radius: 2px;
  border: 1px solid #acd7ff;
  padding-left: 20px;
  padding-right: 20px;
  margin-top: 12px;
  display: flex;
}
.customizeTips div{
  font-size: 12px;
}
.tipsRule{
  margin-left: 8px;
  line-height: 24px;
  margin-top:8px;
}
.customizeContent{
  width: 686px;
  height: 112px;
  background: #fbfbfb;
  border-radius: 2px;
  border: 1px solid #eee;
  margin-top: 16px;
  padding: 16px;
  margin-bottom: 16px;
  position: relative;
}
.customDate{
  display: flex;
}
.cycleOption{
  width: 380px;
  position: relative;
}
.downIcon{
  position: absolute;
  right: 6px;
  color: #C6C6C6;
}
.cycleOption .ant-select-selection--multiple .ant-select-selection__choice{
  padding: 0 0 0 5px;
  width: 46px;
}
.cycleOption .ant-select-selection--multiple .ant-select-selection__choice__remove{
  right: 1px;
}
.cycleOption .ant-select-selection--multiple .ant-select-selection__choice__content{
  font-size: 10px;
}
.customDelBtn{
  position: absolute;
  right: -35px;
  bottom: -10px;
  font-size: 18px;
}
.customTime{
  display: flex;
}
.addCycleBtn{
  float: left;
  font-size: 14px;
  font-weight: 400;
  color: #1890ff;
  line-height: 18px;
  margin-top: 16px;
  cursor: pointer;
}
.addRuleRow{
  background: #fbfbfb;
  border-radius: 2px;
  border: 1px solid #eee;
  min-height: 150px;
  padding-bottom: 45px;
}
.delRuleBtn{
  cursor: pointer;
}
.addRuleCont{
  padding: 16px 20px;
  margin-bottom: -8px;
  display: flex;
  flex-direction: column;
}
.addRuleContRow{
  margin-top: 15px;
  padding-bottom: 20px;
  border-bottom: 1px dashed #e9e9e9;
}
.addRuleContRow:first-child{
  margin-top: 0;
}
.choiceRow{
  display: flex;
  margin-top: 10px;
  line-height: 30px;
  position: relative;
}
.choice_admin{
  width: 200px;
  position: absolute;
  margin-left: 20px;
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
}
.operationTips{
  color: #b2b2b2;
}
.addRuleBtn{
  float: left;
  color: #1890ff;
  line-height: 40px;
  margin-left: 10px;
  cursor: pointer;
}
.addRuleBtn2{
  float: left;
  line-height: 40px;
  margin-left: 10px;
}
.addLimit{
  color: rgba(0,0,0,.45);
  font-size: 13px;
  margin-left: 3px;
}
.whiteTips{
  color: rgba(0,0,0,.45);
  font-size: 13px
}
.addkeyRow{
  margin-top: 10px;
}
.addkeyRow .ant-tag{
  line-height: 30px;
}
.el-tag + .el-tag {
  margin-left: 10px;
}
.button-new-tag {
  margin-right: 10px;
  height: 32px;
  line-height: 30px;
  padding-top: 0;
  padding-bottom: 0;
}
.addBtnIcon{
  margin-right: 5px;
}
.input-new-tag {
  width: 114px;
  margin-right: 10px;
  vertical-align: bottom;
  float: left;
  margin-top: 4px;
}
.establishBtn{
  margin-top: 10px;
  height: 40px;
  width: 142px;
}
</style>
