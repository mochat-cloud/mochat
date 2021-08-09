<template>
  <div class="details">
    <a-modal v-model="modalShow" on-ok="handleOk" :width="767" :footer="false" centered>
      <template slot="title">
        修改门店信息
      </template>
      <div class="tips">
        <a-alert message="配置过程较为复杂，可联系客服提供人工配置服务" type="info" show-icon />
      </div>
      <div class="shopkeeper elastic">
        <span>
          城市：
        </span>
        <div class="city-list">

        </div>
      </div>
      <div class="shop-name elastic">
        <span>
          拉群活码：
        </span>
        <div>
          <a-select default-value="1" style="width: 200px">
            <a-select-opt-group>
              <span slot="label">全部成员</span>
              <a-select-option value="1">
                cyy
              </a-select-option>
              <a-select-option value="2">
                zyy
              </a-select-option>
              <a-select-option value="3">
                刘波
              </a-select-option>刘波
            </a-select-opt-group>
          </a-select>
        </div>
      </div>
      <div class="state mb20 elastic">
        <span>
          开启状态：
        </span>
        <div class="switch-stat">
          <a-switch size="small" default-checked class="mr6"/>
          <span>开启</span>
        </div>
      </div>
      <div class="footer">
        <a-button class="mr16" @click="hide">
          取消
        </a-button>
        <a-button key="submit" type="primary">
          确定
        </a-button>
      </div>
    </a-modal>
  </div>
</template>
<script>
export default {
  data () {
    return {
      modalShow: false
    }
  },
  methods: {
    show () {
      this.modalShow = true
    },

    hide () {
      this.modalShow = false
    }
  }
}
</script>

<style lang="less" scoped>
/deep/ .ant-modal-title {
  text-align: center;
  font-size: 17px;
  font-weight: 600;
  letter-spacing: 1px;
}

/deep/ .ant-modal-body {
  height: 577px;
  overflow: auto;
}
.elastic{
  margin-top: 14px;
  display: flex;
  align-items: center;
}
.shop-address{
  display: flex;
  margin-top: 14px;
}
.maps-bottom{
  width: 540px;
  height: 260px;
  background-color: #8d8d8d;
}
/deep/ .ant-modal-body{
  height:300px;
}
</style>
