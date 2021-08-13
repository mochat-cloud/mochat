<template>
  <div class="page">
    <div class="search">
      <div class="city">
        <a-cascader
          :options="province"
          :display-render="displayRender"
          :field-names="{ label: 'name', value: 'name', children: 'city' }"
          :allowClear="false"
          @change="selectCity"
          v-model="screenCity"
          placeholder="请选择城市"
        />
      </div>
      <a-input-search
        placeholder="请输入门店名称"
        class="search-box"
        @search="onSearch"
        v-model="shopName"
        allowClear
      />
    </div>
    <div class="qc_code">
      <div class="code-box">
        <div class="top">
          <div class="left">
            <img style="width: 30px;height: 30px;" :src="defaultData.logo" alt="">
          </div>
          <div class="right">
            <div class="shop_name">{{ shopInfo.name }}</div>
            <div class="shop_describe">{{defaultData.describe}}</div>
          </div>
        </div>
        <div class="bottom">
          <div class="code">
            <img :src="shopInfo.employeeQrcode" height="200" width="200" alt=""/>
          </div>
        </div>
        <div class="shop_guide">{{defaultData.guide}}</div>
        <div class="shop_address" v-if="defaultData.adress_status==1">
          <a-icon type="environment"/>
          {{shopInfo.address}}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {areaCodeApi, weChatSdkConfig, openUserInfoApi} from '@/api/shopCode'

export default {
  data() {
    return {
      url: window.location.href,
      //省市信息
      province: [],
      //店铺信息
      shopInfo: {},
      //店铺样式
      defaultData: {},
      //页面设置
      pageSet: {},
      //店铺名称
      shopName: '',
      screenCity: []
    }
  },
  created() {
    this.corpId = this.$route.query.id;
    this.type = this.$route.query.type;
    this.getWarrantNews()
  },
  mounted() {
    let url = window.location.href.split('#')[0];

    if (navigator.userAgent.indexOf('iPhone') !== -1) {
      window.wechaturl = window.location + '';
    }
    if (window.wechaturl !== undefined) {
      url = window.wechaturl;
    }
    this.url = url;
    this.getOpenUserInfo();
  },
  methods: {
    getOpenUserInfo() {
      let that = this;
      openUserInfoApi({
        id: that.corpId
      }).then((res) => {
        if (res.data.openid === undefined) {
          let redirectUrl = '/auth/shopCode?id='+that.corpId+'&target=' + encodeURIComponent(that.url);
          that.$redirectAuth(redirectUrl);
        }
      });
    },
    //获取微信config信息
    getWarrantNews() {
      let that = this;
      weChatSdkConfig({
        url: that.url,
        corpId: that.corpId
      }).then((res) => {
        let that = this
        this.setConfigNews(res.data)
        wx.ready(() => {
          wx.getLocation({
            type: 'wgs84',
            success: function (res) {
              that.getShopData({
                corpId: that.corpId,
                type: that.type,
                lat: res.latitude,
                lng: res.longitude
              })
            }
          })
        })
      })
    },
    setConfigNews(data) {
      wx.config({
        debug: false,
        appId: data.appId,
        timestamp: data.timestamp,
        nonceStr: data.nonceStr,
        signature: data.signature,
        jsApiList: ['getLocation']
      });
    },
    //选择城市
    selectCity(e) {
      let params = {
        corpId: this.corpId,
        type: this.type,
        province: e[0],
        city: e[1]
      }
      this.getShopData(params)
    },
    //  获取店铺信息
    getShopData(params) {
      areaCodeApi(params).then((res) => {
        if (res.data.shop_info == '') {
          this.$message.error('门店不存在')
        } else {
          this.handProvinceFormat(res.data.province)
          this.shopInfo = res.data.shop_info
          document.title = this.shopInfo.name
          this.screenCity[0] = this.shopInfo.province
          this.screenCity[1] = this.shopInfo.city
          this.pageSet = res.data.page
          this.defaultData = this.pageSet.default
        }
      })
    },
    onSearch() {
      if (this.shopName != '') {
        let params = {
          corpId: this.corpId,
          type: this.type,
          name: this.shopName
        }
        this.getShopData(params)
      } else {
        this.$message.error('门店名称不能为空');
      }

    },
    //处理城市数据
    handProvinceFormat(data) {
      this.province = []
      data.forEach((item, index) => {
        item.city.forEach((obj) => {
          obj.name = obj.city
          obj.city = []
        })
        item.name = item.province
        this.province.push(item)
      })
    },
    displayRender({labels}) {
      return labels[labels.length - 1];
    }
  }

}
</script>

<style lang="scss">
.ant-cascader-menu:nth-child(3) {
  display: none;
}

.page {
  width: 100vw;
  height: 100vh;
  background-color: #bfddff;
}

.search {
  display: flex;
  align-items: center;
  background-color: #ffffff;
  padding: 16px;

  .city {
    margin-right: 15px;
  }
}

.qc_code {
  display: flex;
  justify-content: center;

  .code-box {
    margin-top: 50px;
    width: 90vw;
    height: 400px;
    background-color: #ffffff;
  }

  .top {
    display: flex;
    align-items: center;
    padding: 28px 28px 20px 28px;

    .left {
      margin-right: 15px;
    }
  }

  .shop_name {
    font-size: 17px;
    color: #222;
    font-weight: bold;
  }

  .shop_describe {
    color: #818181;
  }

  .shop_guide {
    margin-top: 12px;
    text-align: center;
    color: #818181;
  }

  .shop_address {
    text-align: center;
    margin-top: 15px;
    padding-top: 17px;
    color: #818181;
    border-top: 1px dashed #e8e8e8;

    i {
      color: #54b1f7;
      margin-right: 3px;
    }
  }

  .bottom {
    display: flex;
    justify-content: center;
  }

}
</style>
