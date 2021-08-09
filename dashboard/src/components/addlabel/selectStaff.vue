<template>
  <div>
    <a-modal v-model="visible" title="分配客户" :maskClosable="false" class="popContent" :footer="null">
      <div class="tipsStyle">
        <div>可将选中的客户转接给其他员工，进行后续服务</div>
        <div>注意:90天内客户只能被转接一次，一个客户最多只能被转接两次详情</div>
      </div>
      <div class="screen">
        <span>接替员工：</span>
        <div>
          <a-select
            placeholder="请选择企业员工"
            :showSearch="true"
            @change="selectStaff"
            style="width: 200px;margin-left: 5px;"
          >
            <a-select-option
              v-for="(item,index) in staffArray"
              :key="index"
              :value="item.name"
            >{{ item.name }}
            </a-select-option>
          </a-select>
          <div class="tips">
            <span>未找到接替员工？</span><a>查看详情</a>
          </div>
        </div>
      </div>
      <div class="btnRow">
        <a-button @click="hidden">取消</a-button>
        <a-button type="primary" @click="determine">确定</a-button>
      </div>
    </a-modal>
  </div>
</template>
<script>
import { department } from '@/api/contactTransfer'
export default {
  data () {
    return {
      visible: false,
      //  接替员工
      staffName: {},
      // 员工数据
      staffArray: []
    }
  },
  methods: {
    //  选中员工
    selectStaff (e) {
      this.staffArray.forEach((item) => {
        if (e == item.name) {
          this.staffName = item
        }
      })
    },
    // 确定传递数据
    determine () {
      this.visible = false
      this.$emit('change', this.staffName)
    },
    show (type) {
      this.visible = true
      this.staffName = []
      this.getNumberData()
    },
    hidden () {
      this.visible = false
    },
    getNumberData () {
      department().then((res) => {
        this.staffArray = res.data.employee
      })
    }
  }
}
</script>
<style lang="less">
  .popContent{
    .tipsStyle{
      background: #effaff;
      border-radius: 3px;
      padding: 9px 16px;
      font-size: 13px;
      color: rgba(0,0,0,.65);
      line-height: 17px;
    }
    .screen{
      display: flex;
      margin-top: 20px;
      span{
        line-height: 32px;
      }
      .tips{
        margin-top: 12px;
        font-size: 14px;
        color: #8f8f8f;
        margin-left: 5px;
        line-height: 22px;
      }
    }
    .btnRow{
      display: flex;
      justify-content:flex-end;
      margin-top: 12px;
      button{
        margin-left: 20px;
      }
      button:first-child{
        margin-left:0px;
      }
    }
  }
</style>
