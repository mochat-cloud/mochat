<template>
  <div class="batch_page" :style="{minHeight:(clientHeight-20)+'px'}">
    <div ref="element">
      <div class="batch_tip">
        <div>管理员 {{ employeeName }} 给你分配了 {{ list.length }} 个好友,快去复制电话号码添加客户吧，管理员在后台可查看添加状态哦~</div>
      </div>
      <div class="table_tip">
        <div class="client_num">共{{ list.length }}个客户</div>
        <div style="margin-top: 10px;" class="option_row">
          <select @change="switchGroup" v-model="tableAskData.status">
            <option
              v-for="(item,index) in optionArray"
              :key="index"
              :value="item.key"
            >{{ item.name }}</option>
          </select>
        </div>
      </div>
    </div>
    <div style="display: flex;justify-content:space-between;font-size: 17px;padding: 20px 10px 5px 20px;">
      <div class="client_phone">客户电话</div>
      <div class="client_state">添加状态</div>
      <div class="client_operation">操作</div>
    </div>
    <div v-if="list.length!=0">
      <van-list>
        <van-cell v-for="(item,index) in list" :key="index">
          <div style="display: flex;justify-content:space-between;">
            <div class="client_phone">{{ item.phone }}</div>
            <div class="client_state">{{ item.status }}</div>
            <div class="client_operation">
              <van-button type="primary" @click="copyBtn(item)">复制</van-button>
            </div>
          </div>
        </van-cell>
      </van-list>
    </div>
    <div v-else>
      <Empty description="该状态没有客户哦～" />
    </div>
    <input type="text" class="copy-input" ref="copyInput">
  </div>
</template>
<script>
// eslint-disable-next-line no-unused-vars
import { contactBatchAddDetailApi } from '@/api/contactBatchAdd'
import { navigateToAddCustomer } from '@/utils/wxCodeAuth'
// eslint-disable-next-line no-unused-vars
import { Toast, Empty } from 'vant'
export default {
  components: {
    Empty
  },
  data () {
    return {
      list: [],
      employeeName: '',
      loading: false,
      finished: false,
      clientHeight: '',
      tableAskData: {
        batchId: '',
        status: 4
      },
      optionArray: [
        {
          key: 4,
          name: '全部'
        },
        {
          key: 0,
          name: '待分配'
        },
        {
          key: 1,
          name: '待添加'
        },
        {
          key: 2,
          name: '待通过'
        },
        {
          key: 3,
          name: '已添加'
        }
      ]
    }
  },
  created () {
    this.clientHeight = document.documentElement.clientHeight
    this.tableAskData.batchId = this.$route.query.batchId
    this.getTableData()
  },
  methods: {
    // 复制按钮
    async copyBtn (item) {
      const inputElement = this.$refs.copyInput
      inputElement.value = item.phone
      inputElement.select()
      document.execCommand('Copy')
      Toast('复制成功')
      await this.navigateToAddContact()
    },
    // 获取表格数据
    getTableData () {
      contactBatchAddDetailApi(this.tableAskData).then((res) => {
        this.list = res.data.list
        this.employeeName = res.data.employeeName
      })
    },
    switchGroup () {
      this.getTableData()
    },
    async navigateToAddContact () {
      await navigateToAddCustomer()
    }
  }
}
</script>
<style scoped lang="less">
.batch_page{
  padding: 20px;
  background: #fff;
  overflow-x: hidden;
}
.batch_tip{
  background: #FFF7F0;
  font-size: 28px;
  padding: 30px;
  color: #D5A680;
}
.table_tip{
  display: flex;
  justify-content:space-between;
  margin-top: 30px;
  width: 100%;
}
.client_num{
  font-size: 28px;
  font-weight: bold;
  border-left: 8px solid #69B7FF;
  padding-left: 10px;
  height: 40px;
  margin-top: 25px;
}
.option_row{
  width: 270px;
  height: 50px;
  border: 1px solid #cccccc;
  position: relative;
}
.option_row select{
  position: absolute;
  top: 0;
  /*清除select的边框样式*/
  border: none;
  /*清除select聚焦时候的边框颜色*/
  outline: none;
  /*将select的宽高等于div的宽高*/
  width: 100%;
  height: 50px;
}
.option_row option{
  height: 10px;
}
.client_phone{
  width: 40%;
}
.client_state{
  width: 33%;
  text-align: center;
}
.client_operation{
  width: 27%;
  text-align: center;
  button{
    height: 57px;
  }
}
.copy-input{
  width: 10px;
  position: absolute;
  top: 0;
  left: 0;
  opacity: 0;
  z-index: -10;
}
</style>
