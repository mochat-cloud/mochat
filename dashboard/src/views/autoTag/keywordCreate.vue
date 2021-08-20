<template>
  <div class="rule-box">
    <a-card>
      <div class="create-rule">
        <div class="title">创建自动打卡标签规则</div>
        <div class="content mt22">
          <div class="tag-mode">
            <div class="mode elastic">
              <span>打卡标签方式：</span>
              <div class="block">关键词打标签</div>
            </div>
            <p class="tip-gray ml98">根据客户聊天行为，设定客户在聊天过程中提到的关键词，自动将提到关键词的客户打标签</p>
          </div>
          <!--          规则名称-->
          <div class="rule-name elastic mt20">
            <span>规则名称：</span>
            <div><a-input placeholder="请填写规则名称，仅内部可见" style="width: 400px" v-model="formAskData.name"/></div>
          </div>
          <!--          生效员工-->
          <div class="effect">
            <div class="take-effect elastic mt20">
              <span>生效员工：</span>
              <a-button style="width: 180px" @click="$refs.selectMember.setSelect(showSelectStaff)">选择成员</a-button>
            </div>
            <div class="memberRow">
              <div class="tags" v-for="(item,index) in showSelectStaff" :key="index">
                {{ item.name }}
                <span class="closeIcon" @click="delIconSpan(index)"><a-icon type="close" /></span>
              </div>
            </div>
            <p class="tip-gray ml70">仅针对开通了消息存档的员工生效</p>
            <selectMember ref="selectMember" @change="effectStaff"/>
          </div>
        </div>
      </div>
      <!--        设置关键词-->
      <div class="set-keywords mt50">
        <div class="title">设置关键词</div>
        <div class="content mt22">
          <div class="mode elastic">
            <span>模糊匹配
              <a-popover>
                <template slot="content">
                  客户与员工聊天中对话内容只要包含某个关键词，<br>
                  客户就会被自动打上标签
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
              ：
            </span>
            <div class="add-key">
              <a-button @click="addkeyWordType(0)"><a-icon type="plus" />添加关键词</a-button>
            </div>
            <div class="keyTermsRow">
              <a-tag
                closable
                type="info"
                v-for="(item,index) in formAskData.fuzzy_match_keyword"
                :key="index"
                @close="delkeyTerms(index,0)"
              >{{ item }}</a-tag>
            </div>
          </div>
          <div class="mode elastic mt24">
            <span>
              精准匹配
              <a-popover>
                <template slot="content">
                  客户与员工聊天中对话内容只提到某个关键词，<br>
                  客户才会被自动打上标签
                </template>
                <a-icon type="question-circle"/>
              </a-popover>
              ：
            </span>
            <div class="add-key">
              <a-button @click="addkeyWordType(1)"><a-icon type="plus" />添加关键词</a-button>
            </div>
            <div class="keyTermsRow">
              <a-tag
                closable
                type="info"
                v-for="(item,index) in formAskData.exact_match_keyword"
                :key="index"
                @close="delkeyTerms(index,1)"
              >{{ item }}</a-tag>
            </div>
          </div>
        </div>
        <!--        添加关键词弹窗-->
        <a-modal
          v-model="addwordKeyPopup"
          title="添加匹配关键词"
          @ok="addwordKey"
          :maskClosable="false"
        >
          <div class="keywordRow" v-for="(item,index) in addkeyArray" :key="index">
            <div class="keywordIndex">关键词{{ index+1 }}：</div>
            <a-input placeholder="请输入关键词" v-model="addkeyArray[index]" />
            <span
              class="keywordDelIcon"
              v-if="addkeyArray.length!=1"
              @click="delCruxWord(index)"
            >
              <a-icon type="minus-circle" /></span>
          </div>
          <div class="addRowEvent">
            <span class="addkeyWordBtn" @click="addkeyData"><a-icon type="plus-circle" />添加关键词</span>
          </div>
        </a-modal>
      </div>
      <!--      添加规则-->
      <div class="automatic mt50">
        <div class="title">自动打标签
          <span class="tips">触发关键词次数为触发模糊匹配和精准匹配关键词总和</span>
        </div>
        <div class="content mt30">
          <div class="task">
            <div class="task-padding">
              <div class="task-content" v-for="(item,index) in tag_rule" :key="index">
                规则{{ index+1 }}：客户每
                <div class="task-content ml4 mr4">
                  <a-select v-model="item.time_type">
                    <a-select-option :value="1">天</a-select-option>
                    <a-select-option :value="2">周</a-select-option>
                    <a-select-option :value="3">月</a-select-option>
                  </a-select>
                </div>
                触发关键词
                <div class="ml4 mr4">
                  <a-input-number v-model="item.trigger_count" :min="1" :max="10" style="width: 52px;" />
                </div>
                次自动打上
                <div @click="showModal(index)" class="choiceAdmin">
                  <span class="operationTips" v-if="item.clientTagArr.length==0">请选择标签</span>
                  <a-tag v-for="(obj,idx) in item.clientTagArr" :key="idx">
                    {{ obj.name }}
                    <a-icon type="close" @click.stop="delTagsArr(index,idx)" />
                  </a-tag>
                </div>
                标签
                <span class="delete-icon" style="margin-left: 7px;cursor: pointer;" @click="delTag(index)" v-if="tag_rule.length>1">
                  <a-icon type="minus-circle"/>
                </span>
              </div>
            </div>
            <div class="add-task">
              <a @click="addRuleName" v-if="tag_rule.length<10"><a-icon type="plus-circle" class="icon"/>添加规则</a>
              <span v-else><a-icon type="plus-circle" class="icon"/>添加规则</span>
              <span class="tip-gray">（添加的多条规则可同时生效，最多设置10个规则）</span>
            </div>
          </div>
          <!--   弹窗   abc        -->
          <addlableIndex @choiceTagsArr="acceptArray" ref="childRef"/>
        </div>
      </div>
      <div class="create-btn mt60">
        <a-button type="primary" @click="setUpRule">创建规则</a-button>
      </div>
    </a-card>
  </div>
