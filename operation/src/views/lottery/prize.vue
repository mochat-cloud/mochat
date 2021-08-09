<template>
    <div class="record" v-show="prizeShow">
        <div class="bg" @click="prizeShow=false"></div>
        <div class="lottery-box">
            <div class="end" @click="hide">×</div>
            <div class="top">
              <img :src="headimgurl" alt="">
              <p>{{ nickname }}</p>
            </div>
            <div class="content">
                <div class="prize_no" v-if="controlStyle">{{receiveName}}</div>
                <div class="box" v-else>
                    <div class="qc-code">
                        <img :src="qrCode" height="100" width="100"/>
                    </div>
                    <div class="exchange_code">
                      <span style="color: #e3453a;margin-right: 5px;">{{receiveName}}</span>
                      <div v-if="redeemCode!=''">兑换码：<span>{{ redeemCode }}</span></div></div>
                    <div>请及时加客服兑换哦~</div>
                </div>
            </div>

        </div>

    </div>
</template>
<script>
export default {
    data() {
        return {
          prizeShow: false,
          qrCode:'',
          redeemCode:'',
          receiveName:'',
          headimgurl:'',
          nickname:'',
          controlStyle:false
        }
    },
  methods: {
        show(data) {
          this.prizeShow = true
          this.receiveName=data.receiveName
          this.qrCode=data.receiveQr
          this.redeemCode=data.receiveCode
          this.nickname=data.nickname
          this.headimgurl=data.headimgurl
          if(this.receiveName=='谢谢参与'){
            this.controlStyle=true
          }else{
            this.controlStyle=false
          }
        },
        hide() {
            this.prizeShow = false
        }
    }
}
</script>

<style scoped lang="scss">
.record {
    width: 100vw;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    position: fixed;
    z-index: 100;

    .bg {
        width: 100vw;
        height: 100vh;
        background-color: #00000094;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 10;
    }

    .lottery-box {
        width: 88vw;
        height: 280px;
        background-color: #ffffff;
        border-radius: 18px;
        margin-top: -1020px;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        position: relative;
        z-index: 20;

        .top {
            width: 78vw;
            display: flex;
            align-items: center;
            height: 30px;
            margin-top: 32px;
            img{
              width: 33px;
              height: 33px;
            }
            p {
                color: #e3453a;
                margin-left: 14px;
                font-size: 18px;
                font-weight: bold;
                margin-top: 17px;
            }
            span {
                display: block;
                margin-top: -16px;
            }
        }
        .box {
            width: 78vw;
            height: 200px;
            display: flex;
            align-items: center;
            flex-direction: column;
            .qc-code {
                width: 70vw;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 120px;
                margin-bottom: 10px;
            }

        }

    }
}
.end {
    position: absolute;
    font-size: 24px;
    right: 12px;
}
.exchange_code{
  margin-bottom: 10px;
  display: flex;
  span{
    font-weight: bold;
    font-size: 15px;
  }
}
.prize_no{
  font-weight: bold;
  font-size: 21px;
  margin-top: -45px;
}

</style>
