<template>
  <div class="content">
    <a-row>
      <a-spin size="large" :spinning="loadShow">
        <a-col :span="3">
          <div class="contLeft">
            <div class="addPlan" @click="addProgram">
              <span class="iconStyle"><a-icon type="plus" /></span>添加方案
            </div>
            <div
              class="schemeOptions"
              v-for="(item,index) in programArray"
              :key="index"
              :class="[index == programIndex ?'activeProgram':'']"
              @click="tabProgram(index,item.id)">{{ item.name }}</div>
          </div>
        </a-col>
        <a-col :span="21">
          <div class="contRight">
            <a-card class="contRightCard">
              <div class="contRgtTop">
                <div class="cr_top_left">选择适用客户群：</div>
                <div class="cr_top_right" v-if="checkdetails">
                  <div class="leftSwitch">
                    <span v-if="onOffSwitch">已启用</span>
                    <span v-else>已禁用</span>
                    <a-switch
                      v-model="onOffSwitch"
                      @change="enablePlan"
                      size="small"
                    />
                  </div>
                  <a-button class="rightBtn" @click="delPlanBtn" v-if="programIndex!=0">删除</a-button>
                  <a-button type="primary" class="rightBtn" @click="modifyBtn">修改</a-button>
                </div>
              </div>
              <div class="applicableClient">
                <div
                  class="selectGroup"
                  v-if="!checkdetails"
                  @click="$refs.selectGroup.show()"
                >
                  <a-icon type="plus" />选择群聊</div>
                <selectGroup ref="selectGroup" @change="acceptGroupData" />
                <div class="chosenGroup">
                  <div class="chosenElement" v-for="(item,index) in formAskData.rooms" :key="index">
                    <img src="../../assets/avatar-room-default.svg" alt="">
                    <div class="chosenRight">
                      <div class="group_name">{{ item.name }}</div>
                      <div>{{ item.contact_num }} / {{ item.roomMax }}</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="contRgtTips">
                <div class="cr_top_left">设置提醒行为：</div>
              </div>
              <a-card class="behaviorTips">
                <div class="behaviorTitle">行为提醒</div>
                <!--                详情多选框-->
                <div class="selectCheckRow">
                  <a-checkbox v-model="is_qrcode" :disabled="checkdetails">发送带二维码图片</a-checkbox>
                  <a-checkbox v-model="is_link" :disabled="checkdetails" >发送链接分享</a-checkbox>
                  <a-checkbox v-model="is_miniprogram" :disabled="checkdetails">发送小程序</a-checkbox>
                  <a-checkbox v-model="is_card" :disabled="checkdetails">发送名片</a-checkbox>
                  <a-checkbox v-model="is_keyword" :disabled="checkdetails">聊天消息中包含以下关键词</a-checkbox>
                </div>
                <div class="keyWordRow" v-if="is_keyword">
                  <a-button @click="addToWordBtn" class="addToWord" v-if="!checkdetails"><a-icon type="plus" />添加</a-button>
                  <div class="addToWord btnStyle" v-else><a-icon type="plus" />添加</div>
                  <a-input
                    v-if="showKeyInput"
                    placeholder="输入后回车"
                    @pressEnter="addToWordInput"
                    style="width: 120px;margin-left: 10px;height: 28px;"
                    :autoFocus="true"
                    @blur="closeInput"
                    v-model="keyWordValue"
                  />
                  <div class="tagStyle" v-for="(item,index) in formAskData.keyword" :key="index">
                    {{ item }}
                    <span class="shufIcon" v-if="!checkdetails" @click="delSpanBtn(index)"><a-icon type="close" /></span>
                  </div>
                </div>
              </a-card>
              <a-button class="saveSetBtn" @click="cancelBtn" v-if="!checkdetails">取消</a-button>
              <a-button type="primary" class="saveSetBtn" @click="saveSetup" v-if="!checkdetails">保存设置</a-button>
            </a-card>
          </div>
        </a-col>
      </a-spin>
    </a-row>
  </div>