</template>

<script>
// eslint-disable-next-line no-unused-vars
import { storeApi } from '@/api/autoTag'
import addlableIndex from '@/components/addlabel/index'
import selectMember from '@/components/Select/member'
export default {
  components: { addlableIndex, selectMember },
  data () {
    return {
      // 表单请求数据
      formAskData: {
        // 打标签方式
        type: 1,
        // 规则名称
        name: '',
        // 生效成员
        employees: [],
        // 模糊匹配关键词
        fuzzy_match_keyword: [],
        // 精准匹配关键词
        exact_match_keyword: [],
        // 标签规则
        tag_rule: [
          {
            // 时间类型
            time_type: '',
            // 触发次数
            trigger_count: ''
          }
        ]
      },
      //  关键词类型
      keyWordType: 0,
      //  显示关键词弹窗
      addwordKeyPopup: false,
      //  新建关键词
      addkeyArray: [''],
      // 自动打标签规则
      tag_rule: [
        {
          // 时间类型
          time_type: 1,
          // 触发次数
          trigger_count: 1,
          // 请求标签
          tags: [],
          //  客户选中标签
          clientTagArr: []
        }
      ],
      // 弹窗
      visible: false,
      //  选中的输入框
      selectInputIndex: 0,
      //  删除所选输入框中的标签
      delSelectlable: 0,
      // 已选中的标签列表
      selectedList: [],
      // 展示选中的员工
      showSelectStaff: []
    }
  },
  methods: {
    // 删除生效成员
    delIconSpan (index) {
      this.showSelectStaff.splice(index, 1)
      this.formAskData.employees.splice(index, 1)
    },
    // 针对插件
    // 获取员工选中的员工数据
    effectStaff (e) {
      this.showSelectStaff = e
      e.forEach((item, index) => {
        this.formAskData.employees[index] = item.wxUserId
      })
    },
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
    handchange (index) {
      this.delSelectlable = index
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
          this.tag_rule[this.selectInputIndex].tags.push(tagsArray)
          pagArray[index] = item
        })
        this.tag_rule[this.selectInputIndex].clientTagArr = pagArray
      } else {
        this.tag_rule[this.selectInputIndex].clientTagArr = []
        this.tag_rule[this.selectInputIndex].tags = []
      }
    },
    // 页面方法
    // 提交规则
    setUpRule () {
      if (this.formAskData.name == '') {
        this.$message.error('规则名称不能为空')
        return false
      }
      if (this.formAskData.employees == '') {
        this.$message.error('请添加规则生效成员')
        return false
      }
      if (this.formAskData.fuzzy_match_keyword == '' && this.formAskData.exact_match_keyword == '') {
        this.$message.error('请添加关键字')
        return false
      }
      for (var i = 0; i < this.tag_rule.length; i++) {
        if (this.tag_rule[i].tags == '') {
          this.$message.error('规则' + (i + 1) + '标签不能为空')
          return false
        }
      }
      this.formAskData.tag_rule = this.tag_rule
      storeApi(this.formAskData).then((res) => {
        this.$message.success('创建成功')
        this.$router.push({ path: '/autoTag/ruleTagging' })
      })
    },
    // 删除规则
    delTag (index) {
      this.tag_rule.splice(index, 1)
    },
    // 添加规则
    addRuleName () {
      const ruleTemplate = {
        // 时间类型
        time_type: 1,
        // 触发次数
        trigger_count: 1,
        // 请求标签
        tags: [],
        //  客户选中标签
        clientTagArr: []
      }
      this.tag_rule.push(ruleTemplate)
    },
    // 删除关键词
    delkeyTerms (index, type) {
      if (type == 0) {
        this.formAskData.fuzzy_match_keyword.splice(index, 1)
      } else if (type == 1) {
        this.formAskData.exact_match_keyword.splice(index, 1)
      }
    },
    // 添加关键词的类型
    addkeyWordType (type) {
      this.addwordKeyPopup = true
      this.keyWordType = type
      if (this.keyWordType == 0) {
        if (this.formAskData.fuzzy_match_keyword == '') {
          this.addkeyArray = ['']
        } else {
          this.addkeyArray = []
          this.formAskData.fuzzy_match_keyword.forEach((item, index) => {
            this.addkeyArray[index] = item
          })
        }
      }
      if (this.keyWordType == 1) {
        if (this.formAskData.exact_match_keyword == '') {
          this.addkeyArray = ['']
        } else {
          this.addkeyArray = []
          this.formAskData.exact_match_keyword.forEach((item, index) => {
            this.addkeyArray[index] = item
          })
        }
      }
    },
    // 关键词弹窗确认按钮
    addwordKey () {
      // 表单验证
      for (var i = 0; i < this.addkeyArray.length; i++) {
        // 不为空
        if (this.addkeyArray[i] == '') {
          this.$message.error('关键字' + (i + 1) + '不能为空')
          return false
        }
        //  值不能相同
        const keyRepeat = this.addkeyArray.indexOf(this.addkeyArray[i])
        if (keyRepeat != -1 && keyRepeat != i) {
          this.$message.error('关键字' + (i + 1) + '不能为与其他关键字重复')
          return false
        }
      }
      // 赋值
      if (this.keyWordType == 0) {
        this.addkeyArray.forEach((item, index) => {
          this.formAskData.fuzzy_match_keyword[index] = item
        })
      } else if (this.keyWordType == 1) {
        this.addkeyArray.forEach((item, index) => {
          this.formAskData.exact_match_keyword[index] = item
        })
      }
      this.addwordKeyPopup = false
    },
    //  添加关键词
    addkeyData () {
      const name = ''
      this.addkeyArray.push(name)
    },
    //   删除关键词
    delCruxWord (index) {
      this.addkeyArray.splice(index, 1)
    }
  }
}
</script>

