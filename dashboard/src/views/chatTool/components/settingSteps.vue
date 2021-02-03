<template>
  <div class="chat-tool-wrapper">
    <div class="lists">
      <div>1、可配置【内容引擎】应用页面到聊天侧边栏，方便成员在外部会话中查看相关的内容（支持多种消息格式，包括文本，图片，视频，文件以及H5）并使用，提高客户服务效率。 </div>
      <div>2、可配置【客户CRM/群CRM】应用页面到聊天侧边栏，方便成员在外部会话中，不仅可以追踪客户/群行为轨迹，洞察客户需求，还可以快速完善客户/群资料和记录客户/群跟进联系情况，继而提供员工工作效率。</div>
      <div>3、配置的应用名称必须以【MoChat】开头。（例如：MoChatSCRM）
      </div>
    </div>
    <div class="first-wrapper step">
      <div class="title">
        第一步，设置企业微信侧边栏应用
      </div>
      <div class="content">
        <div v-for="(item, index) in agents" :key="index" class="img-wrapper">
          <img class="img" :src="item.squareLogoUrl" alt="">
          <div class="name">{{ item.name }}</div>
        </div>
      </div>
    </div>
    <div class="second-wrapper step">
      <div class="title">
        第二步，设置聊天侧边栏应用页面
      </div>
      <div class="content">
        <div v-for="(item, index) in chatTools" :key="index" class="url-text">
          {{ `${index + 1}、请至企业微信官方后台“客户联系-配置-聊天侧边栏管理 创建【${item.pageName}】应用页面，并将地址 ${item.pageUrl} 设置为该应用主页。` }}
        </div>
      </div>
      <div>
        <a href="https://work.weixin.qq.com/wework_admin/loginpage_wx" target="_blank">
          <a-button type="primary" size="large">前往企业微信后台</a-button></a>
      </div>
    </div>
    <div class="third-wrapper step">
      <div class="title">
        第三步：设置应用可信域名
      </div>
      <div class="content">
        <div>
          {{ `在应用设置页，将 ${whiteDomains.join(',  ')} 设置为可信域名。` }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    chatTools: {
      type: Array,
      default: () => []
    },
    agents: {
      type: Array,
      default: () => []
    },
    whiteDomains: {
      type: Array,
      default: () => []
    }
  }
}
</script>

<style lang="less" scoped>
.chat-tool-wrapper {
  .lists {
    margin-bottom: 20px;
    background-color: #fffbe6;
    div {
      padding: 8px 15px;
    }
  }
  .step {
    background: #fff;
    padding: 8px 15px;
    margin-bottom: 20px;
    .title {
      font-size: 17px;
      font-weight: 600;
    }
  }
  .upload {
    max-width: 400px;
    margin: 20px 0;
  }
  .first-wrapper {
    .content {
      display: flex;
      flex-wrap: wrap;
      .img-wrapper {
        flex: 0 0 23%;
        display: flex;
        align-items: center;
        border: 1px solid #bebcbc;
        margin: 10px;
        .img {
          width: 60px;
          height: 60px;
          flex: 0 0 40px
        }
        .name {
          margin-left: 20px;
        }
      }
    }
  }
  .second-wrapper {
    .content {
      margin-bottom: 20px;
      .url-text {
        padding: 8px 15px;
        // margin-bottom: 20px;
      }
    }
  }
}
</style>
