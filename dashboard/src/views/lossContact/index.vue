<template>
  <div class="loss-customers">
    <a-card>
      <a-button v-permission="'/lossContact/index@choose'" @click="() => { this.choosePeopleShow = true }">选择部门成员</a-button>
      <a-modal
        title="选择企业成员"
        :maskClosable="false"
        :width="700"
        :visible="choosePeopleShow"
        @cancel="() => {this.choosePeopleShow = false; this.employees = []; this.employeeIdList = ''}"
      >
        <Department v-if="choosePeopleShow" :isChecked="employees" :memberKey="employees" @change="peopleChange"></Department>
        <template slot="footer">
          <a-button @click="() => { this.choosePeopleShow = false; this.employees = []; this.employeeIdList = '' }">取消</a-button>
          <a-button type="primary" @click="() => { this.choosePeopleShow = false; this.getTableData(); this.employeeIdList = '';this.employees = [];}">确定</a-button>
        </template>
      </a-modal>
      <div class="table-wrapper">
        <a-table
          :columns="columns"
          :data-source="tableData"
          :rowKey="record => record.id"
          :pagination="pagination"
          @change="handleTableChange">
          <div slot="detail" slot-scope="text, record">
            <div class="detail-box">
              <img :src="record.avatar"/>
              <span>{{ record.name }}</span>
              <span style="color: #3CD389">@微信</span>
            </div>
          </div>
          <div slot="tag" slot-scope="text, record">
            <span v-if="record.tag.length !== 0">{{ record.tag.join(',') }}</span>
            <span v-else>--</span>
          </div>
        </a-table>
      </div>
    </a-card>
  </div>
</template>

<script>
import { getLossContactList } from '@/api/lossContact'
import Department from '@/components/department'
export default {
  components: {
    Department
  },
  data () {
    return {
      columns: [
        {
          title: '客户信息',
          dataIndex: 'detail',
          align: 'center',
          scopedSlots: { customRender: 'detail' }
        },
        {
          title: '标签',
          dataIndex: 'tag',
          align: 'center',
          scopedSlots: { customRender: 'tag' }
        },
        {
          title: '归属成员',
          dataIndex: 'employeeName',
          align: 'center',
          scopedSlots: { customRender: 'employeeName' }

        },
        {
          title: '备注',
          dataIndex: 'remark',
          align: 'center'
        },
        {
          title: '删除时间',
          dataIndex: 'deletedAt',
          align: 'center'
        }
      ],
      tableData: [],
      choosePeopleShow: false,
      employeeIdList: '',
      pagination: {
        total: 0,
        current: 1,
        pageSize: 10,
        showSizeChanger: true
      },
      employees: []
    }
  },
  created () {
    this.getTableData()
  },
  methods: {
    getTableData () {
      const params = {
        employeeId: this.employeeIdList,
        page: this.pagination.current,
        perPage: this.pagination.pageSize
      }
      getLossContactList(params).then(res => {
        this.tableData = res.data.list
        this.pagination.total = res.data.page.total
      })
    },
    handleTableChange ({ current, pageSize }) {
      this.pagination.current = current
      this.pagination.pageSize = pageSize
      this.getTableData()
    },
    // 成员选择
    peopleChange (data) {
      const arr = []
      data.map(item => {
        arr.push(item.employeeId)
      })
      this.employeeIdList = arr.join(',')
    }
  }
}
</script>

<style lang="less" scoped>
.loss-customers {
  .table-wrapper {
    margin-top: 20px;
    .detail-box {
      display: flex;
      align-items: center;
      img {
        width: 40px;
        height: 40px;
      }
      span {
        margin-left: 20px;
      }
    }
  }
}
</style>
