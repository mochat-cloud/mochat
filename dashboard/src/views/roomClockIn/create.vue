<template>
  <div class="create">
    <a-card>
      <div class="block">
        <div class="title">创建打卡任务</div>
        <div class="activity-name interval">
          <span class="name required">打卡活动名称：</span>
          <a-input placeholder="请输入打卡名称" v-model="taskForm.name"/>
        </div>
        <div class="activity-description interval">
          <span class="description required">打卡活动说明：</span>
          <a-textarea placeholder="打卡活动说明" :rows="5" v-model="taskForm.description"/>
        </div>
        <div class="punch-type interval">
          <span class="type required">选择打卡类型：</span>
          <div class="radio">
            <a-radio-group name="radioGroup" v-model="taskForm.type">
              <a-radio :value="1">连续打卡</a-radio>
              <a-radio :value="2">累积打卡</a-radio>
            </a-radio-group>
          </div>
        </div>
        <div class="task-setting interval">
          <span class="setting required">打卡任务设置：</span>
          <div class="task">
            <div class="task-padding">
              <div class="task-content" v-for="(item,index) in tagList" :key="index">
                第 {{ index+1 }} 阶段任务：用户<span v-if="taskForm.type==1">连续</span><span v-else>累计</span>打卡
                <a-input-number class="day" v-model="item.count" :min="1" style="width: 72px;" />
                天，即可领取
                <a-input placeholder="输入奖励名称" class="award-name" v-model="item.prize"/>
                <span class="delete-icon" @click="delClock(index)" v-if="index!=0">
                  <a-icon type="minus-circle" />
                </span>
              </div>
            </div>
            <div class="add-task">
              <a @click="addClock">
                <a-icon type="plus-circle" class="icon"/>添加任务</a>
            </div>
          </div>
        </div>
        <div class="punch-type interval">
          <span class="type required">截止日期：</span>
          <div class="radio">
            <a-radio-group name="radioGroup" v-model="taskForm.time_type">
              <a-radio :value="1">
                永久有效
              </a-radio>
              <a-radio :value="2">
                自定义活动时间
              </a-radio>
            </a-radio-group>
            <div class="mt18" v-if="taskForm.time_type === 2">
              <a-range-picker @change="setActivityTime" />
            </div>
          </div>
        </div>
        <div class="qr-code" style="text-align: left;">
          <span class="cus-service required">客服二维码：
            <span class="notes"> ( 用户完成任务后，联系客服领取奖励 )</span>
          </span>
          <!--          二维码-->
          <div class="upload-qc">
            <m-upload :def="false" text="请上传二维码" @change="uploadImg" />
          </div>
        </div>
        <div class="mb40">
          <div class="card-box interval">
            <span>企业名片：</span>
            <div class="switch">
              <a-switch
                size="small"
                v-model="cardShow"
                @click="()=>{
                  if(this.cardShow){
                    this.taskForm.corp_card_status=1
                  }else{
                    this.taskForm.corp_card_status=0
                  }
                }"
              />
            </div>
          </div>
          <div class="information" v-show="cardShow">
            <div class="portrait interval">
              <div class="corporate-avatar">企业头像：</div>
              <div class="upload-two">
                <m-upload :def="false" text="请上传头像" @change="uploadBusinesshead"/>
              </div>
            </div>
            <div class="corporate-name">
              <span class="cr-name interval">企业名称：<a-input placeholder="请输入企业名称" v-model="taskForm.corp_card.name"/></span>
            </div>
            <div class="mb14">
              <div class="profile-title">
                企业简介：
                <div class="switch">
                  <a-switch
                    size="small"
                    default-checked
                    v-model="profileShow"
                    @change="()=>{
                      if(this.profileShow){
                        this.taskForm.corp_card.description_status=1
                      }else{
                        this.taskForm.corp_card.description_status=0
                      }
                    }"
                  />
                </div>
              </div>
            </div>
            <div class="profile" v-if="profileShow">
              <a-textarea placeholder="请输入企业简介" v-model="taskForm.corp_card.description" :rows="5"/>
            </div>
          </div>
        </div>
        <div class="title">客户设置</div>
        <a-alert message="企业微信接口限制，仅支持为已添加企业员工的客户打标签" type="info" show-icon class="tips"/>
        <div class="customer-label interval">
          <span class="customer-tag">客户标签：</span>
          <div class="task-rule">
            <div class="rule-box">
              <div class="rule">规则：</div>
              <div class="punch-page" v-for="(item,index) in clientTagList" :key="index">
                为
                <div class="drop-down">
                  <template v-if=" item.key=='' ">
                    <a-select style="width: 130px;margin-left: 5px;margin-right: 5px;" placeholder="选择用户行为" @change="setUserAction($event,index)">
                      <a-select-option :value="1" :disabled="selectOption">进入打卡页面</a-select-option>
                      <a-select-option :value="2">设置天数</a-select-option>
                    </a-select>
                  </template>
                  <template v-else>
                    <span class="set_userLable_tips" v-if="item.key==1">进入打卡页面</span>
                    <span v-else>打卡 <a-input-number v-model="item.count" :min="1" style="margin-left: 5px;margin-right: 5px;" />天</span>
                  </template>
                </div>
                的客户打上
                <div @click="showModal(index)" class="choiceAdmin">
                  <span class="operationTips" v-if="item.clientTagArr.length==0">请选择标签</span>
                  <a-tag v-for="(obj,idx) in item.clientTagArr" :key="idx">
                    {{ obj.name }}
                    <a-icon type="close" @click.stop="delTagsArr(index,idx)" />
                  </a-tag>
                </div>
                <div>
                  标签
                  <a @click="delTag(index)">
                    <span class="delete-icon">
                      <a-icon type="minus-circle"/>
                    </span>
                  </a>
                </div>
              </div>
              <!--   弹窗           -->
              <addlableIndex @choiceTagsArr="acceptArray" ref="childRef"/>
            </div>
            <div class="add-task">
              <a @click="addClinentTag">
                <a-icon type="plus-circle" class="icon"/>
                添加任务
              </a>
            </div>
          </div>
        </div>
        <div class="create-over">
          <a-button type="primary" @click="establishTask">创建打卡任务</a-button>
        </div>
      </div>
    </a-card>

  </div>