<style lang="less" scoped>
.keyTermsRow{
  margin-left: 10px;
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
.keyTermsRow span{
  background: #f7f7f7;
  border-radius: 2px;
  padding: 5px 10px;
  font-size: 14px;
  line-height: 22px;
  color: rgba(0,0,0,.85);
}
.showElection{
  margin-left: 70px;
  margin-top: 10px;
}
.showElection span{
  background: #f7f7f7;
  border-radius: 2px;
  padding: 5px 10px;
  font-size: 14px;
  line-height: 22px;
  color: rgba(0,0,0,.85);
}
.keywordRow{
  display: flex;
  margin-top: 15px;
}
.keywordIndex{
  width: 82px;
  line-height: 30px;
}
.keywordRow input{
  width: 365px;
}
.keywordDelIcon i{
  font-size: 15px;
  margin-left: 10px;
  margin-top: 10px;
  cursor: pointer;
}
.title {
  font-size: 15px;
  line-height: 21px;
  color: rgba(0, 0, 0, .85);
  border-bottom: 1px solid #e9ebf3;
  padding-bottom: 16px;
  margin-bottom: 16px;
  position: relative;
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
   text-align: center;
 }
}
.tips{
  font-size: 12px;
  margin-left: 6px;
  color: #b8b8b8;
}
.addRowEvent{
  color: #1B91FF;
  margin-left: 80px;
  margin-top: 20px;
}
.addkeyWordBtn{
  cursor: pointer;
}
.addkeyWordBtn i{
  margin-right:5px;
}
.task {
  background-color: #fbfbfb;
  width: 800px;
  border: 1px solid #e2e2e2;
}
.task-content {
  display: flex;
  align-items: center;
  margin-top: 15px;
}
.task-content:first-child{
  margin-top: 0;
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
</style>
