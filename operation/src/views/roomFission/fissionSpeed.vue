<template>
    <div class="page">
        <div class="portrait">
            <div class="portrait-box">
              <img :src="wxUserData.headimgurl" alt="" class="user_img">
            </div>
            <div class="name-box">{{wxUserData.nickname}}</div>
        </div>
        <div class="info">
            <div class="info-top">
                <div class="left">
                    <p class="people">{{helphandData.success_num}}</p>
                    <p>助力成功人数</p>
                </div>
                <div class="right">
                    <p class="people">{{helphandData.diff_num}}</p>
                    <p>
                        还需助力人数
                    </p>
                </div>
            </div>
            <div class="info-bottom">
                <div>
                    <span class="tips" v-if="helphandData.success_num!=helphandData.diff_num">领取奖品</span>
                    <span class="tips success"  @click="receiveReward" v-else>领取奖品</span>
                </div>
            </div>
        </div>
        <div class="list">
            <div class="title">
                <span class="text">我的助力好友</span>
            </div>
            <div class="content">
                <div class="row" v-for="(item,index) in helphandData.invite_friends" :key="index">
                    <div class="left">
                        <img :src="item.avatar" class="avatar">
                    </div>
                    <div class="right">
                        <div class="user-info">
                            <div class="name">{{item.nickname}}</div>
                            <div class="time">{{item.createdAt}}</div>
                        </div>
                        <div class="tips">
                            <div class="tip">助力成功</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--完成任务-->
        <div class="mask" v-show="prizeShow">
            <div class="content">
                <div class="title">恭喜你完成任务</div>
                <img :src="receiveCode" alt="" class="gift-image">
                <div class="tip">长按保存二维码添加客服</div>
                <a-icon type="close-circle" @click="closebtn"  class="closeIcon" />
            </div>
        </div>
    </div>
</template>

<script>
import { inviteFriendsApi,receiveApi,openUserInfoApi } from '@/api/roomFission'
export default {
    data() {
        return {
          prizeShow: false,
        //  助力信息
          helphandData:{},
          receiveCode:''
        }
    },
  created() {
    this.fission_id = this.$route.query.fission_id
  },
  methods: {
      getOpenUserInfo() {
          let that = this;
          openUserInfoApi({
              id: that.fission_id
          }).then((res) => {
              if (res.data.openid === undefined) {
                  let redirectUrl = '/auth/roomFission?id=' + that.fission_id + '&target=' + encodeURIComponent(that.url);
                  that.$redirectAuth(redirectUrl);
              }

              this.wxUserData = res.data;
              this.gethelpData({
                  fission_id:this.fission_id,
                  union_id:this.wxUserData.unionid
              })
          });
      },
      closebtn(){
        this.prizeShow=false
      },
      //获取助力信息
      gethelpData(params){
        inviteFriendsApi(params).then((res)=>{
          this.helphandData=res.data
        })
      },
      //领取奖励
      receiveReward(){
        receiveApi({
          fission_id:this.fission_id,
          union_id:this.wxUserData.unionid
        }).then((res)=>{
          this.prizeShow=true
          this.receiveCode=res.data.qrCode
        })
      }
    }
}
</script>

