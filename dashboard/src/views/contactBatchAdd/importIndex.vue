<template>
  <div class="white-bg-module">
    <a-table class="table" rowKey="id" :data-source="tableData">
      <a-table-column title="名称" data-index="phone" />
      <a-table-column title="上传时间" data-index="name" />

      <a-table-column title="分配客户" data-index="date">
        <template slot-scope="date">
          <div class="user"><a-icon type="user" />{{ date }}</div>
        </template>
      </a-table-column>
      <a-table-column title="客户标签" data-index="tag" />
      <a-table-column title="导入客户数" data-index="status" />

      <a-table-column title="已添加客户数" data-index="date1" />
      <a-table-column title="添加完成率" data-index="status1" />
      <a-table-column title="操作">
        <template slot-scope="record">
          <span :id="record.id">
            <a> 提醒</a>
            <a-divider type="vertical" />
            <a>删除</a> <a-divider type="vertical" />
            <a>详情</a>
          </span>
        </template>
      </a-table-column>
    </a-table>
  </div>
</template>
<script>
export default {
  data () {
    return {
      tableData: [
        {
          id: 1,
          phone: '1564556',
          name: 'jsdh1',
          date: '2015- 10-12 12:00',
          date1: '2015- 10-12 12:00',
          tag: 'hha',
          status: 2,
          status1: 12,
          status2: 23
        }
      ]
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
