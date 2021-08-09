<template>
  <div class="select-public">
    <a-dropdown class="add-account-select">
      <a-button>
        选择公众号
        <a-icon type="down"/>
      </a-button>
      <a-menu slot="overlay">
        <a-menu-item @click="$router.push('/officialAccount/create')">
          <div class="add-account">
            <a-icon type="plus"/>
            添加公众号
          </div>
        </a-menu-item>
        <a-menu-item>
          <div class="account-item">
            <div class="avatar">
              <img
                src="https://wx.qlogo.cn/mmopen/9YAYFgu8QC3ibLZ708eUjyWa9hfweLnLfhGB5mGJ0pibq9IWfndUy6WFAwy0w0n58fvgH4iaapFlDROeKHfcL7gPhmXEvr0E9Km/64">
            </div>
            <div class="info">
              <div class="name">MoChat科技</div>
              <div class="type">
                服务号
                <a-tag color="green">
                  已认证
                </a-tag>
              </div>
            </div>
          </div>
        </a-menu-item>
        <a-menu-item>
          <div class="account-item">
            <div class="avatar">
              <img
                src="https://wx.qlogo.cn/mmopen/9YAYFgu8QC3ibLZ708eUjyWa9hfweLnLfhGB5mGJ0pibq9IWfndUy6WFAwy0w0n58fvgH4iaapFlDROeKHfcL7gPhmXEvr0E9Km/64">
            </div>
            <div class="info">
              <div class="name">MoChat科技</div>
              <div class="type">
                服务号
                <a-tag color="green">
                  已认证
                </a-tag>
              </div>
            </div>
          </div>
        </a-menu-item>
      </a-menu>
    </a-dropdown>
  </div>
</template>

<script>
export default {
  name: 'Public'
}
</script>

<style lang="less" scoped>
.add-account-select {
  width: 187px;
}

.add-account {
  border-bottom: 1px dashed #e8e8e8;
  padding-left: 8px;
  padding-top: 4px;
  padding-bottom: 4px;
}

.account-item {
  display: flex;
  align-items: center;
  padding: 0 13px;
  margin-bottom: 5px;

  .avatar {
    margin-right: 10px;

    img {
      width: 30px;
      height: 30px;
      border-radius: 2px;
    }
  }

  .info {
    .name {
      font-size: 13px;
    }

    .type {
      font-size: 12px;
    }
  }
}
</style>