<style lang="scss" scoped>
.user_img{
  width: 50px;
  height: 50px;
  border-radius: 50%;
}
.portrait {
    display: flex;
    width: 100vw;
    height: 90px;
    align-items: center;
    .portrait-box {
        margin-right: 20px;
    }
    .name-box {
        font-size: 22px;
        color: #ffffff;
    }
}
.page {
    width: 100vw;
    height: 110vh;
    background-color: #ff5636;
    padding: 16px;
    position: relative;
    background-image: url("../../static/images/bg.png");
    background-size: cover;
    display: flex;
    flex-direction: column;

    .info {
        background-color: #ffefdf;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 40px;
        margin-top: 20px;
        display: flex;
        flex-direction: column;
        .gift {
            display: flex;
            overflow: auto;
            margin-bottom: 8px;

            .item {
                $size: 86px;
                min-width: $size;
                display: flex;
                flex-direction: column;
                align-items: center;
                flex-grow: 1;

                .top {
                    $size: 70px;
                    width: $size;
                    height: $size;
                    background-color: orange;
                    border-radius: 10px;
                    margin-bottom: 8px;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    position: relative;
                    overflow: hidden;

                    .icon {
                        width: 50%;
                        height: 50%;
                    }

                    .received {
                        width: 100%;
                        text-align: center;
                        position: absolute;
                        background-color: #ffc271;
                        color: saddlebrown;
                        font-size: 12px;
                        bottom: 0;
                    }
                }

                .top-received {
                    background-color: #ffd6a1;
                    .icon {
                        opacity: .5;
                    }
                }

                .bottom {
                    width: 100%;
                    position: relative;

                    $lineHeight: 4px;

                    .lines {
                        display: flex;
                        align-items: center;

                        .line {
                            width: 50%;
                            height: $lineHeight;
                            background-color: #ffe1c4;
                        }

                        .light {
                            background-color: orange;
                        }
                    }

                    .round {
                        $size: 12px;
                        position: absolute;
                        top: 0 - $size / 2 + $lineHeight / 2;
                        left: calc(50% - (#{$size} / 2));
                        width: $size;
                        height: $size;
                        background-color: #ffd6a1;
                        border-radius: $size;
                    }

                    .light {
                        background-color: orange;
                    }
                }

                .level {
                    font-size: 12px;
                    margin-top: 2px;
                    text-align: center;
                    color: #ffaf45;
                }
            }
        }
    }

    .list {
        background-color: #ffefdf;
        border-radius: 10px;
        padding: 30px 30px 1px;
        position: relative;
        min-height: 400px;
        display: flex;
        flex-direction: column;

        .title {
            $size: 100px;
            width: 100%;
            top: -4px;
            left: 0;
            position: absolute;
            display: flex;
            justify-content: center;

            .text {
                padding: 3px 8px;
                color: white;
                $radius: 6px;
                border-radius: 0 0 $radius $radius;
                background-color: #ffaf45;
            }
        }

        .content {
            height: 0;
            flex-grow: 1;
            overflow-y: auto;

            .row {
                padding: 14px 0;
                border-bottom: 1px solid rgba(0, 0, 0, 0.06);
                display: flex;
                align-items: center;

                .left {
                    margin-right: 14px;

                    .avatar {
                        $size: 44px;
                        width: $size;
                        height: $size;
                        border-radius: 50%;
                        border: 2px solid #ffaf45;
                    }
                }

                .right {
                    flex-grow: 1;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;

                    .user-info {
                        .name {
                            color: saddlebrown;
                            font-size: 16px;
                            font-weight: bold;
                        }

                        .time {
                            color: #bd6e3a;
                            font-size: 12px;
                        }
                    }

                    .tips {
                        .tip {
                            font-size: 12px;
                            color: #B7EB8F;
                        }
                    }
                }
            }
        }
    }

    .mask {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.64);
        display: flex;
        justify-content: center;
        align-items: center;

        .content {
            width: 84%;
            padding: 20px 0 30px;
            background-color: white;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            .title {
                font-size: 16px;
                color: black;
            }
            .closeIcon{
              position: absolute;
              font-size: 20px;
              right: 10px;
              top: 8px;
            }
            .gift-image {
              margin-top: 5px;
              width: 200px;
              height: 200px;
            }
            .tip {
                margin-top: 10px;
                margin-bottom: -12px;
            }

            .get-gift {
                background-color: #ff5636;
                width: 50%;
                height: 32px;
                display: flex;
                color: white;
                border-radius: 32px;
                justify-content: center;
                align-items: center;

            }
        }
    }

    .code-mask {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.64);
        display: flex;
        justify-content: center;
        align-items: center;

        .content {
            width: 84%;
            padding: 20px 0 30px;
            background-color: white;
            display: flex;
            border-radius: 6px;
            flex-direction: column;
            align-items: center;

            .title {
                color: black;
                margin-bottom: 10px;
                font-size: 16px;
            }

            .tip {
                color: saddlebrown;
                font-size: 13px;
            }

            .code-image {
                width: 70%;
                margin-bottom: 20px;
            }

            .bottom-tip {
                font-size: 15px;
            }
        }
    }

    .fail-mask {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.64);
        display: flex;
        justify-content: center;
        align-items: center;

        .content {
            width: 84%;
            padding: 20px 0 30px;
            background-color: white;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;

            .title {
                font-size: 17px;
                color: black;
                margin-bottom: 20px;
            }

            .fail-image {
                width: 34%;
                margin-bottom: 12px;
            }

            .tip {
                margin-bottom: 20px;
            }

            .fail-btn {
                background-color: #ff5636;
                width: 70%;
                height: 40px;
                font-size: 15px;
                display: flex;
                color: white;
                border-radius: 32px;
                justify-content: center;
                align-items: center;
            }
        }
    }
}

.info-top {
    display: flex;

    .left, .right {
        display: flex;
        width: 50%;
        flex-direction: column;
        align-items: center;

        .people {
            font-size: 24px;
            font-weight: bold;
            color: #ff5636;
        }

    }

    .left {
        border-right: 1px dashed #ff5636;
    }


}

.info-bottom {
    display: flex;
    justify-content: center;
    margin-top: 24px;
    font-size: 16px;
    .tips {
        padding: 12px 110px;
        margin-top: 10px;
        margin-bottom: 12px;
        border-radius: 20px;
        background: rgba(0,0,0,.4);
        color: #e8e8e8;
    }
  .success{
    color: #ff5636;
    background: linear-gradient(#ffa73a, #FFCC00);
  }
}
</style>