</template>
<script>
import { storeApi, indexApi, infoApi, updateApi, statusApi, destroyApi } from '@/api/roomRemind'
import selectGroup from '@/components/Select/group'
export default {
  components: { selectGroup },
  data () {
    return {
      onOffSwitch: true,
      // 显示加载
      loadShow: false,
      // 复选框控制
      // 发送带二维码图片
      is_qrcode: false,
      // 发送链接
      is_link: false,
      // 发送小程序
      is_miniprogram: false,
      // 发送名片
      is_card: false,
      // 关键字
      is_keyword: false,
      // 显示关键字输入框
      showKeyInput: false,
      // 获取关键字
      keyWordValue: '',
      formAskData: {
        name: '',
        // 群聊
        rooms: [],
        // 关键字数组
        keyword: []
      },
      programArray: [],
      programIndex: 0,
      //  是否是查看详情
      checkdetails: true,
      // 判断创建事件
      newBuild: false
    }
  },
  created () {
    this.getProjectData()
  },
  methods: {
    // 接收组件信息
    acceptGroupData (e) {
      this.formAskData.rooms = []
      this.formAskData.rooms = e
    },
    // 获取方案数据
    getProjectData () {
      indexApi().then((res) => {
        this.programArray = []
        res.data.forEach((item, index) => {
          const planCont = {
            id: '',
            name: ''
          }
          planCont.id = item.id
          planCont.name = item.name
          this.programArray.push(planCont)
        })
        this.getPlanData(this.programArray[0].id)
      })
    },
    // 获取方案详情
    getPlanData (id) {
      infoApi({ id }).then((res) => {
        this.formAskData.name = res.data.name
        this.formAskData.rooms = res.data.rooms
        if (res.data.keyword) {
          this.formAskData.keyword = res.data.keyword
        } else {
          this.formAskData.keyword = []
        }
        this.showActionTips(res.data)
      })
    },
    //  添加方案
    addProgram () {
      if (!this.newBuild) {
        const planNews = {
          name: '方案' + (this.programArray.length)
        }
        this.programArray.forEach((item, index) => {
          if (planNews.name == item.name) {
            planNews.name = '方案' + (this.programArray.length + 1)
          }
        })
        this.programArray.push(planNews)
        this.programIndex = this.programArray.length - 1
        this.addToNews()
      } else {
        this.$message.error('请保存操作')
        return false
      }
    },
    // 新建方案处理数据
    addToNews () {
      this.onOffSwitch = true
      this.checkdetails = false
      this.formAskData.name = ''
      this.formAskData.rooms = []
      this.formAskData.keyword = []
      this.is_qrcode = false
      // 发送链接
      this.is_link = false
      // 发送小程序
      this.is_miniprogram = false
      // 发送名片
      this.is_card = false
      // 关键字
      this.is_keyword = false
      //  判断创建事件
      this.newBuild = true
    },
    // 取消
    cancelBtn () {
      if (this.newBuild) {
        this.programArray.splice(this.programIndex, 1)
        this.programIndex = 0
        this.checkdetails = true
        this.newBuild = false
        this.getPlanData(this.programArray[this.programIndex].id)
      } else {
        if (!this.checkdetails) {
          this.checkdetails = true
        }
      }
    },
    //  点击修改按钮
    modifyBtn () {
      this.checkdetails = false
    },
    // 删除按钮
    delPlanBtn () {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否删除',
        okText: '删除',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          destroyApi({
            id: that.programArray[that.programIndex].id
          }).then((res) => {
            that.programArray.splice(that.programIndex, 1)
            that.$message.success('删除成功')
            that.programIndex = 0
            that.getPlanData(that.programArray[that.programIndex].id)
          })
        }
      })
    },
    //  启用方案
    enablePlan () {
      if (this.onOffSwitch) {
        this.formAskData.status = 1
      } else {
        this.formAskData.status = 0
      }
      statusApi({
        id: this.programArray[this.programIndex].id,
        status: this.formAskData.status
      }).then((res) => {})
    },
    // 切换方案
    tabProgram (index, id) {
      this.newBuild = false
      this.checkdetails = true
      this.loadShow = true
      this.programIndex = index
      indexApi().then((res) => {
        this.programArray = []
        res.data.forEach((item, index) => {
          const planCont = {
            id: '',
            name: ''
          }
          planCont.id = item.id
          planCont.name = item.name
          this.programArray.push(planCont)
        })
      })
      setTimeout(() => {
        this.getPlanData(id)
        this.loadShow = false
      }, 500)
    },
    // 保存设置
    saveSetup () {
      //  处理提醒行为数据
      this.handleActionData()
      if (this.formAskData.rooms == '') {
        this.$message.error('未选择群聊')
        return false
      }
      if (this.formAskData.is_keyword == 1) {
        if (this.formAskData.keyword == '') {
          this.$message.error('未选择消息关键词')
          return false
        }
      }
      // 新增
      if (this.newBuild && !this.checkdetails) {
        // 方案名称
        this.formAskData.name = this.programArray[this.programIndex].name
        storeApi(this.formAskData).then((res) => {
          this.$message.success('创建成功')
          this.newBuild = false
          this.checkdetails = true
        })
      } else {
        // 修改
        if (!this.checkdetails) {
          this.formAskData.id = this.programArray[this.programIndex].id
          updateApi(this.formAskData).then((res) => {
            this.$message.success('修改成功')
            this.checkdetails = true
          })
        }
      }
    },
    // 处理开关数据格式
    handleActionData () {
      if (this.is_qrcode) {
        this.formAskData.is_qrcode = 1
      } else {
        this.formAskData.is_qrcode = 0
      }
      if (this.is_link) {
        this.formAskData.is_link = 1
      } else {
        this.formAskData.is_link = 0
      }
      if (this.is_miniprogram) {
        this.formAskData.is_miniprogram = 1
      } else {
        this.formAskData.is_miniprogram = 0
      }
      if (this.is_card) {
        this.formAskData.is_card = 1
      } else {
        this.formAskData.is_card = 0
      }
      if (this.is_keyword) {
        this.formAskData.is_keyword = 1
      } else {
        this.formAskData.is_keyword = 0
      }
    },
    // 展示获取的行为详情数据
    showActionTips (data) {
      if (data.status) {
        this.onOffSwitch = true
      } else {
        this.onOffSwitch = false
      }
      if (data.isQrcode) {
        this.is_qrcode = true
      } else {
        this.is_qrcode = false
      }
      if (data.isLink) {
        this.is_link = true
      } else {
        this.is_link = false
      }
      if (data.isMiniprogram) {
        this.is_miniprogram = true
      } else {
        this.is_miniprogram = false
      }
      if (data.isCard) {
        this.is_card = true
      } else {
        this.is_card = false
      }
      if (data.isKeyword) {
        this.is_keyword = true
      } else {
        this.is_keyword = false
      }
    },
    // 显示关键字输入框
    addToWordBtn () {
      this.showKeyInput = true
    },
    // 删除新建的标签
    delSpanBtn (index) {
      this.formAskData.keyword.splice(index, 1)
    },
    // 按下回车添加标签
    addToWordInput () {
      if (this.keyWordValue) {
        this.formAskData.keyword.push(this.keyWordValue)
      }
      this.showKeyInput = false
      this.keyWordValue = ''
    },
    // 关闭输入框
    closeInput () {
      this.showKeyInput = false
      this.keyWordValue = ''
    }
  }
}
</script>
<style>
.contLeft{
  width: 100%;
  height: 100%;
  background: #fff;
  border: 1px solid #e8e8e8;
  min-height: 500px;
}
.keyWordRow{
  margin-top: 25px;
  display: flex;
  flex-wrap: wrap;
}
.addToWord{
  height: 28px;
  line-height: 26px;
  width: 70px;
  text-align: center;
}
.btnStyle{
  border: 1px solid #D9D9D9;
}
.addToWord i{
  margin-right: -7px;
}
.btnStyle i{
  margin-right: 0;
}
.tagStyle{
  position: relative;
  border: 1px solid #e8e8eb;
  background:#f1f1f1;
  padding: 2px 15px;
  margin-left: 10px;
}
.shufIcon{
  position: absolute;
  width: 15px;
  height: 15px;
  line-height: 12px;
  top: -7px;
  right: -7px;
  color: #fff;
  font-size: 9px;
  text-align: center;
  border-radius: 50%;
  background: rgba(0,0,0,.5);
  cursor: pointer;
}
.addPlan{
  width: 100%;
  height: 47px;
  line-height: 47px;
  text-align: center;
  border-bottom: 1px dashed #e8e8e8;
  cursor: pointer;
  position: relative;
}
.iconStyle{
  display: inline-block;
  width: 15px;
  height: 15px;
  background: #9BAEBF;
  font-size: 9px;
  color: #CDD7DF;
  line-height: 12px;
  font-width: bold;
}
.schemeOptions{
  font-size: 14px;
  padding: 12px 41px 12px 24px;
  color: #595959;
  line-height: 20px;
  cursor: pointer;
}
.schemeOptions:hover{
  background: #EFFAFF;
}
.activeProgram{
  border-left: 2px solid #1584FF;
  background: #EFFAFF;
}
.contRight{
  margin-left: 10px;
}
.contRightCard{
  min-height: 500px;
}
.contRgtTop{
  display: flex;
  width: 100%;
  justify-content:space-between;
}
.cr_top_left{
  border-left: 3px solid #1584FF;
  padding-left: 10px;
  font-size: 14px;
  font-weight: 500;
  color: #222;
  height: 20px;
}
.cr_top_right{
  display: flex;
}
.leftSwitch span{
  margin-right: 8px;
  font-size: 12px;
}
.rightBtn{
  margin-left: 12px;
  margin-top: -5px;
}
.applicableClient{
  display: flex;
  flex-wrap: wrap;
}
.selectGroup{
  width: 144px;
  height: 49px;
  text-align: center;
  line-height: 49px;
  box-sizing: border-box;
  border-radius: 2px;
  border: 1px solid #e8e8eb;
  margin-right: 24px;
  margin-top: 10px;
  align-items: center;
  cursor: pointer;
}
.selectGroup i{
  margin-right: 2px;
}
.chosenGroup{
  display: flex;
  flex-wrap: wrap;
}
.chosenElement{
  min-width: 144px;
  height: 49px;
  border-radius: 2px;
  border: 1px solid #e8e8eb;
  margin-right: 24px;
  margin-top: 10px;
  display: flex;
  padding-left: 7px;
  padding-top: 1px;
  padding-right: 7px;
  position: relative;
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
}
.chosenElement img{
  width: 30px;
  height: 30px;
  margin-top: 7px;
}
.chosenRight{
  margin-left: 5px;
  font-size: 12px;
  margin-top: 5px;
}
.contRgtTips{
  margin-top: 40px;
}
.selectLable{
  display: flex;
}
.behaviorTips{
  margin-top: 20px;
}
.selectCheckRow{
  /*display: flex;*/
}
.behaviorTips .ant-checkbox-wrapper{
  width: 30%;
  margin-left: 10px;
  margin-top: 16px;
}
.behaviorTitle{
  font-size: 14px;
  color: #666;
  line-height: 20px;
}
.saveSetBtn{
  margin-top: 16px;
  margin-left: 16px;
  width: 100px;
}
.saveSetBtn:nth-of-type(1){
  margin-left: 0;
}
</style>
