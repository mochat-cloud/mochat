<template>
  <div class="white-bg-module">
    <a-table :columns="drawTable.columns" :data-source="drawTable.data">
      <div slot="allotEmployee" slot-scope="text">
        <a-tag v-for="(item,index) in text" :key="index">{{ item.name }}</a-tag>
      </div>
      <div slot="tags" slot-scope="text">
        <a-tag v-for="(item,index) in text" :key="index">{{ item.name }}</a-tag>
      </div>
      <!--      -->
      <div slot="operation" slot-scope="text,record">
        <a @click="remindBtn">提醒</a>
        <a-divider type="vertical" />
        <a @click="delWriteRow(record)">删除</a>
        <a-divider type="vertical" />
        <a @click="$router.push({ path: '/contactBatchAdd/importShow?recordId='+record.id })">详情</a>
      </div>
    </a-table>
  </div>
</template>
<script>
// eslint-disable-next-line no-unused-vars
import { importData, importDestroyApi } from '@/api/contactBatchAdd'
export default {
  data () {
    return {
      //  导入弹窗
      drawTable: {
        columns: [
          {
            title: '标题',
            dataIndex: 'title'
          },
          {
            title: '上传时间',
            dataIndex: 'uploadAt'
          },
          {
            title: '分配员工',
            dataIndex: 'allotEmployee',
            scopedSlots: { customRender: 'allotEmployee' }
          },
          {
            title: '标签',
            dataIndex: 'tags',
            scopedSlots: { customRender: 'tags' }
          },
          {
            title: '导入数量',
            dataIndex: 'importNum',
            width: 100
          },
          {
            title: '成功添加数量',
            dataIndex: 'addNum',
            width: 120
          },
          {
            title: '文件名',
            dataIndex: 'fileName',
            width: 200
          },
          {
            title: '操作',
            dataIndex: 'operation',
            scopedSlots: { customRender: 'operation' }
          }
        ],
        data: []
      }
    }
  },
  created () {
    this.importDataClick()
  },
  methods: {
    // 提醒
    remindBtn (record) {

    },
    // 获取数据
    importDataClick () {
      importData().then(res => {
        this.drawTable.data = res.data.data
      })
    },
    //  删除
    delWriteRow (record) {
      const that = this
      this.$confirm({
        title: '提示',
        content: '是否删除',
        okText: '删除',
        okType: 'danger',
        cancelText: '取消',
        onOk () {
          importDestroyApi({
            id: record.id
          }).then((res) => {
            that.importDataClick()
            that.$message.success('删除成功')
          })
        }
      })
    }
  }
}
</script>
<style scoped>
.white-bg-module {
  background-color: #fff;
}
.user {
  font-size: 13px;
  border: 1px solid #e9e9e9;
  margin-bottom: 5px;
  border-radius: 5px;
  text-align: center;
  display: inline-block;
  padding:2px 5px;
}
</style>
