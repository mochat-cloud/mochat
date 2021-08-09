<template>
  <div class="white-bg-module">
    <div class="search">
      <div class="total"><span class="b">共1个客户</span> <a-divider type="vertical" />已添加0个客户</div>
      <div class="an-form">
        添加状态
        <a-select default-value="lucy" style="width: 200px" @change="handleChange">
          <a-select-option value="jack"> 全部 </a-select-option>
          <a-select-option value="lucy"> 手动录入 </a-select-option>
          <a-select-option value="disabled"> 有赞平台下单 </a-select-option>
          <a-select-option value="Yiminghe"> 手机线索导入 </a-select-option>
        </a-select>
        <a-input-search placeholder="请搜素电话" style="width: 200px" @search="onSearch" />
      </div>
    </div>
    <a-table class="table" rowKey="id" :data-source="tableData">
      <a-table-column title="客户电话" data-index="phone" />
      <a-table-column title="备注名" data-index="name" />

      <a-table-column title="员工" data-index="date">
        <template slot-scope="date">
          <div class="user"><a-icon type="user" />{{ date }}</div>
        </template>
      </a-table-column>
      <a-table-column title="客户标签" data-index="tag" />
      <a-table-column title="添加状态" data-index="status" />

      <a-table-column title="操作">
        <template slot-scope="record">
          <span :id="record.id">
            <a> 提醒添加</a>
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
<style scoped lang="less">
.white-bg-module {
  background-color: #fff;
  .search {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    align-items: center;
    padding: 15px 15px 0px;
    .total {
      .b {
        font-weight: bold;
      }
    }
    .ant-select {
      width: 120px;
      margin-right: 15px;
    }
    .ant-calendar-picker {
      margin-right: 15px;
    }
  }
}

.user {
  font-size: 13px;
  border: 1px solid #e9e9e9;
  margin-bottom: 5px;
  border-radius: 5px;
  text-align: center;
  display: inline-block;
  padding: 2px 5px;
}
</style>
