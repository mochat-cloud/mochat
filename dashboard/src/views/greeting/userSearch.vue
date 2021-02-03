<template>
  <div class="userSearch">
    <div class="title">
      如何在个人微信和企业微信同时添加好友客户？
    </div>
    <div class="first">
      客户搜索个人微信号添加员工时，员工可在 【企业微信】 通过客户好友申请 （个人微信需绑定企业微信账号）
    </div>
    <div class="title">
      使用场景
    </div>
    <ul>
      <li class="list">
        <span class="dot"></span>
        <span class="label">【场景一】：</span>
        <div class="text">
          在移动端和PC端进行投放时，客户不方便通过识别二维码的方式添加员工。企业可在投放渠道放置员工
          个人微信号，客户添加时，员工可同时在【个人微信】和【企业微信】通过客户的好友申请
        </div>
      </li>
      <li class="list">
        <span class="dot"></span>
        <span class="label">【场景二】：</span>
        <div class="text">
          将客户同时引流至企业微信和个人微信
        </div>
      </li>
    </ul>
    <div class="title">
      使用步骤
    </div>
    <div class="line"></div>
    <ul>
      <li class="blue-list">
        <span class="dot"></span>
        (1) 客户添加员工个人微信时，员工个人微信收到好友验证消息
      </li>
      <li class="blue-list">
        <span class="dot"></span>
        (2) 员工前往验证 — 点击 【查看】
      </li>
    </ul>
    <div class="img-wrapper">
      <img class="img" :src="require('@/assets/1.jpeg')" alt="">
      <img class="img" :src="require('@/assets/2.jpeg')" alt="">
    </div>
    <ul>
      <li class="blue-list">
        <span class="dot"></span>
        (3) 然后点击 【去企业微信添加对方】
      </li>
      <li class="blue-list">
        <span class="dot"></span>
        (4) 自动跳转到企业微信， 先不要点击【通过验证】
      </li>
    </ul>
    <div class="img-wrapper">
      <img class="img" :src="require('@/assets/3.jpeg')" alt="">
      <img class="img" :src="require('@/assets/4.jpeg')" alt="">
    </div>
    <ul>
      <li class="blue-list">
        <span class="dot"></span>
        (5) 切换至员工个人微信进行 【前往验证】- 点击 【完成】 - 添加微信好友成功
      </li>
    </ul>
    <div class="img-wrapper four-wrapper">
      <img class="img" :src="require('@/assets/5.jpeg')" alt="">
      <img class="img" :src="require('@/assets/6.jpeg')" alt="">
      <img class="img" :src="require('@/assets/7.jpeg')" alt="">
      <img class="img" :src="require('@/assets/8.jpeg')" alt="">
    </div>
    <ul>
      <li class="blue-list">
        <span class="dot"></span>
        (6) 再次切换至企业微信【通过验证】- 点击 【完成】- 完成添加企业微信好友
      </li>
    </ul>
    <div class="img-wrapper four-wrapper">
      <img class="img" :src="require('@/assets/9.jpeg')" alt="">
      <img class="img" :src="require('@/assets/10.jpeg')" alt="">
      <img class="img" :src="require('@/assets/11.jpeg')" alt="">
      <img class="img" :src="require('@/assets/12.jpeg')" alt="">
    </div>
    <ul>
      <li class="blue-list">
        <span class="dot"></span>
        (7) 个人微信和企业微信同时添加好友完成
      </li>
    </ul>
    <div class="img-wrapper">
      <img class="img" :src="require('@/assets/13.jpeg')" alt="">
      <img class="img" :src="require('@/assets/14.jpeg')" alt="">
    </div>
  </div>
</template>

<script>
export default {

}
</script>

<style lang="less" scoped>
.userSearch {
  background-color: #fff;
  padding: 20px;
  .title {
    font-size: 14px;
    font-weight: 600;
    margin: 10px
  }
  .first {
    width: 70%;
    padding: 10px;
    background: #f8f8f8;
  }
  ul {
    list-style: none;
    padding: 0;
  }
  .list {
    display: flex;
    padding: 10px 0;
    .dot {
      margin-top: 6px;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #cacaca;
    }
    .label {
      flex: 0 0 85px;
    }
    .text {
      flex: 1;
      max-width: calc(70% - 100px);
    }
  }
  .line {
    width: 70%;
    height: 1px;
    background-color: #eee;
    margin-top: 12px;
    position: relative;
    margin-bottom: 16px;
    &:before {
      content: "";
      width: 62px;
      height: 3px;
      background: #1890ff;
      position: absolute;
      left: 10px;
      bottom: 0;
    }
  }
  .blue-list {
    width: 70%;
    padding: 10px;
    background: #f0f8ff;
    .dot {
      margin-top: 6px;
      display: inline-block;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #1890ff;
    }
  }
  .img-wrapper {
    margin-bottom: 50px;
    .img:not(:last-child) {
      margin-right: 50px
    }
  }
  .four-wrapper {
    .img:not(:last-child) {
      margin-right: 15px
    }
  }
}
</style>
