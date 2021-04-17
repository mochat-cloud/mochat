<template>
  <div>
    <a-row class="top" :gutter="0" type="flex" justify="space-between">
      <a-col :span="12">
        <div class="child_module left">
          <div class="child_title">基本详情</div>
          <div class="user_info">
            <div class="item">
              <span>创建者：</span>
              <div class="user"><a-icon class="icon" type="user" />张明</div>
            </div>
            <div class="item"><span>创建时间：</span>2021-02-22 17:51</div>
            <div class="item"><span>群发对象：</span>购物车</div>
            <div class="item"><span>群发消息：</span>test</div>
          </div>
        </div>
      </a-col>
      <a-col :span="12">
        <div class="child_module right">
          <div class="child_title">数据详情</div>
          <a-row class="total_module" type="flex" justify="space-between">
            <a-col class="item item_hr" :span="12">
              <span>0</span>
              <p>已发送群主</p>
            </a-col>
            <a-col class="item" :span="12">
              <span>0</span>
              <p>未发送群主</p>
            </a-col>
            <a-col class="item item_hr" :span="12">
              <span>0</span>
              <p>已送达群聊</p>
            </a-col>
            <a-col class="item" :span="12">
              <span>0</span>
              <p>未送达群聊</p>
            </a-col>
          </a-row>
        </div>
      </a-col>
    </a-row>
    <a-tabs class="tabs_module" default-active-key="1" @change="callback">
      <a-tab-pane key="1" tab="客户群接受详情">
        <div class="text_num">共8个群聊</div>
        <div class="search_module">
          <span class="text">搜索群聊：</span>
          <a-input-search placeholder="请输入群发名称" @search="search" />
          <span class="text">群主：</span>
          <a-select placeholder="请选择群主">
            <a-select-option value="jack"> Jack </a-select-option>
            <a-select-option value="lucy"> Lucy </a-select-option>
            <a-select-option value="disabled" disabled> Disabled </a-select-option>
            <a-select-option value="Yiminghe"> yiminghe </a-select-option>
          </a-select>
          <span class="text">送达状态：</span>
          <a-select placeholder="请选择消息送达状态">
            <a-select-option value="jack"> Jack </a-select-option>
            <a-select-option value="lucy"> Lucy </a-select-option>
            <a-select-option value="disabled" disabled> Disabled </a-select-option>
            <a-select-option value="Yiminghe"> yiminghe </a-select-option>
          </a-select>
        </div>
        <a-table class="show_table" :columns="columns" :data-source="data" :scroll="{ x: 1200 }">
          <a-row slot="action">
            <a-button type="link" disabled>聊天记录</a-button>
            <a-button type="link">群详情</a-button>
          </a-row>
        </a-table>
      </a-tab-pane>
      <a-tab-pane key="2" tab="客户发送详情" force-render>客户发送详情 </a-tab-pane>
    </a-tabs>
  </div>
</template>
<script>
const columns = [
  { title: '群聊名称', width: 100, dataIndex: 'name', key: 'name' },
  { title: '群主', width: 100, dataIndex: 'age', key: 'age' },
  { title: '消息松达状态', dataIndex: 'address', key: '1', width: 150 },
  { title: '消息创建时间', dataIndex: 'address', key: '2', width: 150 },
  { title: '群聊创建时间', dataIndex: 'address', key: '3', width: 150 },
  {
    title: '操作',
    key: 'operation',
    fixed: 'right',
    width: 180,
    scopedSlots: { customRender: 'action' }
  }
]

const data = []
for (let i = 0; i < 100; i++) {
  data.push({
    key: i,
    name: `Edrward ${i}`,
    age: 32,
    address: `London Park no. ${i}`
  })
}

export default {
  data () {
    return {
      data,
      columns
    }
  },
  methods: {
    callback (key) {
      console.log(key)
    },
    search () {

    }
  }
}
</script>

<style lang="less">
.top {
  width: 100%;
}
.child_module {
  background-color: #fff;
  border-radius: 5px;
  padding-bottom: 1px;
  .child_title {
    font-size: 16px;
    font-weight: 600;
    padding: 15px;
    border-bottom: 1px solid #f2f2f2;
  }
  .user_info {
    .item {
      padding-left: 15px;
      line-height: 50px;
      span {
        color: #999;
        display: inline-block;
        width: 80px;
        text-align: right;
      }
      .user {
        display: inline-block;
        border: 1px solid #d9d9d9;
        border-radius: 5px;
        padding: 0px 6px;
        line-height: 30px;
        .icon {
          color: #8ba3c9;
          margin-right: 5px;
          font-size: 16px;
        }
      }
    }
    .item:first-child {
      margin-top: 25px;
    }
    .item:last-child {
      margin-bottom: 47px;
    }
  }
  .total_module {
    background-color: #fbfdff;
    border: 1px solid #daedff;
    margin: 30px;
    position: relative;
    padding: 10px 0px;
    .item {
      padding: 15px 0px;
      text-align: center;
      span {
        font-size: 20px;
      }
    }
    .item_hr::after {
      position: absolute;
      top: 30%;
      right: 0;
      height: 40%;
      width: 1px;
      content: '';
      background: #e9e9e9;
    }
  }
}
.left {
  margin-right: 8px;
}
.right {
  margin-left: 8px;
}
.tabs_module {
  background: #fff;
  margin-top: 16px;
  border-radius: 5px;
  .ant-tabs-nav-scroll {
    padding-left: 15px;
  }
  .text_num {
    padding-left: 15px;
  }
  .search_module {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    padding: 15px;
    .ant-select,
    .ant-input-search {
      width: 200px;
      margin-right: 25px;
    }
  }
}
.show_table {
  margin: 15px 15px;
  .ant-btn {
    padding: 0px 10px;
  }
}
</style>
