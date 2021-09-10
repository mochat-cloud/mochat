<template>
  <div>
    <a-modal v-model="showPopup" title="设置" :footer="null" :maskClosable="false" width="800px">
      <div class="setInterface">
        <div class="setInterface_left">
          <img
            src="../../../assets/phone-preview-bg.png"
            alt=""
            class="phoneImg">
          <div class="page_title">{{ setupData.title }}</div>
          <div class="page_content">
            <div class="business_logo">
              <img :src="setupData.default.logo" alt="" v-if="setupData.default.logo">
              <img src="../../../assets/default-cover.png" alt="" v-else>
            </div>
            <div class="shop_cont">
              <div class="shop_name">武汉分店</div>
              <div class="aboutUs" v-if="outlineOffon">{{ setupData.default.describe }}</div>
            </div>
          </div>
          <div class="shop_guide">{{ setupData.default.guide }}</div>
          <div class="shop_address" v-if="switchAddress"><a-icon type="environment" />山东省临沂市兰山区xxxxxx</div>
        </div>
        <div class="setInterface_right">
          <div class="right_row">
            <div class="bar"></div>
            <div class="row_title_a">页面标题：</div>
            <a-input
              class="row_cont"
              v-model="setupData.title"
              placeholder="请输入页面标题"
              allowClear
            ></a-input>
          </div>
          <div class="right_row">
            <div class="bar"></div>
            <div class="row_title_a">扫码页面展示：</div>
            <div class="row_cont">
              <a-radio-group v-model="setupData.show_type">
                <a-radio :value="1">默认样式</a-radio>
                <!--                <a-radio :value="2">自定义海报</a-radio>-->
              </a-radio-group>
            </div>
          </div>
          <div class="right_row">
            <div class="row_title_b">企业介绍：</div>
            <div class="row_cont"><a-switch v-model="outlineOffon" size="small" /></div>
          </div>
          <div class="right_row" v-if="outlineOffon">
            <a-input class="row_cont" v-model="setupData.default.describe" placeholder="请输入企业介绍" allowClear></a-input>
          </div>
          <div class="right_row">
            <div class="row_title_b">企业logo：</div>
            <div class="row_cont" style="margin-top: 7px;">
              <m-upload :def="false" text="请上传LOGO图片" v-model="setupData.default.logo" ref="kfQrcode" />
            </div>
          </div>
          <div class="right_row">
            <div class="row_title_b">扫码引导语：</div>
            <a-input class="row_cont" placeholder="请输入扫码引导语" allowClear v-model="setupData.default.guide"></a-input>
          </div>
          <div class="right_row">
            <div class="row_title_b">门店地址：</div>
            <div class="row_cont">
              <a-switch v-model="switchAddress" size="small" />
              <div class="row_cont_tips">开启后可在页面展示门店地址信息</div>
            </div>
          </div>
          <div class="right_row">
            <a-button class="foot_btn" @click="cancel">取消</a-button>
            <a-button type="primary" class="foot_btn" @click="determine">确定</a-button>
          </div>
        </div>
      </div>
    </a-modal>
  </div>
