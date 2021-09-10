<template>
  <div>
    <a-row class="user-total" type="flex" :gutter="0" justify="space-between">
      <a-col class="white-bg-module user" :span="12">
        <img src="https://img0.baidu.com/it/u=73163259,1373343321&fm=26&fmt=auto&gp=0.jpg" />
        <div class="info">
          <p>张萨克</p>
          <div class="status">管理员</div>
        </div>
      </a-col>
      <a-col class="white-bg-module total_module" :span="12">
        <a-row type="flex" :gutter="20" justify="space-between">
          <a-col class="item item_hr" :span="12">
            <p>总客户数</p>
            <span>0 <span>人</span></span>
          </a-col>
          <a-col class="item" :span="12">
            <p>今日新增</p>
            <span>0 <span>人</span></span>
          </a-col>
        </a-row>
      </a-col>
    </a-row>
    <div class="white-bg-module">
      <div class="client-title">联系客户统计</div>
      <a-row class="user-total" type="flex" align="bottom" :gutter="0" justify="first-start">
        <a-col class="white-bg-module total_module total_module1" :span="18">
          <a-row type="flex" :gutter="20" justify="space-between">
            <a-col class="item item_hr" :span="6">
              <p>昨日聊天总数</p>
              <span>0 <span>条</span></span>
            </a-col>
            <a-col class="item" :span="6">
              <p>昨日发消息总数</p>
              <span>0 <span>条</span></span>
            </a-col>
            <a-col class="item item_hr" :span="6">
              <p>昨日回复聊天占比</p>
              <span>0 <span>%</span></span>
            </a-col>
            <a-col class="item" :span="6">
              <p>昨日平均首次回复时长</p>
              <span>0 <span>min</span></span>
            </a-col>
          </a-row>
        </a-col>
        <a-col class="tooltip-text" :span="3">
          <a-tooltip placement="topLeft" arrow-point-at-center>
            <a-icon type="question-circle" /> 统计说明
            <div class="text" slot="title">
              1、聊天总数：成员有主动发送过消息的单聊总数<br />

              2、发送消息数：成员在单聊中发送的消息总数<br />

              3、已回复聊天占比：客户主动发起聊天后，成员在一个自然日内有回复过消息的聊天数/客户主动发起的聊天数比例<br />

              4、平均首次回复时长：客户主动发起聊天后，成员在一个自然日内首次回复的时长间隔为首次回复时长
            </div>
          </a-tooltip>
        </a-col>
      </a-row>
    </div>
    <div class="white-bg-module"></div>
  </div>
</template>
<style scoped lang="less">
.white-bg-module {
  max-height: 400px;
  background-color: #fff;
  margin-bottom: 20px;
  border-radius: 5px;
}
.user-total {
  width: 100%;
}
.user {
  display: flex;
  justify-content: flex-start;
  padding: 15px 0px;
  width: 49%;
  align-items: center;
  img {
    margin-left: 15px;
    width: 80px;
    margin-right: 15px;
    border-radius: 10px;
  }
  .info {
    p {
      margin: 0px;
      font-size: 16px;
      font-weight: bold;
    }
    .status {
      border: 1px solid #1890ff;
      color: #1890ff;
      border-radius: 5px;
      margin-top: 10px;
      padding: 2px 5px;
      font-size: 12px;
    }
  }
}
.total_module {
  widows: 49%;
  position: relative;
  padding: 10px 0px;
  .item {
    padding: 15px 0px;
    text-align: center;
    span {
      font-size: 28px;
      color: black;
      font-weight: bold;
      span {
        font-size: 14px;
        font-weight: normal;
      }
    }
    p {
      color: #8691a4;
    }
  }
  .item_hr::after {
    position: absolute;
    top: 20%;
    right: 0;
    height: 60%;
    width: 1px;
    content: '';
    background: #e9e9e9;
  }
}
.client-title {
  color: #1890ff;
  font-size: 18px;
  padding: 15px;
  font-weight: bold;
  margin-bottom: 20px;
  border-bottom: 1px solid #f2f2f2;
}
.tooltip-text {
  margin-bottom: 20px;
  padding-left: 20px;
}
.total_module1 {
  background: #fbfdff;
  margin-left: 20px;
  border: 1px solid #cfe8ff;
}
</style>
