<template>
  <div>
    <a-row class="top child_module" :gutter="0" type="flex" justify="space-between">
      <a-col :span="16">
        <div class="left">
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
          <div class="obj">
            <span class="title">群发对象：</span>
            <div class="data">
              <div class="item">
                <p class="p">在张宝龙，满足以下条件中的两个客户</p>
                <div class="sex"><span>性别：</span>全部性别</div>
              </div>
            </div>
          </div>
        </div>
      </a-col>
      <a-col :span="7" class="msg_module">
        <div class="msg_title">客户收到的消息</div>
        <msgModel :width="300"></msgModel>
      </a-col>
    </a-row>
    <a-row :gutter="0" type="flex" justify="space-between">
      <a-col :span="24">
        <div class="child_module">
          <div class="child_title">数据统计</div>
          <a-row class="total" type="flex" :gutter="20" justify="space-between">
            <a-col class="item" :span="8">
              <a-row class="total_module" type="flex" justify="space-between">
                <a-col class="item item_hr" :span="12">
                  <span>0<span>人</span></span>
                  <p>已发送成员</p>
                </a-col>
                <a-col class="item" :span="12">
                  <span>0<span>人</span></span>
                  <p>送达用户</p>
                </a-col>
              </a-row>
            </a-col>
            <a-col class="item" :span="8">
              <a-row class="total_module" type="flex" justify="space-between">
                <a-col class="item item_hr" :span="12">
                  <span>0<span>人</span></span>
                  <p>未发送成员</p>
                </a-col>
                <a-col class="item" :span="12">
                  <span>0<span>人</span></span>
                  <p>未送达客户</p>
                </a-col>
              </a-row>
            </a-col>
            <a-col class="item" :span="8">
              <a-row class="total_module" type="flex" justify="space-between">
                <a-col class="item item_hr" :span="12">
                  <span>0<span>人</span></span>
                  <p>客户接受已达上限</p>
                </a-col>
                <a-col class="item" :span="12">
                  <span>0<span>人</span></span>
                  <p>因不是好友发送失败</p>
                </a-col>
              </a-row>
            </a-col>
          </a-row>
        </div>
      </a-col>
    </a-row>
    <a-row :gutter="7" type="flex" justify="space-between">
      <a-col :span="12">
        <div class="child_module">
          <div class="child_title">成员详情</div>
          <a-tabs class="tabs_module" size="small" default-active-key="1" @change="callback">
            <a-tab-pane key="1" tab="本次推动全部成员"> </a-tab-pane>
            <a-tab-pane key="2" tab="已发送成员" force-render> </a-tab-pane>
            <a-tab-pane key="3" tab="为未发送成员" force-render> </a-tab-pane>
            <a-tab-pane key="4" tab="发送失败" force-render> </a-tab-pane>
          </a-tabs>
          <div class="search_module">
            <div class="total_num"><a-icon type="user" /> <span class="text">共一人</span></div>
            <a-input-search placeholder="请输入成员名称" @search="search" />
          </div>
          <div class="data_list">
            <div class="item" v-for="item in num" :key="item">
              <a-row type="flex" justify="space-between" align="middle">
                <a-col :span="19">
                  <div class="userinfo">
                    <img src="https://img1.baidu.com/it/u=2397591911,2044651242&fm=26&fmt=auto&gp=0.jpg" />
                    <div class="info">
                      <div class="name">妮萨 <span>未发送</span></div>
                      <div class="text">共两个好友</div>
                    </div>
                  </div>
                </a-col>
                <a-col :span="4">
                  <a-button type="primary">提醒</a-button>
                </a-col>
              </a-row>
            </div>
          </div>
        </div>
      </a-col>
      <a-col :span="12">
        <div class="child_module">
          <div class="child_title">客户详情</div>
          <a-tabs class="tabs_module" size="small" default-active-key="1" @change="callback">
            <a-tab-pane key="1" tab="本次推送全部客户"> </a-tab-pane>
            <a-tab-pane key="2" tab="已送达" force-render> </a-tab-pane>
            <a-tab-pane key="3" tab="未送达客户" force-render> </a-tab-pane>
            <a-tab-pane key="4" tab="客户接受已达上限" force-render> </a-tab-pane>
            <a-tab-pane key="4" tab="已不是好友客户" force-render> </a-tab-pane>
          </a-tabs>
          <div class="search_module">
            <div class="total_num"><a-icon type="user" /> <span class="text">共一人</span></div>
            <a-input-search placeholder="请输入客户名称" @search="search" />
          </div>
          <div class="data_list">
            <div class="item" v-for="item in num" :key="item">
              <a-row type="flex" justify="space-between" align="middle">
                <a-col :span="19">
                  <div class="userinfo">
                    <img src="https://img1.baidu.com/it/u=2397591911,2044651242&fm=26&fmt=auto&gp=0.jpg" />
                    <div class="info">
                      <div class="name">妮萨</div>
                      <div class="btn">未发送</div>
                    </div>
                  </div>
                </a-col>
                <a-col :span="5">
                  <span class="text text_span">通过王所发送</span>
                </a-col>
              </a-row>
            </div>
          </div>
        </div>
      </a-col>
    </a-row>
  </div>