</template>

<script>
import { addActivity } from '@/api/roomClockIn'
import addlableIndex from '@/components/addlabel/index'
export default {
  components: { addlableIndex },
  data () {
    return {
      // 已选中的标签列表
      selectedList: [],
      // 控制 进入打卡页面 选项能否被选中
      selectOption: true,
      // 弹窗
      visible: false,
      // 任务设置
      tagList: [
        {
          count: '',
          prize: ''
        },
        {
          count: '',
          prize: ''
        }
      ],
      // 设置客户标签
      clientTagList: [
        {
          key: 1,
          count: 0,
          tags: [],
          //  客户选中标签
          clientTagArr: []
        },
        {
          key: '',
          count: 1,
          tags: [],
          //  客户选中标签
          clientTagArr: []
        }
      ],
      // 打卡任务表单
      taskForm: {
        // 名称  1
        name: '',
        //  任务说明  1
        description: '',
        // 截止日期     1
        time_type: 1,
        //  类型   1
        type: 1,
        //  开始时间
        start_time: '',
        // 结束时间
        end_time: '',
        //  任务设置   1
        tasks: '',
        // 客户标签
        contact_clock_tags: '',
        corp_card_status: 0,
        // 客户积分
        point: 0,
        // 企业名片
        corp_card: {
          logo: '',
          name: '',
          description: '',
          description_status: 1
        },
        // 客服二维码    1
        employee_qrcode: ''
      },
      value: 0,
      cardShow: false,
      profileShow: true,
      //  选中的输入框
      selectInputIndex: 0,
      //  删除所选输入框中的标签
      delSelectlable: 0
    }
  },
  methods: {
    // 创建打卡任务
    establishTask () {
      this.taskForm.tasks = this.tagList
      this.taskForm.contact_clock_tags = this.clientTagList
      if (!this.taskForm.name) {
        this.$message.warning('打卡名称未填写')
        return false
      }
      if (!this.taskForm.description) {
        this.$message.warning('打卡说明未填写')
        return false
      }
      // 任务设置
      const taskCount = []
      this.taskForm.tasks.forEach((item, index) => {
        taskCount[index] = item.count
      })
      for (let i = 0; i < this.taskForm.tasks.length; i++) {
        if (this.taskForm.tasks[i].count == '') {
          this.$message.warning('打卡天数不能为空')
          return false
        }
        if (this.taskForm.tasks[i].prize == '') {
          this.$message.warning('奖励不能为空')
          return false
        }
        for (let m = 0; m < taskCount.length; m++) {
          // 打卡天数不能一致
          if (this.taskForm.tasks[i].count == taskCount[m]) {
            if (i != m) {
              this.$message.error('阶段' + (i + 1) + '的打卡天数和阶段' + (m + 1) + '打卡天数不能相同')
              return false
            }
          }
          //  打卡天数递增
          if (this.taskForm.tasks[i].count < taskCount[m]) {
            if (i > m) {
              this.$message.error('打卡阶段' + (i + 1) + '天数应大于打卡阶段' + (m + 1) + '天数')
            }
          }
        }
      }
      // 客服二维码
      if (this.taskForm.time_type == 2) {
        if (this.taskForm.time_type == '') {
          this.$message.warning('开始时间不能为空')
          return false
        }
        if (this.taskForm.end_time == '') {
          this.$message.warning('结束时间不能为空')
          return false
        }
      }
      if (this.taskForm.employee_qrcode == '') {
        this.$message.warning('客服二维码不能为空')
        return false
      }
      if (this.taskForm.corp_card_status == 1) {
        if (this.taskForm.corp_card.logo == '') {
          this.$message.warning('企业头像不能为空')
          return false
        }
        if (this.taskForm.corp_card.name == '') {
          this.$message.warning('企业名称不能为空')
          return false
        }
        if (this.taskForm.corp_card.description_status == 1) {
          if (this.taskForm.corp_card.description == '') {
            this.$message.warning('企业简介不能为空')
            return false
          }
        }
      }
      addActivity(this.taskForm).then((res) => {
        this.$message.success('创建成功')
        this.$router.push({ path: '/roomClockIn/index' })
      })
    },
    // 显示弹窗
    showModal (index) {
      this.selectInputIndex = index
      this.$refs.childRef.show(this.clientTagList[this.selectInputIndex].clientTagArr)
    },
    // 删除标签
    delTagsArr (index, idx) {
      this.clientTagList[index].tags.splice(idx, 1)
      this.clientTagList[index].clientTagArr.splice(idx, 1)
    },
    // 接收子组件传来的值
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
          this.clientTagList[this.selectInputIndex].tags.push(tagsArray)
          pagArray[index] = item
        })
        this.clientTagList[this.selectInputIndex].clientTagArr = pagArray
      } else {
        this.clientTagList[this.selectInputIndex].clientTagArr = []
        this.clientTagList[this.selectInputIndex].tags = []
      }
    },
    // 设置客户行为属性标签
    // 选择用户行为
    setUserAction (e, index) {
      if (e == 1) {
        this.selectOption = true
      }
      this.clientTagList[index].key = e
    },
    // 获取企业头像
    uploadBusinesshead (e) {
      this.taskForm.corp_card.logo = e
    },
    // 上传图片
    uploadImg (e) {
      this.taskForm.employee_qrcode = e
    },
    // 设置活动时间
    setActivityTime (date, dateString) {
      this.taskForm.start_time = dateString[0]
      this.taskForm.end_time = dateString[1]
    },
    delTag (index) {
      if (this.clientTagList.length === 1) {
        this.$message.error('不能删除最后一个')
        return false
      }
      if (this.clientTagList[index].key == 1) {
        this.selectOption = false
      }
      this.clientTagList.splice(index, 1)
    },
    // 添加客户行为标签
    addClinentTag () {
      var newAddCont = {
        key: '',
        count: 0,
        tags: [],
        clientTagArr: []
      }
      this.clientTagList.forEach((item) => {
        if (item.count == 0) {
          newAddCont.count = 1
        }
      })
      this.clientTagList.push(newAddCont)
    },
    addTag () {
      if (this.tagList.length === 3) {
        this.$message.error('不能添加3个以上')
        return false
      }
      this.tagList.push({
        type: '0',
        tasg: []
      })
    },
    delClock (i) {
      this.tagList.splice(i, 1)
    },
    addClock () {
      if (this.tagList.length === 5) {
        this.$message.error('不能添加五个以上')
        return false
      }

      this.tagList.push({
        type: '0',
        tasg: []
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
.interval {
  display: flex;
  margin-bottom: 23px;
}

.name,
.description,
.type,
.setting,
.cus-service {
  position: relative;
}
.drop-down .set_userLable_tips{
  font-weight: bold;
  margin-left: 5px;
  margin-right: 5px;
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

.ant-input {
  width: 400px;
}

.day {
  width: 50px;
  margin-right: 5px;
  margin-left: 5px;
}

.task {
  background-color: #fbfbfb;
  width: 800px;
  border: 1px solid #e2e2e2;
}

.task-content {
  padding: 12px;
}

.task-padding {
  padding: 15px;
  border-bottom: 1px dashed #e2e2e2;
}

.award-name {
  width: 200px;
}

.upload-qc {
  padding-left: 80px;
  padding-top: 10px;
  display: flex;
  margin-bottom: 16px;
}

.upload-two {
  display: flex;
  margin-bottom: 16px;
}

.add-task {
  padding-left: 26px;
  padding-top: 8px;
  padding-bottom: 8px;

  .icon {
    padding-right: 3px;
  }
}

.notes {
  color: #b1b1b1;
}

.avatar-uploader > .ant-upload {
  width: 128px;
  height: 128px;
}

.ant-upload-select-picture-card i {
  font-size: 32px;
  color: #999;
}

.ant-upload-select-picture-card .ant-upload-text {
  margin-top: 8px;
  color: #666;
}

.ant-upload-picture-card-wrapper {
  width: 135px;
  padding-left: 4px;
}

.proposal {
  position: relative;
  bottom: -86px;
  color: #999;
}

.information {
  width: 500px;
  background-color: #fbf9f9;
  margin-left: 79px;
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

.cr-name {
  input {
    width: 300px;
  }
}

.profile-title {
  display: flex;
}

.tips {
  width: 500px;
  font-size: 12px;
  margin-bottom: 15px;
}

#components-popover-demo-placement {
  width: 70px;
  text-align: center;
  padding: 0;
  margin-right: 8px;
  margin-bottom: 8px;
}
.btn-color {
  background-color: #ffffff;
  color: #979797;
  border: 1px solid #bdbdbd;
  margin-left: 5px;
  margin-right: 5px;
}
.punch-page {
  display: flex;
  align-content: center;
  padding-left: 18px;
  padding-top: 8px;
  padding-bottom: 14px;
  line-height: 32px;
}
.task-rule {
  background-color: #fbfbfb;
  width: 800px;
  border: 1px solid #e2e2e2;
  margin-left: 10px;
}
.rule {
  padding-top: 14px;
  padding-bottom: 4px;
  padding-left: 18px;
}
.rule-box {
  border-bottom: 1px dashed #e2e2e2;
}
.delete-icon {
  margin-left: 4px;
  color: #8d8d8d;
  cursor: pointer;
}
.center {
  background-color: #fbfbfb;
  width: 800px;
  border: 1px solid #e2e2e2;
}
.add-points {
  padding-left: 18px;
  padding-bottom: 12px;
}
.create-over {
  button {
    margin-left: 80px;
    height: 40px;
  }
}
</style>
