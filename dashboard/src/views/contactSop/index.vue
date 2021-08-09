<template>
  <div class="contact-sop-index">
    <div class="mb20">
      <a-button type="primary" @click="$router.push('/contactSop/create')">创建规则</a-button>
    </div>
    <a-card :bordered="false">
      <a-table :columns="table.col" :data-source="table.data">
        <div slot="members" slot-scope="row">
          <a-tag v-for="(member,i) in row.employees" :key="i">
            {{ member.name }}
          </a-tag>
        </div>
        <div slot="switch" slot-scope="row">
          <a-switch
            checked-children="开"
            un-checked-children="关"
            :defaultChecked="row.state === 1"
            @change="switchStateClick(row.id,row.state)"
          />
        </div>
        <div class="btn-group" slot="operating" slot-scope="row">
          <a
            @click="showSelectMember(row)">
            添加客服
          </a>
          <a-divider type="vertical"/>
          <a @click="$router.push({path:'/contactSop/detail',query:{id:row.id}})">
            详情
          </a>
          <a-divider type="vertical"/>
          <a-popover trigger="click" placement="bottomRight">
            <template slot="content">
              <div class="divider-btn" @click="$router.push({path:'/contactSop/edit',query:{id:row.id}})">
                修改
              </div>
              <div class="divider-btn" @click="delClick(row.id)">

                删除
              </div>
            </template>
            <a>编辑</a>
            <a-icon type="caret-down" :style="{ color: '#1890ff' }"/>
          </a-popover>
        </div>
      </a-table>
    </a-card>

    <selectMember ref="selectMember" @change="selectMemberChange"/>
  </div>
</template>

<script>
import { getList, switchState, del, setEmployee } from '@/api/contactSop'
import selectMember from '@/components/Select/member'

export default {
  data () {
    return {
      table: {
        col: [
          {
            dataIndex: 'name',
            title: '规则名称'
          },
          {
            dataIndex: 'creatorName',
            title: '创建人'
          },
          {
            scopedSlots: { customRender: 'members' },
            title: '使用成员'
          },
          {
            dataIndex: 'createTime',
            title: '创建时间'
          },
          {
            scopedSlots: { customRender: 'switch' },
            title: '开关'
          },
          {
            title: '操作',
            scopedSlots: { customRender: 'operating' }
          }
        ],
        data: []
      }
    }
  },
  mounted () {
    this.getData()
  },
  methods: {
    selectMemberChange (e, data) {
      const employees = JSON.stringify(e.map(v => {
        return v.id
      }))

      setEmployee({
        id: data.id,
        employees
      }).then(_ => {
        this.$message.success('操作成功')

        this.getData()
      })
    },

    showSelectMember (data) {
      const memebrs = JSON.parse(JSON.stringify(data.employees))

      this.$refs.selectMember.setSelect(memebrs, { id: data.id })
    },

    delClick (id) {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否删除',
        okText: '删除',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          del({
            id
          }).then(res => {
            that.$message.success('操作成功')
            that.getData()
          })
        }
      })
    },

    switchStateClick (id, current) {
      switchState({
        id,
        state: current === 0 ? 1 : 0
      }).then(res => {
        this.$message.success('操作成功')

        this.getData()
      })
    },

    getData () {
      getList().then(res => {
        this.table.data = res.data
      })
    }
  },
  components: { selectMember }
}
</script>

<style lang="less" scoped>
/deep/ .ant-card-body {
  padding: 0;
}

.divider-btn {
  height: 33px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}
</style>