</template>
<script>
import msgModel from '@/components/MsgModel'
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
  components: {
    msgModel
  },
  data () {
    return {
      data,
      columns,
      num: 3
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
  margin-top: 15px;
  .child_title {
    font-size: 16px;
    font-weight: 600;
    padding: 15px;
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
      margin-top: 5px;
    }
    .item:last-child {
      margin-bottom: 15px;
    }
  }
  .obj {
    border-top: 1px dashed #f2f2f2;
    display: flex;
    justify-content: flex-start;
    padding: 25px 15px;
    flex-wrap: nowrap;
    .title {
      color: #999;
      width: 80px;
      text-align: right;
      flex-shrink: 0;
    }
    .data {
      display: flex;
      justify-content: flex-start;
      .item {
        background-color: #fbfbfb;
        border: 1px solid #e8e8e8;
        border-radius: 5px;
        .p {
          line-height: 40px;
          padding: 0px 15px;
          margin-bottom: 0px;
          border-bottom: 1px solid #e8e8e8;
        }
        .sex {
          line-height: 60px;
          padding: 0px 15px;
          span {
            color: #999;
          }
        }
      }
    }
  }
  .msg_module {
    padding: 25px 0px ;
    .msg_title {
      text-align: center;
      padding-bottom: 10px;
    }
  }
  .total {
    padding: 0px 15px 15px;
  }
  .total_module {
    background-color: #fbfdff;
    border: 1px solid #daedff;
    position: relative;
    padding: 10px 0px;
    .item {
      padding: 15px 0px;
      text-align: center;
      span {
        font-size: 20px;
        color: black;
        span {
          font-size: 22px;
          font-weight: bold;
          padding-left: 3px;
        }
      }
      p {
        color: #666;
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
  .ant-tabs-tab {
    padding: 0px 8px;
    margin-right: 10px;
  }
}
.search_module {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0px 15px;
  .total_num {
    color: black;
    .anticon-user {
      font-size: 16px;
      color: #666;
    }
  }
  .ant-select,
  .ant-input-search {
    width: 200px;
  }
}
.data_list {
  margin: 15px;
  .item {
    border: 1px solid #ffc8c8;
    background-color: #fffcfc;
    margin-bottom: 15px;
    .userinfo {
      display: flex;
      justify-content: flex-start;
      padding: 15px;
      align-items: center;
      img {
        width: 60px;
        border-radius: 50%;
      }
      .info {
        margin-left: 15px;
        .name {
          font-size: 16px;
          color: black;
          span {
            display: inline-block;
            background-color: #ffc2c2;
            font-size: 12px;
            padding: 2px 5px;
            margin-left: 5px;
          }
        }
        .btn {
          display: inline-block;
          background-color: #ffc2c2;
          font-size: 12px;
          padding: 2px 5px;
          margin-top: 5px;
          border-radius: 3px;
        }
        .text {
          margin-top: 5px;
        }
      }
    }
    .text_span {
    }
  }
}
</style>
