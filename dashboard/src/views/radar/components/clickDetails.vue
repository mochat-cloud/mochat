<template>
  <div class="click_detail">
    <a-modal
      v-model="visible"
      title="点击详情"
      :footer="null"
      :maskClosable="false"
      width="800px"
      :zIndex="10000">
      <a-timeline>
        <a-timeline-item v-for="(item,index) in seedetails.click_info" :key="index">
          <div>{{ item.createdAt }}</div>
          <div class="look_detail" v-html="item.content">{{ item.content }}</div>
        </a-timeline-item>
      </a-timeline>
    </a-modal>
    <a-modal v-model="tablePop" title="查看详情" :footer="null" :maskClosable="false" width="800px">
      <!--            客户数据表格-->
      <div class="table mt16">
        <a-table
          :columns="table.col"
          :data-source="table.data">
          <div slot="employee" slot-scope="text">
            <a-tag><a-icon type="user" />{{ text }}</a-tag>
          </div>
          <!--          slot-scope="text, record"-->
          <div slot="operation" slot-scope="text, record">
            <a @click="seedataDetail(record)">点击详情</a>
            <a-divider type="vertical" />
            <span style="color: gray;" v-if="record.contact_id==0||record.employee_id==0">客户详情</span>
            <a v-else @click="$router.push({ path: '/workContact/contactFieldPivot?contactId='+record.contact_id+'&employeeId='+record.employee_id+'&isContact=2'})">客户详情</a>
          </div>
        </a-table>
      </div>
    </a-modal>
  </div>
</template>
<script>
// eslint-disable-next-line no-unused-vars
import { showContactApi } from '@/api/radar'
export default {
  data () {
    return {
      visible: false,
      tablePop: false,
      panelType: 0,
      seedetails: {},
      // 客户数据表格
      table: {
        col: [
          {
            dataIndex: 'contactName',
            title: '客户'
          },
          {
            dataIndex: 'employee',
            title: '所属成员',
            scopedSlots: { customRender: 'employee' }
          },
          {
            dataIndex: 'createdAt',
            title: '最近点击时间'
          },
          {
            dataIndex: 'channel',
            title: '最近点击渠道'
          },
          {
            dataIndex: 'click_num',
            title: '客户点击总次数'
          },
          {
            dataIndex: 'operation',
            title: '操作',
            scopedSlots: { customRender: 'operation' }
          }
        ],
        data: []
      }
    }
  },
  created () {
  },
  methods: {
  //  获取点击详情数据
    getClickDetails (params) {
      showContactApi(params).then((res) => {
        // console.log(res)
        this.tablePop = true
        this.table.data = res.data.list
      })
    },
    show (type, data) {
      // type=0  客户数据  type=1渠道数据
      this.panelType = type
      if (this.panelType == 0) {
        this.visible = true
        this.detailsDataShow(data)
      } else if (this.panelType == 1) {
        this.getClickDetails({
          type: data.type,
          radar_id: data.radar_id,
          channelId: data.channelId
        })
      }
    },
    //  点击详情
    detailsDataShow (data) {
      this.seedetails = data
    },
    seedataDetail (data) {
      this.visible = true
      this.seedetails = data
    }
  }
}
</script>
<style>
.ant-modal-title{
  font-size: 17px;
  text-align: center;
  font-weight: 600;
}
.look_detail{
  background: #f7fbff;
  border-radius: 1px;
  border: 1px solid #b4cbf8;
  font-size: 14px;
  font-family: PingFangSC-Regular,PingFang SC;
  font-weight: 400;
  color: #425772;
  line-height: 20px;
  padding: 10px;
  margin-top: 10px;
}
.look_detail img{
  width: 20px;
  height: 20px;
  margin-left: 5px;
  margin-right: 5px;
}
</style>
