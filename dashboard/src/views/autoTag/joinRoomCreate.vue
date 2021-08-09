<template>
  <div class="rule-box">
    <a-card>
      <div class="set-keywords">
        <div class="title">规则基础信息</div>
        <div class="content mt22">
          <div class="mode elastic">
            <span class="tip-gray">设定规则，当用户加入规则内的群聊，自动给入群客户打标签</span>
          </div>
          <div class="mode elastic mt24">
            <span>规则名称：</span>
            <div class="add-key">
              <a-input placeholder="请填写规则名称，仅内部可见" style="width: 300px" v-model="formAskData.name"/>
            </div>
          </div>
        </div>
      </div>
      <div class="automatic mt50">
        <div class="title">设置打标签规则</div>
        <div class="content mt30">
          <div class="task">
            <div class="task-padding">
              <div class="task-content" v-for="(item,index) in tag_rule" :key="index">
                规则{{ index+1 }}：用户加入群聊
                <div class="task-content ml4 mr4" @click="showGroupPop(index)">
                  <div class="groupchoose">
                    <span class="groupSpan" v-if="item.rooms==''">请选择群聊</span>
                    <div v-else>
                      <a-tag v-for="(obj,idx) in item.rooms" :key="idx">{{ obj.name }}</a-tag>
                    </div>
                  </div>
                </div>
                时，可打上标签
                <div @click="showModal(index)" class="choiceAdmin">
                  <span class="operationTips" v-if="item.clientTagArr.length==0">请选择标签</span>
                  <a-tag v-for="(obj,idx) in item.clientTagArr" :key="idx">
                    {{ obj.name }}
                    <a-icon type="close" @click.stop="delTagsArr(index,idx)" />
                  </a-tag>
                </div>
                并添加评分
                <a-switch size="small" class="ml4" v-model="item.is_grade" />
                <div>
                  <div class="ml6" v-if="item.is_grade">
                    <a-input-number
                      v-model="item.grade"
                      :min="1"
                      style="width: 60px;margin-right: 10px;"
                    />分
                  </div>
                </div>
                <span
                  class="keywordDelIcon"
                  v-if="tag_rule.length!=1"
                  @click="delCruxWord(index)"><a-icon type="minus-circle" /></span>
              </div>
            </div>
            <div class="add-task">
              <a @click="addRule" v-if="tag_rule.length<10"><a-icon type="plus-circle" class="icon"/>添加规则</a>
              <span v-else><a-icon type="plus-circle" class="icon"/>添加规则</span>
              <span class="tip-gray">（添加的多条规则可同时生效，最多设置10个规则）</span>
            </div>
          </div>
        </div>
      </div>
      <!--                选择标签弹窗-->
      <addlableIndex :showPopup="visible" @choiceTagsArr="acceptArray" ref="childRef"/>
      <!--                  选择群聊弹窗-->
      <selectGroup ref="selectGroup" @change="receiveGroup" />
      <div class="create-btn mt60">
        <a-button type="primary" @click="setUprule">创建规则</a-button>
      </div>
    </a-card>
  </div>
</template>

<script>
import { storeApi } from '@/api/autoTag'
import addlableIndex from '@/components/addlabel/index'
import selectGroup from '@/components/Select/group'
export default {
  components: { addlableIndex, selectGroup },
  data () {
    return {
      value: 1,
      // 表单请求数据
      formAskData: {
        // 打标签方式
        type: 2,
        // 规则名称
        name: ''
      },
      // 标签规则
      tag_rule: [
        {
          //  是否开启评分
          is_grade: false,
          grade: 1,
          // 请求群聊
          rooms: [],
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
      // 展示选中的员工
      showSelectStaff: [],
      selectGroupIpt: 0
    }
  },
  methods: {
    // 弹窗1
    showGroupPop (index) {
      this.selectGroupIpt = index
      this.$refs.selectGroup.show()
    },
    // 接收群组
    receiveGroup (e) {
      e.forEach((item, index) => {
        const tagsArray = {
          id: '',
          name: ''
        }
        tagsArray.id = item.id
        tagsArray.name = item.name
        this.tag_rule[this.selectGroupIpt].rooms.push(tagsArray)
      })
    },
    // 显示弹窗  弹窗2
    showModal (index) {
      this.selectInputIndex = index
      this.$refs.childRef.show(this.tag_rule[this.selectInputIndex].clientTagArr)
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
    // 删除标签
    delTagsArr (index, idx) {
      this.tag_rule[index].tags.splice(idx, 1)
      this.tag_rule[index].clientTagArr.splice(idx, 1)
    },
    // 页面方法
    // 创建规则
    setUprule () {
      // 验证名称
      if (this.formAskData.name == '') {
        this.$message.error('规则名称不能为空')
        return false
      }
      for (var i = 0; i < this.tag_rule.length; i++) {
        if (this.tag_rule[i].rooms == '') {
          this.$message.error('请选择用户加入的群聊')
          return false
        }
        if (this.tag_rule[i].tags == '') {
          this.$message.error('请选择要打的标签')
          return false
        }
      }
      this.formAskData.tag_rule = this.tag_rule
      storeApi(this.formAskData).then((res) => {
        this.$message.success('创建成功')
        this.$router.push({ path: '/autoTag/ruleTagging' })
      })
    },
    // 添加规则
    addRule () {
      const ruleTemplate = {
        //  是否开启评分
        is_grade: false,
        grade: 1,
        // 请求群聊
        rooms: [],
        // 请求标签
        tags: [],
        //  客户选中标签
        clientTagArr: []
      }
      this.tag_rule.push(ruleTemplate)
    },
    //   删除规则
    delCruxWord (index) {
      this.tag_rule.splice(index, 1)
    }
  }
}
</script>

<style lang="less" scoped>
.groupchoose .groupSpan{
  color: #bfbfbf;
}
.groupchoose{
  min-height: 32px;
  width: 250px;
  border:1px solid #d9d9d9;
  line-height: 32px;
  cursor: pointer;
  padding-left: 5px;
}
.keywordDelIcon{
  margin-left: 10px;
  font-size: 15px;
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
  margin-top: 17px;
}
.task-content:first-child{
  margin-top: 0px;
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
