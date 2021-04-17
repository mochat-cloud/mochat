<template>
  <div class="index_module">
    <a-row class="search" type="flex" justify="space-between">
      <a-button type="primary">新建群发</a-button>
      <a-input-search placeholder="请输入群发名称" @search="onSearch" />
    </a-row>
    <a-table class="index_table" :columns="columns" :data-source="data"></a-table>
  </div>
</template>
<script>
const columns = [
  {
    dataIndex: 'name',
    title: '群发名称',
    slots: { title: 'customTitle' },
    scopedSlots: { customRender: 'name' }
  },
  {
    title: '发送时间',
    dataIndex: 'age',
    key: 'age'
  },
  {
    title: '群发内容',
    dataIndex: 'address',
    key: 'address'
  },
  {
    title: '已发送成员',
    key: 'tags',
    dataIndex: 'tags'
  },
  {
    title: '送达客户',
    key: 'action'
  },

  {
    title: '未发送成员',
    key: 'action'
  },

  {
    title: '操作',
    key: 'action'
  }
]

const data = [
  {
    key: '1',
    name: 'John Brown',
    age: 32,
    address: 'New York No. 1 Lake Park',
    tags: ['nice', 'developer']
  },
  {
    key: '2',
    name: 'Jim Green',
    age: 42,
    address: 'London No. 1 Lake Park',
    tags: ['loser']
  },
  {
    key: '3',
    name: 'Joe Black',
    age: 32,
    address: 'Sidney No. 1 Lake Park',
    tags: ['cool', 'teacher']
  }
]

export default {
  data () {
    return {
      data,
      columns
    }
  }
}
</script>

<style lang="less">
.index_module {
  background-color: #fff;
  padding: 0px 15px;
}
.search {
  padding: 15px 0px;
  .ant-input-search {
    width: 200px;
  }
}
</style>