</template>
<script>
// eslint-disable-next-line no-unused-vars
import { pageSetApi, pageInfoApi } from '@/api/shopCode'
export default {
  data () {
    return {
      showPopup: false,
      // 过滤上传的logo
      serviceCode: 0,
      // 企业简介开关
      outlineOffon: false,
      // 门店地址开关
      switchAddress: false,
      //  页面标题
      setupData: {
        type: 1,
        title: '',
        show_type: 1,
        // 默认样式
        default: {
          // 企业介绍开关（0：关，1：开）
          describe_status: 0,
          // 企业介绍
          describe: '',
          //  企业logo
          logo: '',
          // 扫码引导语
          guide: '',
          // 门店地址
          adress_status: 0
        }
      }
    }
  },
  created () {
    // this.show()
  },
  methods: {
    show (type) {
      this.showPopup = true
      this.setupData.type = type
      this.getSetpageData({ type: this.setupData.type })
    },
    cancel () {
      this.showPopup = false
    },
    // 获取后台设置详情数据 pageInfoApi
    getSetpageData (params) {
      // this.setupData = {}
      console.log(params)
      pageInfoApi(params).then((res) => {
        if (JSON.stringify(res.data) != '[]') {
          this.setupData.id = res.data.id
          this.setupData.title = res.data.title
          this.setupData.show_type = res.data.showType
          this.setupData.default = res.data.default
          this.$refs.kfQrcode.setUrl(this.setupData.default.logo)
          this.packbackstageData(this.setupData.default)
        } else {
          this.setupData.title = ''
          this.setupData.default = {
            // 企业介绍开关（0：关，1：开）
            describe_status: 0,
            // 企业介绍
            describe: '',
            //  企业logo
            logo: '',
            // 扫码引导语
            guide: '',
            // 门店地址
            adress_status: 0
          }
          this.$refs.kfQrcode.setUrl(this.setupData.default.logo)
          this.packbackstageData(this.setupData.default)
        }
      })
    },
    // 包装后台数据
    packbackstageData (data) {
    // 企业介绍开关
      if (data.describe_status == 1) {
        this.outlineOffon = true
      } else {
        this.outlineOffon = false
      }
      if (data.adress_status == 1) {
        this.switchAddress = true
      } else {
        this.switchAddress = false
      }
    },
    // 确定按钮
    determine () {
      // 页面标题
      if (this.setupData.title == '') {
        this.$message.error('页面标题不能为空')
        return false
      }
      // 默认样式
      if (this.setupData.show_type == 1) {
        // 企业介绍开关
        if (this.outlineOffon) {
          this.setupData.default.describe_status = 1
        } else {
          this.setupData.default.describe_status = 0
        }
        // 门店地址开关
        if (this.switchAddress) {
          this.setupData.default.adress_status = 1
        } else {
          this.setupData.default.adress_status = 0
        }
        // 企业介绍
        if (this.setupData.default.describe_status == 1) {
          if (this.setupData.default.describe == '') {
            this.$message.error('企业介绍不能为空')
            return false
          }
        }
        // logo
        if (this.setupData.default.logo == '') {
          this.$message.error('企业logo不能为空')
          return false
        }
        //  引导语
        if (this.setupData.default.guide == '') {
          this.$message.error('引导语不能为空')
          return false
        }
      }
      pageSetApi(this.setupData).then((res) => {
        this.$message.success('页面设置成功')
        this.showPopup = false
      })
    }
  }
}
</script>
<style lang="less">
.setInterface{
  display: flex;
  position: relative;
}
.setInterface_left{
  width: 280px;
  height: 560px;
}
.setInterface_right{
  width: calc(100% - 280px);
}
.phoneImg{
  width: 100%;
  height: 100%;
}
.page_title{
  position: absolute;
  width: 175px;
  height: 20px;
  background: #EDEDED;
  top: 75px;
  left: 52px;
  text-align: center;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.page_content{
  position: absolute;
  display: flex;
  top:152px;
  left: 52px;
}
.business_logo{
  width: 30px;
  height: 30px;
}
.business_logo img{
  width: 100%;
  height: 100%;
}
.shop_cont{
  margin-top: -5px;
  margin-left: 5px;
}
.shop_name{
  width: 100px;
  font-size: 10px;
  font-weight:bold;
}
.aboutUs{
  width: 131px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 10px;
  color: rgba(0,0,0,.45);
}
.shop_guide{
  width: 134px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 10px;
  text-align: center;
  position: absolute;
  bottom: 197px;
  left: 76px;
  color: rgba(0,0,0,.45);
}
.shop_address{
  position: absolute;
  width: 217px;
  bottom: 165px;
  left: 30px;
  border-top: 1px dashed #e8e8e8;
  text-align: center;
  font-size: 10px;
  padding-top: 5px;
  color: rgba(0,0,0,.45);
}
.shop_address i{
  color: #54B1F7;
  margin-right: 2px;
}
.right_row{
  display: flex;
  justify-content:flex-end;
  width: 100%;
  margin-top: 15px;
}
.right_row:first-child{
  margin-top: 0;
}
.bar{
  margin-right: 6px;
  width: 3px;
  height: 12px;
  margin-top: 9px;
  background: #1890ff;
}
.row_title_a{
  font-size: 14px;
  font-weight: 600;
  color: rgba(0,0,0,.85);
  line-height: 32px;
}
.row_cont{
  width: calc(100% - 160px);
  line-height: 32px;
}
.row_title_b{
  font-size: 14px;
  color: rgba(0,0,0,.65);
  line-height: 32px;
}
.row_cont_tips{
  font-size: 12px;
  color: rgba(0,0,0,.45);
  line-height: 50px;
}
.foot_btn{
  width: 100px;
  margin-left: 35px;
  margin-top: 10px;
}
</style>
